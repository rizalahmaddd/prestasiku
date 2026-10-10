<?php

use App\Http\Controllers\ContactAttachmentController;
use App\Livewire\Content\Achievements;
use App\Livewire\Content\Analytics;
use App\Livewire\Content\Categories;
use App\Livewire\Content\Educations;
use App\Livewire\Content\GalleryItems;
use App\Livewire\Content\Interests;
use App\Livewire\Content\Locations;
use App\Livewire\Content\MediaLibrary;
use App\Livewire\Content\Messages;
use App\Livewire\Content\PageContent;
use App\Livewire\Content\RoadmapSteps;
use App\Livewire\Content\Stats;
use App\Livewire\Content\Testimonials;
use Illuminate\Support\Facades\Route;

// Konten website publik. Halaman butuh izin lihat (view-content); simpan/hapus dibatasi di
// dalam komponen lewat canManage() (manage-content).
Route::middleware(['auth', 'verified', 'can:view-content'])->prefix('konten')->name('content.')->group(function () {
    Route::get('statistik', Analytics::class)->name('analytics');
    Route::get('pesan', Messages::class)->name('messages');
    Route::get('pesan/{message}/lampiran/{index}', ContactAttachmentController::class)->whereNumber('index')->name('messages.attachment');
    Route::get('halaman', PageContent::class)->name('page');
    Route::get('prestasi', Achievements::class)->name('achievements');
    Route::get('galeri', GalleryItems::class)->name('gallery');
    Route::get('kategori', Categories::class)->name('categories');
    Route::get('pendidikan', Educations::class)->name('education');
    Route::get('lokasi', Locations::class)->name('locations');
    Route::get('rekam-jejak', RoadmapSteps::class)->name('roadmap');
    Route::get('bidang-minat', Interests::class)->name('interests');
    Route::get('angka', Stats::class)->name('stats');
    Route::get('testimoni', Testimonials::class)->name('testimonials');
    Route::get('media', MediaLibrary::class)->name('media');
});
