#!/usr/bin/env php
<?php

$environment = "DEV";
$package_name = "zyphor";

if ($environment === "DEV") {
    chdir(__DIR__ . '/application');
} else {
    chdir(__DIR__ . '/usr/lib/' . $package_name);
}

passthru('php artisan ' . escapeshellarg($argv[1] ?? 'list'));