<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/parking-owners', function () {
    return view('admin.parking-owners.index');
})->name('admin.parking-owners.index');

Route::get('/parking-owners/show', function () {
    return view('admin.parking-owners.show');
})->name('admin.parking-owners.show');
Route::get('/parkings/create', function () {
    return view('admin.parkings.create');
})->name('admin.parkings.create');
Route::get('/parkings/pricing', function () {
    return view('admin.parkings.pricing');
})->name('admin.parkings.pricing');
Route::get('/parkings/facilities', function () {
    return view('admin.parkings.facilities');
})->name('admin.parkings.facilities');
Route::get('/parkings/images', function () {
    return view('admin.parkings.images');
})->name('admin.parkings.images');
Route::get('/parkings/review', function () {
    return view('admin.parkings.review');
})->name('admin.parkings.review');
Route::get('/bookings', function () {
    return view('admin.bookings.index');
})->name('admin.bookings.index');
Route::get('/bookings/show', function () {
    return view('admin.bookings.show');
})->name('admin.bookings.show');