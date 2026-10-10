<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Satu-satunya sumber identitas aplikasi yang tampil di layar (judul tab, sidebar, halaman login,
 * logo, favicon, kepala dokumen ekspor). Semua nilainya diatur di Pengaturan > Branding Aplikasi;
 * view dan komponen tidak boleh menulis nama aplikasi sendiri.
 */
class Branding
{
    public const APP_NAME_KEY = 'app_name';

    public const APP_TAGLINE_KEY = 'app_tagline';

    public const LOGO_KEY = 'app_logo';

    /**
     * Nama aplikasi di sidebar, halaman login, dan judul tab. Belum diatur: pakai APP_NAME di .env.
     */
    public static function appName(): string
    {
        return Setting::get(self::APP_NAME_KEY) ?: (string) config('app.name');
    }

    public static function tagline(): ?string
    {
        return Setting::get(self::APP_TAGLINE_KEY) ?: null;
    }

    public static function logoPath(): ?string
    {
        return Setting::get(self::LOGO_KEY) ?: null;
    }

    /**
     * Nama file logo selalu unik per unggahan, jadi URL-nya otomatis berganti saat logo diganti
     * dan browser tidak menampilkan logo lama dari cache.
     */
    public static function logoUrl(): ?string
    {
        $path = self::logoPath();

        return $path ? route('branding.logo', ['v' => basename($path)]) : null;
    }

    public static function pageTitle(?string $title = null): string
    {
        return filled($title) ? $title.' - '.self::appName() : self::appName();
    }
}
