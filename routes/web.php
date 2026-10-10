<?php

use App\Http\Controllers\BrandingLogoController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteTrackController;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

// Halaman publik portofolio; seluruh isinya dikelola dari menu Website di dashboard.
Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('resume', [SiteController::class, 'resume'])->name('resume');
Route::post('pesan', ContactMessageController::class)->middleware('throttle:5,1')->name('contact');
Route::post('t', SiteTrackController::class)->middleware('throttle:120,1')->name('track');

// Logo dari Pengaturan Perusahaan; publik karena dipakai juga di halaman login dan favicon.
Route::get('branding/logo', BrandingLogoController::class)->name('branding.logo');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
require __DIR__.'/content.php';
require __DIR__.'/reports.php';
require __DIR__.'/settings.php';
