<?php

use App\Helpers\SystemHelper;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| install
|--------------------------------------------------------------------------
| Runs scripts/install-root.sh with administrator rights.
|   php artisan install           -> graphical prompt (pkexec)
|   php artisan install --sudo    -> terminal password prompt (sudo)
|   sudo php artisan install      -> already root, runs directly
*/
Artisan::command('install {--sudo : Use sudo instead of the graphical pkexec prompt}', function () {
    $useSudo = (bool) $this->option('sudo');

    $this->info('Requesting administrator authorization...');

    try {
        $status = SystemHelper::runScript(
            'install-root',
            [],
            asRoot: $useSudo,
            withPkexec: ! $useSudo
        );
    } catch (\Throwable $e) {
        $this->error($e->getMessage());

        return 1;
    }

    if ($status === 126 || $status === 127) {
        $this->error('Authorization was cancelled or denied.');

        return $status;
    }

    if ($status !== 0) {
        $this->error("Installation failed (exit code: {$status}).");

        return $status;
    }

    $this->info('Installation completed.');

    return 0;
})->purpose('Install the application with administrator privileges');

/*
|--------------------------------------------------------------------------
| script
|--------------------------------------------------------------------------
|   php artisan script backup                      -> as current user
|   php artisan script backup /home 7              -> with arguments
|   php artisan script backup --root               -> via sudo
|   php artisan script backup --pkexec             -> via graphical auth
|   php artisan script backup -- --verbose         -> pass option-like args after --
*/
Artisan::command(
    'script {name : Script name, with or without .sh} '
    . '{args?* : Arguments passed to the script} '
    . '{--root : Run with sudo} '
    . '{--pkexec : Run with graphical PolicyKit authentication}',
    function (string $name) {
        $asRoot = (bool) $this->option('root');
        $withPkexec = (bool) $this->option('pkexec');

        if ($asRoot && $withPkexec) {
            $this->error('Choose either --root or --pkexec, not both.');

            return 1;
        }

        $this->info("Executing script: {$name}");

        try {
            $status = SystemHelper::runScript(
                $name,
                $this->argument('args') ?? [],
                $asRoot,
                $withPkexec
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return 1;
        }

        if ($status !== 0) {
            $this->error("Script failed with exit code {$status}.");

            return $status;
        }

        $this->info('Script executed successfully.');

        return 0;
    }
)->purpose('Execute a shell script from the scripts directory');

/*
|--------------------------------------------------------------------------
| scripts
|--------------------------------------------------------------------------
| Lists every .sh script available to `phpti arsan script`.
*/
Artisan::command('scripts', function () {
    try {
        $names = SystemHelper::listScripts();
    } catch (\Throwable $e) {
        $this->error($e->getMessage());

        return 1;
    }

    if ($names === []) {
        $this->warn('No scripts found.');

        return 0;
    }

    $this->table(['Available scripts'], array_map(fn ($n) => [$n], $names));

    return 0;
})->purpose('List available shell scripts');

Artisan::command('ohahay', function () {
    SystemHelper::runScript('sample');
})->purpose('List available shell scripts');