#!/usr/bin/env php
<?php

require '../helpers/SystemHelper.php';

SystemHelper::setBaseDirectory(__DIR__);   // "scripts" now resolves next to this file

SystemHelper::runScript('packages.sh');