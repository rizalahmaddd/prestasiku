<?php

use App\Http\Controllers\BackupDownloadController;
use App\Livewire\Settings\Backups;
use App\Livewire\Settings\BrandingSettings;
use App\Livewire\Settings\FeatureToggles;
use App\Livewire\Settings\RolesAndPermissions;
use Illuminate\Support\Facades\Route;

// Branding aplikasi, Peran / Hak Akses, sakelar fitur, dan backup database (khusus Superadmin)
Route::middleware(['auth', 'verified'])->prefix('pengaturan')->name('settings.')->group(function () {
    Route::get('branding', BrandingSettings::class)->name('branding');
    Route::get('peran-izin', RolesAndPermissions::class)->name('roles-and-permissions');
    Route::get('fitur', FeatureToggles::class)->name('features');
    Route::get('backup', Backups::class)->name('backups');
    Route::get('backup/{file}/unduh', BackupDownloadController::class)->name('backups.download');
});
