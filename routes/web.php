<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::post('/locale/{locale}', LocaleController::class)
    ->whereIn('locale', ['en', 'he'])
    ->name('locale.update');

Route::get('/dashboard', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

require __DIR__.'/auth.php';
