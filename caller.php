#!/usr/bin/env php
<?php

chdir(__DIR__ . '/application');

passthru('php artisan ' . escapeshellarg($argv[1] ?? 'list'));