# Instalasi & Konfigurasi

## Kebutuhan server

- **PHP 8.3+** dengan ekstensi GD. `MediaStore` memakai GD untuk mengecilkan gambar yang sisi terpanjangnya lebih dari 2000 px.
- **Node.js** hanya dibutuhkan saat build aset dashboard (`npm run build`). Halaman publik memuat Tailwind, Lucide, dan Leaflet dari CDN.
- **Queue worker** (`php artisan queue:work`) untuk notifikasi dan backup.
- **Scheduler** (`php artisan schedule:run` tiap menit): backup terjadwal dan `model:prune` harian yang membuang data statistik berumur lebih dari 14 bulan.
- Batas upload PHP (`upload_max_filesize`, `post_max_size`) minimal 10 MB, mengikuti batas unggah gambar di `MediaStore::MAX_UPLOAD_KB`.
- Symlink `public/storage` supaya gambar bisa diakses. `app:install` membuatnya; kalau belum ada, jalankan `php artisan storage:link`.
- **Cloudflare** di depan server kalau ingin lokasi pengunjung terisi. Negara dan kota dibaca dari header `CF-IPCountry` dan `CF-IPCity`.

## Installer

`composer run setup` menginstal dependency, membuat `.env` dan `APP_KEY`, menyiapkan berkas SQLite, menjalankan `php artisan app:install`, lalu membuild aset. Installer menjalankan migrasi, membuat peran dan izin, serta akun superadmin pertama. Jawab "ya" pada pertanyaan konten contoh (atau pakai `--demo`) untuk mengisi website dengan portofolio contoh lengkap dengan fotonya.

Tanpa interaksi, misalnya di server:

```bash
php artisan app:install --no-interaction --demo \
  --app-name="Prestasiku" --name="Admin" --email=admin@example.com --username=admin --password='rahasia-kuat'
```

## Konfigurasi (`.env`)

| Env | Default | Keterangan |
|---|---|---|
| `APP_TIMEZONE` | `Asia/Jakarta` | Zona waktu WIB. |
| `APP_LOCALE` | `id` | Bahasa Indonesia. |
| `DB_CONNECTION` | `sqlite` | Ganti ke MySQL lewat variabel `DB_*` kalau perlu. |
| `QUEUE_CONNECTION` | `database` | Notifikasi dan backup berjalan lewat queue. |
| `BROADCAST_CONNECTION` | `reverb` | Notifikasi realtime. Tanpa Reverb, dashboard tetap berfungsi. |
| `REVERB_PORT` | `8085` | Ganti kalau bentrok dengan service lain. |
| `FONNTE_TOKEN` | kosong | Isi kalau login OTP WhatsApp dipakai. |

## Data demo untuk pengembangan

`php artisan migrate:fresh --seed` mengisi konten contoh (`DemoContentSeeder`, foto di `database/seeders/media`) dan satu akun per peran, semua dengan password `password`:

| Peran | Email | Akses konten |
|---|---|---|
| Superadmin | superadmin@example.test | Semua |
| Admin | admin@example.test | Lihat dan kelola |
| Staff | staff@example.test | Lihat saja |

Jangan jalankan seeder demo di production; pakai `app:install`.

## Perintah pengembangan

```bash
composer run setup             # dependency, .env, APP_KEY, app:install, build aset
composer run dev               # server, queue, scheduler, Vite, Reverb → http://localhost:8000
php artisan test --compact     # Pest
vendor/bin/phpstan analyse     # Larastan
vendor/bin/pint                # format kode
```

Workflow `.github/workflows/tests.yml` menjalankan test dan pemeriksaan kode di GitHub.
