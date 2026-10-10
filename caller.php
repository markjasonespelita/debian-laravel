#!/usr/bin/env php
<?php

$environment = "DEV";
$package_name = "zyphor";

if ($environment === "DEV") {
    chdir(__DIR__ . '/application');
} else {
    chdir(__DIR__ . '/usr/lib/' . $package_name);
}

// Forward all arguments to Artisan
$arguments = array_slice($argv, 1);

$command = 'php artisan ' . implode(
    ' ',
    array_map('escapeshellarg', $arguments ?: ['list'])
);

passthru($command, $exitCode);

exit($exitCode);