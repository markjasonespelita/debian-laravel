#!/usr/bin/env php
<?php

require __DIR__ . '/../helpers/SystemHelper.php';

SystemHelper::setBaseDirectory(__DIR__);

$arg = trim($argv[1] ?? '');

// Don't allow a leading "-" so the name can't be read as an option
if (!preg_match('/^[A-Za-z0-9._+:@\/][A-Za-z0-9._+:@\/-]{0,99}$/', $arg)) {
    fwrite(STDERR, "Zyphor: ✖ Enter a valid package or app name\n");
    exit(1);
}

SystemHelper::runScript('search.sh', [$arg]);