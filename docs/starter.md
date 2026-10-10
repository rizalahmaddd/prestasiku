# Catatan Starter (JagoDev Laravel Starter)

Prestasiku dibangun di atas [JagoDev Laravel Starter](https://github.com/rizalahmaddd/jagodev-laravel-starter), jadi fondasi berikut ikut tersedia:

- **Auth**: login dengan username/email/nomor HP, OTP WhatsApp (Fonnte), reset password, verifikasi email, profil. Registrasi publik ditutup; akun pertama dibuat lewat `php artisan app:install`.
- **RBAC**: `spatie/laravel-permission`, dengan halaman Peran & Perizinan (matriks izin, peran kustom, penugasan peran ke user). Superadmin melewati semua cek izin.
- **Sakelar fitur**: Superadmin bisa mematikan modul/fitur mana pun dari Pengaturan Fitur tanpa menyentuh data maupun izin.
- **Audit trail**: setiap create/update/delete model `Auditable` tercatat beserta snapshot before/after, ditambah log login dan ekspor, di halaman Log Aktivitas.
- **REST API v1**: token Sanctum untuk login dan akun, dengan dokumentasi OpenAPI yang digenerate otomatis (Scalar di `/docs/api`).
- **Realtime**: Reverb + Echo untuk lonceng notifikasi tanpa reload.
- **Backup & restore**, **branding** dashboard (nama, logo), dan **UI kit** komponen Blade dengan tema gelap/terang serta layout mobile.

Modul contoh Pelanggan dari starter sudah dihapus. Modul konten Prestasiku ada di `app/Livewire/Content`; cara menambahnya dijelaskan di [Arsitektur](arsitektur.md#menambah-modul-konten).

## Peta fondasi

| Bagian | Lokasi |
|---|---|
| Menu sidebar, flyout, bottom nav mobile, pencarian menu | `app/Support/Navigation.php` |
| Daftar modul & fitur yang bisa dimatikan | `app/Support/Features.php` |
| Daftar izin & izin bawaan per peran | `database/seeders/PermissionSeeder.php` |
| Plumbing CRUD Livewire (search, sort, paginasi, modal, hapus, ekspor) | `app/Livewire/Concerns/WithCrudActions.php`, `WithDataTable.php` |
| Audit trail | trait `app/Models/Concerns/Auditable.php`, label di `app/Support/Audit/AuditTrail.php` |
| Pencarian global (⌘K) | `resources/views/livewire/layout/global-search.blade.php` |
| Generator OpenAPI | `app/Support/OpenApi/` |
| Branding | `app/Support/Branding.php` |
| Backup & restore | `app/Services/BackupService.php` |

## Catatan lingkungan

- Broadcasting memakai Reverb (`BROADCAST_CONNECTION=reverb`). `composer run dev` sudah menyalakannya; tanpa Reverb, dashboard tetap berfungsi.
- Notifikasi dan backup berjalan lewat queue (`QUEUE_CONNECTION=database`), jadi di production perlu worker (`php artisan queue:work`) dan scheduler (`php artisan schedule:run` tiap menit).
- Export dokumentasi API ke file: `php artisan api:docs --output=storage/app/openapi.json`.
- Deploy: `.github/workflows/deploy.yml` memakai repository variable `DEPLOY_PATH`.
