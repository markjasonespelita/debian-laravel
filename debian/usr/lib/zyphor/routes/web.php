<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/trigger', function () {
    Artisan::call('install');

    $output = Artisan::output();

    return response($output);
});