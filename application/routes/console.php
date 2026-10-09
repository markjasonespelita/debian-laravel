<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Artisan::command('install', function () {
//     // Check if running as root
//     if (function_exists('posix_geteuid') && posix_geteuid() !== 0) {
//         $this->error('This command requires sudo privileges.');
//         $this->line('Please run: sudo php artisan install');

//         return 1;
//     }

//     $this->info('Running installer with root privileges.');

//     $name = $this->ask('What is your name?');

//     $this->info("Hello, {$name}!");

//     // Add your installation logic here
//     $this->info('Installation completed successfully.');
// })->purpose('Install the application (requires sudo)');

Artisan::command('install', function () {
    if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
        $this->info('Running installer as root.');

        // Your installation logic here.

        return 0;
    }

    $this->info('Requesting administrator authorization...');

    $script = realpath(base_path('../scripts/install-root'));

    if ($script === false || !is_file($script)) {
        $this->error('Installer script not found.');
        $this->line('Expected location: ' . base_path('../scripts/install-root.sh'));

        return 1;
    }

    if (!is_executable($script)) {
        $this->error('Installer script is not executable.');
        $this->line('Run: chmod +x ' . escapeshellarg($script));

        return 1;
    }

    passthru(
        'pkexec /bin/sh ' . escapeshellarg($script),
        $status
    );

    if ($status !== 0) {
        $this->error("Installation failed or was cancelled (exit code: {$status}).");

        return $status;
    }

    $this->info('Installation completed.');

    return 0;
})->purpose('Install the application with administrator privileges');