<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Sakelar fitur per modul yang diatur Superadmin di Pengaturan Fitur. Fitur yang dimatikan
 * tertutup untuk semua akun (termasuk Superadmin), apa pun peran dan izinnya; data dan izin
 * tetap tersimpan sehingga menyalakannya lagi langsung memulihkan akses.
 */
class Features
{
    public const DISABLED_KEY = 'features.disabled';

    public const MODULE_CATEGORIES = [
        'data' => 'Data & Operasional',
        'system' => 'Laporan & Sistem',
    ];

    /**
     * Pola nama route per fitur. Route yang tidak tercakup di sini (dashboard, profil, halaman
     * Pengaturan Fitur & Backup) selalu terbuka. Modul baru cukup didaftarkan di sini supaya
     * muncul di halaman Pengaturan Fitur.
     *
     * @var array<string, array{label: string, icon: string, category: string, description: string, features: array<string, array{label: string, description: string, routes: list<string>}>}>
     */
    public const MODULES = [
        'content' => [
            'label' => 'Konten Website',
            'category' => 'data',
            'icon' => 'globe',
            'description' => 'Isi halaman publik: prestasi, galeri, pendidikan, peta lokasi, dan teks halaman.',
            'features' => [
                'page' => [
                    'label' => 'Konten Halaman',
                    'description' => 'Identitas, hero, judul bagian, kontak, resume PDF, dan SEO.',
                    'routes' => ['content.page'],
                ],
                'achievements' => [
                    'label' => 'Prestasi',
                    'description' => 'Kartu prestasi beserta detail piagamnya.',
                    'routes' => ['content.achievements'],
                ],
                'gallery' => [
                    'label' => 'Galeri',
                    'description' => 'Foto dokumentasi kegiatan.',
                    'routes' => ['content.gallery'],
                ],
                'categories' => [
                    'label' => 'Kategori',
                    'description' => 'Saringan prestasi dan galeri.',
                    'routes' => ['content.categories'],
                ],
                'education' => [
                    'label' => 'Pendidikan',
                    'description' => 'Kartu riwayat pendidikan.',
                    'routes' => ['content.education'],
                ],
                'locations' => [
                    'label' => 'Lokasi',
                    'description' => 'Titik di peta website.',
                    'routes' => ['content.locations'],
                ],
                'roadmap' => [
                    'label' => 'Rekam Jejak',
                    'description' => 'Tahapan perjalanan dari tahun ke tahun.',
                    'routes' => ['content.roadmap'],
                ],
                'interests' => [
                    'label' => 'Bidang Minat',
                    'description' => 'Kartu bidang yang ditekuni.',
                    'routes' => ['content.interests'],
                ],
                'stats' => [
                    'label' => 'Angka Ringkasan',
                    'description' => 'Angka sorotan di bawah hero dan di rekam jejak.',
                    'routes' => ['content.stats'],
                ],
                'testimonials' => [
                    'label' => 'Testimoni',
                    'description' => 'Catatan guru dan pembina.',
                    'routes' => ['content.testimonials'],
                ],
                'analytics' => [
                    'label' => 'Statistik Pengunjung',
                    'description' => 'Mencatat dan menampilkan jumlah pengunjung, asal kunjungan, dan interaksi. Dimatikan berarti pencatatan berhenti.',
                    'routes' => ['content.analytics'],
                ],
                'messages' => [
                    'label' => 'Pesan Masuk',
                    'description' => 'Formulir kontak di website dan kotak masuknya. Dimatikan berarti formulirnya hilang dari website.',
                    'routes' => ['content.messages*'],
                ],
                'media' => [
                    'label' => 'Pustaka Media',
                    'description' => 'Semua gambar yang diunggah.',
                    'routes' => ['content.media'],
                ],
            ],
        ],
        'reports' => [
            'label' => 'Laporan',
            'category' => 'system',
            'icon' => 'bar-chart-3',
            'description' => 'Laporan dan jejak audit aktivitas pengguna.',
            'features' => [
                'activity-log' => [
                    'label' => 'Log Aktivitas',
                    'description' => 'Audit trail aktivitas pengguna, riwayat login, dan perubahan data.',
                    'routes' => ['reports.activity-log'],
                ],
            ],
        ],
        'settings' => [
            'label' => 'Pengaturan',
            'category' => 'system',
            'icon' => 'settings',
            'description' => 'Branding dashboard dan hak akses pengguna berbasis peran (RBAC).',
            'features' => [
                'branding' => [
                    'label' => 'Branding Aplikasi',
                    'description' => 'Nama, tagline, dan logo dashboard.',
                    'routes' => ['settings.branding'],
                ],
                'roles-and-permissions' => [
                    'label' => 'Peran & Perizinan',
                    'description' => 'Manajemen peran (roles), izin akses (permissions), dan penugasan peran ke pengguna.',
                    'routes' => ['settings.roles-and-permissions*'],
                ],
            ],
        ],
    ];

    /** @var array<string, list<string>> */
    private static array $urlCache = [];

    /**
     * Kunci modul ("content") atau fitur ("content.achievements"). Modul dianggap aktif selama
     * modulnya tidak dimatikan dan masih ada minimal satu fitur di dalamnya yang menyala.
     */
    public static function enabled(string $key): bool
    {
        if (! self::isKnown($key)) {
            throw new InvalidArgumentException("Fitur [{$key}] tidak terdaftar di Features::MODULES.");
        }

        $disabled = self::disabledKeys();

        if (str_contains($key, '.')) {
            return ! in_array(Str::before($key, '.'), $disabled, true) && ! in_array($key, $disabled, true);
        }

        if (in_array($key, $disabled, true)) {
            return false;
        }

        foreach (array_keys(self::MODULES[$key]['features']) as $feature) {
            if (! in_array("{$key}.{$feature}", $disabled, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Satu route bisa dimiliki beberapa fitur, dan semuanya harus menyala.
     *
     * @return list<string>
     */
    public static function featuresForRoute(?string $routeName): array
    {
        if ($routeName === null) {
            return [];
        }

        $features = [];

        foreach (self::MODULES as $module => $definition) {
            foreach ($definition['features'] as $feature => $featureDefinition) {
                if (Str::is($featureDefinition['routes'], $routeName)) {
                    $features[] = "{$module}.{$feature}";
                }
            }
        }

        return $features;
    }

    public static function allowsRoute(?string $routeName): bool
    {
        return self::allEnabled(self::featuresForRoute($routeName));
    }

    /**
     * Untuk tautan yang hanya tersedia sebagai URL (komponen dashboard, notifikasi, x-feature-link).
     * URL di luar aplikasi atau yang tidak cocok dengan route mana pun dianggap boleh.
     */
    public static function allowsUrl(?string $url): bool
    {
        if ($url === null || $url === '') {
            return true;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);

        if (! array_key_exists($path, self::$urlCache)) {
            try {
                $routeName = Route::getRoutes()->match(Request::create($path))->getName();
            } catch (HttpException) {
                $routeName = null;
            }

            self::$urlCache[$path] = self::featuresForRoute($routeName);
        }

        return self::allEnabled(self::$urlCache[$path]);
    }

    /**
     * @param  list<string>  $features
     */
    private static function allEnabled(array $features): bool
    {
        foreach ($features as $feature) {
            if (! self::enabled($feature)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    public static function disabledKeys(): array
    {
        // Sidebar memanggil ini puluhan kali per halaman dan cache Setting ada di database,
        // jadi hasilnya ditahan per request (scoped: ikut dibuang di queue worker).
        if (! app()->bound(self::DISABLED_KEY)) {
            app()->scoped(self::DISABLED_KEY, function (): array {
                $decoded = json_decode(Setting::get(self::DISABLED_KEY) ?? '[]', true);

                return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
            });
        }

        return app(self::DISABLED_KEY);
    }

    /**
     * @param  list<string>  $keys
     */
    public static function setDisabled(array $keys): void
    {
        $valid = array_values(array_filter(array_unique($keys), fn (string $key) => self::isKnown($key)));
        sort($valid);

        Setting::put(self::DISABLED_KEY, json_encode($valid));
        app()->forgetInstance(self::DISABLED_KEY);
    }

    public static function isKnown(string $key): bool
    {
        if (! str_contains($key, '.')) {
            return isset(self::MODULES[$key]);
        }

        [$module, $feature] = explode('.', $key, 2);

        return isset(self::MODULES[$module]['features'][$feature]);
    }
}
