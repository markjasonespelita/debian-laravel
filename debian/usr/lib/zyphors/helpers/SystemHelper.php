<?php

declare(strict_types=1);

/**
 * System helper (plain PHP, no framework required).
 *
 * Usage:
 *     require_once __DIR__ . '/SystemHelper.php';
 *
 * Requires PHP 8.0+ and Linux. CLI only (refuses to run under a web SAPI).
 *
 * Design notes:
 *  - Commands run through proc_open() with an argument array, so NO shell is
 *    involved and shell injection is not possible.
 *  - Passwords are never collected in PHP. sudo / pkexec do their own auth.
 *  - Privileged script runs refuse scripts that group/others can write to.
 */
final class SystemHelper
{
    private const BASH = '/bin/bash';
    private const SUDO = '/usr/bin/sudo';
    private const PKEXEC_CANDIDATES = ['/usr/bin/pkexec', '/bin/pkexec'];

    /** Scripts location set from code (takes priority over the environment). */
    private static ?string $scriptsLocation = null;

    /** Directory that relative script locations resolve from. Defaults to this file's folder. */
    private static ?string $baseDirectory = null;

    // ------------------------------------------------------------------
    // Configuration
    // ------------------------------------------------------------------

    /**
     * Set the scripts directory from code. Relative paths resolve from the base directory.
     * Example: SystemHelper::setScriptsDirectory('/usr/lib/myapp/scripts');
     */
    public static function setScriptsDirectory(string $path): void
    {
        self::$scriptsLocation = $path;
    }

    /** Set the directory that relative script locations resolve from. */
    public static function setBaseDirectory(string $path): void
    {
        self::$baseDirectory = $path;
    }

    // ------------------------------------------------------------------
    // Privilege checks
    // ------------------------------------------------------------------

    /** True when the current process is running as root (effective UID 0). */
    public static function isRoot(): bool
    {
        if (function_exists('posix_geteuid')) {
            return posix_geteuid() === 0;
        }

        // No POSIX extension: read the effective UID from /proc (no shell needed).
        $status = @file_get_contents('/proc/self/status');
        if ($status !== false && preg_match('/^Uid:\s+\d+\s+(\d+)/m', $status, $m)) {
            return (int) $m[1] === 0;
        }

        return false;
    }

    /** Throw unless the current process is root. */
    public static function requireRoot(): void
    {
        if (!self::isRoot()) {
            throw new RuntimeException('Root privileges are required. Run this command with sudo.');
        }
    }

    // ------------------------------------------------------------------
    // Running commands
    // ------------------------------------------------------------------

    /**
     * Run a program with output streamed to the terminal. Returns the exit code.
     * Example: SystemHelper::run(['/usr/bin/ls', '-la', '/tmp']);
     */
    public static function run(array $command): int
    {
        return self::execute($command, false)['code'];
    }

    /**
     * Run a program and capture its output instead of printing it.
     *
     * @return array{code:int, stdout:string, stderr:string}
     */
    public static function capture(array $command): array
    {
        return self::execute($command, true);
    }

    /**
     * Run a command as root via sudo (runs directly if already root).
     * $interactive=false adds `sudo -n`, which fails instead of prompting.
     */
    public static function runAsRoot(array $command, bool $interactive = true): int
    {
        return self::run(self::sudoCommand($command, $interactive));
    }

    /**
     * Capture the output of a command run as root via sudo.
     *
     * @return array{code:int, stdout:string, stderr:string}
     */
    public static function captureAsRoot(array $command, bool $interactive = true): array
    {
        return self::capture(self::sudoCommand($command, $interactive));
    }

    /** Run a command with graphical PolicyKit authentication (needs a polkit agent). */
    public static function runWithPkexec(array $command): int
    {
        return self::run(self::pkexecCommand($command));
    }

    /** True if an executable exists (absolute path, or found on PATH). */
    public static function commandExists(string $name): bool
    {
        if ($name === '' || str_contains($name, "\0")) {
            return false;
        }

        if (str_contains($name, '/')) {
            return is_file($name) && is_executable($name);
        }

        foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $dir) {
            if ($dir !== '' && is_file("$dir/$name") && is_executable("$dir/$name")) {
                return true;
            }
        }

        return false;
    }

    // ------------------------------------------------------------------
    // Scripts directory
    // ------------------------------------------------------------------

    /**
     * Resolve the scripts directory.
     * Order: setScriptsDirectory() -> SCRIPTS_LOCATION environment variable -> ./scripts
     * Relative paths resolve from the base directory (default: the folder of this file);
     * absolute paths are used as-is.
     */
    public static function scriptsDirectory(): string
    {
        $configured = self::$scriptsLocation;

        if ($configured === null) {
            $env = getenv('SCRIPTS_LOCATION');
            $configured = ($env !== false && $env !== '') ? $env : 'scripts';
        }

        if (trim($configured) === '' || str_contains($configured, "\0")) {
            throw new RuntimeException('Scripts location is empty or invalid.');
        }

        $base = self::$baseDirectory ?? __DIR__;
        $candidate = str_starts_with($configured, '/')
            ? $configured
            : rtrim($base, '/') . '/' . $configured;

        $path = realpath($candidate);

        if ($path === false || !is_dir($path)) {
            throw new RuntimeException('Scripts directory not found: ' . $candidate);
        }

        return $path;
    }

    /** @return string[] Names (without .sh) of all scripts in the scripts directory. */
    public static function listScripts(): array
    {
        $names = [];
        foreach (glob(self::scriptsDirectory() . '/*.sh') ?: [] as $file) {
            $names[] = basename($file, '.sh');
        }
        sort($names);

        return $names;
    }

    /** Resolve and validate a script by name ("sample" or "sample.sh"). Returns its real path. */
    public static function scriptPath(string $name): string
    {
        // Simple filename only: no paths, no shell syntax.
        if (!preg_match('/^[A-Za-z0-9_-]+(?:\.sh)?$/', $name)) {
            throw new InvalidArgumentException(
                'Invalid script name. Use letters, numbers, underscores, hyphens, and an optional .sh extension.'
            );
        }

        if (!str_ends_with($name, '.sh')) {
            $name .= '.sh';
        }

        $directory = self::scriptsDirectory();
        $path = realpath($directory . DIRECTORY_SEPARATOR . $name);

        // Must resolve to a regular file directly inside the scripts directory
        // (this also rejects symlinks that point elsewhere).
        if ($path === false || dirname($path) !== $directory || !is_file($path)) {
            throw new RuntimeException('Script not found: ' . $name);
        }

        if (!is_readable($path)) {
            throw new RuntimeException('Script is not readable: ' . $name);
        }

        return $path;
    }

    /**
     * Execute a .sh script from the scripts directory.
     * Choose at most one of $asRoot (sudo) or $withPkexec (graphical).
     * Returns the script's exit code.
     */
    public static function runScript(
        string $name,
        array $arguments = [],
        bool $asRoot = false,
        bool $withPkexec = false
    ): int {
        if ($asRoot && $withPkexec) {
            throw new InvalidArgumentException('Choose either sudo or pkexec, not both.');
        }

        $path = self::scriptPath($name);
        // Arguments may legitimately be empty; only the full command must not be.
        $command = array_merge([self::BASH, $path], $arguments === [] ? [] : self::normalize($arguments));

        if ($withPkexec) {
            self::assertTrustedForPrivilegedRun($path);
            return self::runWithPkexec($command);
        }

        if ($asRoot) {
            self::assertTrustedForPrivilegedRun($path);
            return self::runAsRoot($command);
        }

        return self::run($command);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    /** @return string[] */
    private static function sudoCommand(array $command, bool $interactive): array
    {
        $command = self::normalize($command);

        if (self::isRoot()) {
            return $command;
        }

        if (!is_executable(self::SUDO)) {
            throw new RuntimeException('sudo is not installed at ' . self::SUDO . '.');
        }

        return array_merge([self::SUDO], $interactive ? [] : ['-n'], ['--'], $command);
    }

    /** @return string[] */
    private static function pkexecCommand(array $command): array
    {
        $command = self::normalize($command);

        if (self::isRoot()) {
            return $command;
        }

        foreach (self::PKEXEC_CANDIDATES as $candidate) {
            if (is_executable($candidate)) {
                return array_merge([$candidate], $command);
            }
        }

        throw new RuntimeException('pkexec is not installed. Install PolicyKit (polkit) support first.');
    }

    /**
     * Validate and stringify a command/argument array.
     *
     * @return string[]
     */
    private static function normalize(array $command): array
    {
        $command = array_values($command);

        if ($command === []) {
            throw new InvalidArgumentException('Command cannot be empty.');
        }

        $result = [];
        foreach ($command as $part) {
            if (!is_string($part) && !is_int($part) && !is_float($part)) {
                throw new InvalidArgumentException('Command arguments must be strings or numbers.');
            }

            $part = (string) $part;

            if (str_contains($part, "\0")) {
                throw new InvalidArgumentException('Command arguments cannot contain null bytes.');
            }

            $result[] = $part;
        }

        return $result;
    }

    /**
     * Privileged runs must not execute scripts someone else could modify.
     * Script and directory must not be group/world-writable and must be owned
     * by root or by the current user.
     */
    private static function assertTrustedForPrivilegedRun(string $script): void
    {
        $uid = function_exists('posix_geteuid') ? posix_geteuid() : null;

        foreach ([$script, dirname($script)] as $path) {
            $perms = fileperms($path);
            $owner = fileowner($path);

            if ($perms === false || $owner === false) {
                throw new RuntimeException("Cannot inspect permissions of {$path}.");
            }

            if (($perms & 0022) !== 0) {
                throw new RuntimeException("Refusing privileged run: {$path} is writable by group/others (fix: chmod go-w).");
            }

            if ($owner !== 0 && $owner !== $uid) {
                throw new RuntimeException("Refusing privileged run: {$path} is owned by an untrusted user.");
            }
        }
    }

    /** @return array{code:int, stdout:string, stderr:string} */
    private static function execute(array $command, bool $capture): array
    {
        if (PHP_SAPI !== 'cli') {
            throw new RuntimeException('SystemHelper can only be used from the command line.');
        }

        $command = self::normalize($command);

        // Array form of proc_open() (PHP 7.4+) executes directly, with no shell.
        $descriptors = $capture
            ? [0 => ['file', '/dev/null', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']]
            : [0 => STDIN, 1 => STDOUT, 2 => STDERR];

        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to start command: ' . $command[0]);
        }

        $buffers = [1 => '', 2 => ''];

        if ($capture) {
            // Read stdout and stderr together so neither pipe can fill and deadlock.
            $open = [1 => $pipes[1], 2 => $pipes[2]];
            foreach ($open as $pipe) {
                stream_set_blocking($pipe, false);
            }

            while ($open !== []) {
                $read = array_values($open);
                $write = $except = null;

                if (stream_select($read, $write, $except, 1) === false) {
                    break;
                }

                foreach ($read as $stream) {
                    $key = array_search($stream, $open, true);
                    $chunk = fread($stream, 8192);

                    if ($chunk !== false && $chunk !== '') {
                        $buffers[$key] .= $chunk;
                    }

                    if (feof($stream)) {
                        fclose($stream);
                        unset($open[$key]);
                    }
                }
            }

            foreach ($open as $pipe) {
                fclose($pipe);
            }
        }

        return [
            'code' => proc_close($process),
            'stdout' => $buffers[1],
            'stderr' => $buffers[2],
        ];
    }
}

/*
 * Method                    Usage
 * setScriptsDirectory()     Set the scripts folder from code.
 * setBaseDirectory()        Set what relative script paths resolve from.
 * isRoot()                  Check whether PHP is running as root.
 * requireRoot()             Throw if root privileges are missing.
 * run()                     Run a command, streaming output to the terminal.
 * capture()                 Run a command and return code/stdout/stderr.
 * runAsRoot()               Run a command via sudo.
 * captureAsRoot()           Capture output of a command run via sudo.
 * runWithPkexec()           Run a command with graphical PolicyKit auth.
 * commandExists()           Check whether a binary exists on PATH.
 * scriptsDirectory()        Resolve the configured scripts directory.
 * listScripts()             List available .sh scripts.
 * scriptPath()              Validate a script name and get its real path.
 * runScript()               Run a script from the scripts directory.
 *
 * Example (example.php):
 *     <?php
 *     require_once __DIR__ . '/SystemHelper.php';
 *
 *     SystemHelper::setScriptsDirectory('/usr/lib/myapp/scripts');
 *     print_r(SystemHelper::listScripts());
 *     $code = SystemHelper::runScript('sample', ['--verbose']);
 *     $out  = SystemHelper::capture(['/usr/bin/uname', '-r']);
 *     echo $out['stdout'];
 */