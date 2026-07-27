<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/parking-owners', function () {
    return view('admin.parking-owners.index');
})->name('admin.parking-owners.index');