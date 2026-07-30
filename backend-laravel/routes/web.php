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
Route::get('/payments', function () {
    return view('admin.payments.index');
})->name('admin.payments.index');
Route::get('/payments/show', function () {
    return view('admin.payments.show');
})->name('admin.payments.show');
Route::get('/earnings', function () {
    return view('admin.earnings.dashboard');
})->name('admin.earnings.dashboard');
Route::get('/users', function () {
    return view('admin.users.index');
})->name('admin.users.index');
Route::get('/users/show', function () {
    return view('admin.users.show');
})->name('admin.users.show');
Route::get('/reports', function () {
    return view('admin.reports.dashboard');
})->name('admin.reports.dashboard');
Route::get('/notifications', function () {
    return view('admin.notifications.index');
})->name('admin.notifications.index');
Route::get('/support', function () {
    return view('admin.support.index');
})->name('admin.support.index');
Route::get('/support/show', function () {
    return view('admin.support.show');
})->name('admin.support.show');
Route::get('/cms', function () {
    return view('admin.cms.index');
})->name('admin.cms.index');
Route::get('/cms/edit', function () {
    return view('admin.cms.edit');
})->name('admin.cms.edit');
Route::get('/settings', function () {
    return view('admin.settings.index');
})->name('admin.settings.index');