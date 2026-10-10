<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

/**
 * Satu-satunya daftar menu aplikasi: sidebar, flyout sidebar ciut, navigasi bawah mobile, dan
 * pencarian menu di global search membaca dari sini. Modul baru cukup menambah entri di items().
 *
 * Setiap tautan boleh punya:
 * - feature: kunci Features yang harus menyala
 * - can: ability Gate, atau [ability, model] untuk policy
 * - superadmin: true bila hanya untuk Superadmin
 * - mobile: true bila layak jadi tujuan di navigasi bawah mobile (maks. 4 yang lolos izin)
 */
class Navigation
{
    /**
     * @return list<array{label: ?string, items: list<array<string, mixed>>}>
     */
    public static function items(): array
    {
        return [
            [
                'label' => null,
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'mobile' => true, 'mobile_label' => 'Beranda', 'keywords' => 'beranda ringkasan home'],
                ],
            ],
            [
                'label' => 'Website',
                'items' => [
                    ['label' => 'Statistik Pengunjung', 'icon' => 'chart-no-axes-combined', 'route' => 'content.analytics', 'feature' => 'content.analytics', 'can' => 'view-content', 'mobile' => true, 'mobile_label' => 'Statistik', 'keywords' => 'analitik analytics pengunjung trafik kunjungan sumber asal visitor'],
                    ['label' => 'Pesan Masuk', 'icon' => 'inbox', 'route' => 'content.messages', 'feature' => 'content.messages', 'can' => 'view-content', 'mobile' => true, 'mobile_label' => 'Pesan', 'keywords' => 'pesan kontak formulir inbox kotak masuk tindak lanjut undangan'],
                    ['label' => 'Konten Halaman', 'icon' => 'layout-template', 'route' => 'content.page', 'feature' => 'content.page', 'can' => 'view-content', 'keywords' => 'hero identitas nama foto profil kontak whatsapp email resume seo footer judul'],
                    [
                        'key' => 'portfolio',
                        'label' => 'Portofolio',
                        'icon' => 'trophy',
                        'feature' => 'content',
                        'children' => [
                            ['label' => 'Prestasi', 'icon' => 'trophy', 'route' => 'content.achievements', 'feature' => 'content.achievements', 'can' => 'view-content', 'mobile' => true, 'keywords' => 'lomba juara medali piagam sertifikat penghargaan'],
                            ['label' => 'Galeri', 'icon' => 'camera', 'route' => 'content.gallery', 'feature' => 'content.gallery', 'can' => 'view-content', 'keywords' => 'foto dokumentasi album gambar'],
                            ['label' => 'Kategori', 'icon' => 'tags', 'route' => 'content.categories', 'feature' => 'content.categories', 'can' => 'view-content', 'keywords' => 'saringan filter kelompok'],
                        ],
                    ],
                    [
                        'key' => 'journey',
                        'label' => 'Profil & Perjalanan',
                        'icon' => 'route',
                        'feature' => 'content',
                        'children' => [
                            ['label' => 'Angka Ringkasan', 'icon' => 'hash', 'route' => 'content.stats', 'feature' => 'content.stats', 'can' => 'view-content', 'keywords' => 'statistik jumlah total medali'],
                            ['label' => 'Bidang Minat', 'icon' => 'compass', 'route' => 'content.interests', 'feature' => 'content.interests', 'can' => 'view-content', 'keywords' => 'profil karakter minat bakat'],
                            ['label' => 'Pendidikan', 'icon' => 'graduation-cap', 'route' => 'content.education', 'feature' => 'content.education', 'can' => 'view-content', 'keywords' => 'sekolah riwayat kampus'],
                            ['label' => 'Lokasi', 'icon' => 'map-pin', 'route' => 'content.locations', 'feature' => 'content.locations', 'can' => 'view-content', 'keywords' => 'peta map alamat venue koordinat'],
                            ['label' => 'Rekam Jejak', 'icon' => 'milestone', 'route' => 'content.roadmap', 'feature' => 'content.roadmap', 'can' => 'view-content', 'keywords' => 'linimasa timeline tahapan perjalanan'],
                            ['label' => 'Testimoni', 'icon' => 'message-square-quote', 'route' => 'content.testimonials', 'feature' => 'content.testimonials', 'can' => 'view-content', 'keywords' => 'catatan guru pembina rekomendasi'],
                        ],
                    ],
                    ['label' => 'Pustaka Media', 'icon' => 'images', 'route' => 'content.media', 'feature' => 'content.media', 'can' => 'view-content', 'keywords' => 'gambar foto unggah upload berkas'],
                ],
            ],
            [
                'label' => 'Sistem',
                'items' => [
                    [
                        'key' => 'reports',
                        'label' => 'Laporan',
                        'icon' => 'bar-chart-3',
                        'feature' => 'reports',
                        'children' => [
                            ['label' => 'Log Aktivitas', 'icon' => 'scroll-text', 'route' => 'reports.activity-log', 'feature' => 'reports.activity-log', 'can' => ['viewAny', Activity::class], 'keywords' => 'audit trail jejak riwayat login'],
                        ],
                    ],
                    [
                        'key' => 'settings',
                        'label' => 'Pengaturan',
                        'icon' => 'settings',
                        'children' => [
                            ['label' => 'Branding Aplikasi', 'icon' => 'palette', 'route' => 'settings.branding', 'feature' => 'settings.branding', 'can' => ['update', Setting::class], 'keywords' => 'branding nama aplikasi logo tagline dashboard'],
                            ['label' => 'Peran & Perizinan', 'icon' => 'shield-alert', 'route' => 'settings.roles-and-permissions', 'active' => 'settings.roles-and-permissions*', 'feature' => 'settings.roles-and-permissions', 'can' => ['viewAny', Role::class], 'keywords' => 'role permission peran perizinan hak akses user pengguna'],
                            ['label' => 'Pengaturan Fitur', 'icon' => 'toggle-right', 'route' => 'settings.features', 'superadmin' => true, 'keywords' => 'fitur modul aktif nonaktif toggle'],
                            ['label' => 'Backup & Restore', 'icon' => 'database-backup', 'route' => 'settings.backups', 'active' => 'settings.backups*', 'superadmin' => true, 'keywords' => 'backup cadangan restore pulihkan database'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Menu yang boleh dilihat user: tautan yang gagal izin/fiturnya mati dibuang, grup tanpa
     * anak dan bagian kosong ikut hilang. Grup yang tersisa satu anak tampil sebagai tautan
     * langsung supaya tidak perlu dibuka dulu; nama grupnya disimpan di `group` untuk pencarian.
     *
     * @return list<array{label: ?string, items: list<array<string, mixed>>}>
     */
    public static function forUser(User $user): array
    {
        $sections = [];

        foreach (self::items() as $section) {
            $items = [];

            foreach ($section['items'] as $item) {
                if (isset($item['children'])) {
                    if (isset($item['feature']) && ! Features::enabled($item['feature'])) {
                        continue;
                    }

                    $item['children'] = array_values(array_filter($item['children'], fn (array $link) => self::allows($user, $link)));

                    if (count($item['children']) === 1) {
                        $items[] = $item['children'][0] + ['group' => $item['label']];
                    } elseif ($item['children'] !== []) {
                        $items[] = $item;
                    }
                } elseif (self::allows($user, $item)) {
                    $items[] = $item;
                }
            }

            if ($items !== []) {
                $sections[] = ['label' => $section['label'], 'items' => $items];
            }
        }

        return $sections;
    }

    /**
     * Semua tautan (tanpa grup) yang boleh dibuka user, beserta nama bagian induknya.
     *
     * @return list<array<string, mixed>>
     */
    public static function linksForUser(User $user): array
    {
        $links = [];

        foreach (self::forUser($user) as $section) {
            foreach ($section['items'] as $item) {
                foreach ($item['children'] ?? [$item] as $link) {
                    $links[] = $link + ['section' => isset($item['children']) ? $item['label'] : ($item['group'] ?? $section['label'] ?? 'Beranda')];
                }
            }
        }

        return $links;
    }

    /**
     * @param  array<string, mixed>  $link
     */
    public static function isActive(array $link): bool
    {
        return request()->routeIs($link['active'] ?? $link['route']);
    }

    /**
     * @param  array<string, mixed>  $group
     */
    public static function isGroupActive(array $group): bool
    {
        foreach ($group['children'] ?? [] as $link) {
            if (self::isActive($link)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $link
     */
    private static function allows(User $user, array $link): bool
    {
        if (($link['superadmin'] ?? false) && ! $user->isSuperAdmin()) {
            return false;
        }

        if (isset($link['feature']) && ! Features::enabled($link['feature'])) {
            return false;
        }

        if (isset($link['can'])) {
            $ability = (array) $link['can'];

            if (! $user->can($ability[0], $ability[1] ?? [])) {
                return false;
            }
        }

        return true;
    }
}
