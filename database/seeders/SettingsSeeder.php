<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Nilai awal branding dashboard. Bisa diubah lewat Pengaturan > Branding Aplikasi.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::putMany([
            'app_name' => config('app.name'),
            'app_tagline' => 'Kelola Portofolio',
        ]);
    }
}
