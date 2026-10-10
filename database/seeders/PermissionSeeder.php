<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Definisi lengkap hak akses (perizinan) sistem yang dikelompokkan per modul. Halaman
     * Peran & Perizinan membaca daftar ini, jadi modul baru cukup menambah grupnya di sini.
     *
     * @var array<string, array<string, array{label: string, description: string}>>
     */
    public const PERMISSION_GROUPS = [
        'Konten Website' => [
            'content.view' => [
                'label' => 'Lihat Konten Website',
                'description' => 'Membuka halaman pengelolaan konten website tanpa bisa mengubahnya.',
            ],
            'content.manage' => [
                'label' => 'Kelola Konten Website',
                'description' => 'Menambah, mengubah, mengurutkan, dan menghapus konten serta gambar website.',
            ],
        ],
        'Laporan & Audit' => [
            'reports.activity.view' => [
                'label' => 'Log Aktivitas (Audit Trail)',
                'description' => 'Melihat seluruh jejak audit aktivitas pengguna sistem.',
            ],
        ],
        'Pengaturan & Keamanan' => [
            'settings.branding.manage' => [
                'label' => 'Branding Aplikasi',
                'description' => 'Mengatur nama, tagline, dan logo dashboard ini.',
            ],
            'roles.manage' => [
                'label' => 'Kelola Peran & Perizinan',
                'description' => 'Membuat peran baru, mengubah perizinan, dan mengatur matriks akses.',
            ],
            'users.manage' => [
                'label' => 'Kelola Penugasan Peran Pengguna',
                'description' => 'Menugaskan dan mencabut peran dari akun pengguna sistem.',
            ],
        ],
    ];

    /**
     * Izin bawaan per peran; "Kembalikan ke default" di halaman Peran & Perizinan memakai daftar ini.
     *
     * @var array<string, list<string>>
     */
    public const DEFAULT_ROLE_PERMISSIONS = [
        'superadmin' => ['*'],
        'admin' => [
            'content.view', 'content.manage',
            'reports.activity.view',
            'settings.branding.manage',
            'users.manage',
        ],
        'staff' => [
            'content.view',
        ],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = [];
        foreach (self::PERMISSION_GROUPS as $permissions) {
            foreach ($permissions as $permissionName => $meta) {
                Permission::findOrCreate($permissionName, 'web');
                $allPermissions[] = $permissionName;
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::DEFAULT_ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            if ($permissions === ['*']) {
                $role->syncPermissions($allPermissions);
            } else {
                $role->syncPermissions($permissions);
            }
        }
    }
}
