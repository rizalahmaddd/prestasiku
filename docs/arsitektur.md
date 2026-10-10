# Arsitektur

## Menu dashboard

| Menu | Isi |
|---|---|
| Statistik Pengunjung | Jumlah pengunjung, sumber kunjungan, lokasi, perangkat, jam ramai, dan interaksi di halaman |
| Pesan Masuk | Pesan dari formulir kontak website (boleh berlampiran foto/PDF): baca, balas lewat WhatsApp/email, tandai status, catat tindak lanjut |
| Konten Halaman | Identitas siswa, hero, judul tiap bagian, kontak WhatsApp/email, resume PDF, SEO, footer |
| Portofolio → Prestasi | Kartu prestasi, detail piagam, foto, kategori, lokasi di peta |
| Portofolio → Galeri | Foto dokumentasi beserta keterangannya |
| Portofolio → Kategori | Tombol saringan prestasi dan galeri |
| Profil & Perjalanan | Angka ringkasan, bidang minat, riwayat pendidikan, lokasi (map picker), rekam jejak, testimoni |
| Pustaka Media | Semua gambar yang diunggah |

Formulir memakai pemilih gambar (unggah atau ambil dari pustaka), pemilih titik peta dengan pencarian alamat, pemilih ikon dan warna, serta tombol naik/turun untuk urutan tampil.

## Peta kode

| Bagian | Lokasi |
|---|---|
| Halaman publik | `app/Http/Controllers/SiteController.php`, `resources/views/site/home.blade.php`, `public/site/site.{css,js}` |
| Teks halaman (identitas, hero, kontak, SEO) | `app/Support/Site/SiteContent.php` (formulirnya dibangun dari `TABS`) |
| Halaman CMS | `app/Livewire/Content/*` + `resources/views/livewire/content/*`, dasar bersama `ContentCrud` |
| Pemilih gambar | trait `app/Livewire/Concerns/WithMediaPicker.php`, partial `livewire/content/partials/image-picker` |
| Pemilih peta | `resources/js/map-picker.js`, partial `livewire/content/partials/map-picker` |
| Penyimpanan & pengecilan gambar | `app/Support/Site/MediaStore.php` |
| Urutan tampil | trait `app/Models/Concerns/Orderable.php` |
| Formulir kontak | modal di `home.blade.php` + `initContactForm()` di `site.js` → `ContactMessageController`; kotak masuk di `app/Livewire/Content/Messages.php` |
| Statistik pengunjung | `public/site/track.js` → `SiteTrackController` → `app/Support/Site/VisitRecorder.php`; laporan di `AnalyticsReport.php` |
| Menu, sakelar fitur, izin | `app/Support/Navigation.php`, `app/Support/Features.php`, `database/seeders/PermissionSeeder.php` |

Halaman publik punya tema terang sendiri dan memuat Tailwind, Lucide, dan Leaflet dari CDN, sama seperti versi statis awalnya. `DESIGN.md` hanya berlaku untuk dashboard.

## Statistik pengunjung

- Tanpa cookie dan tanpa layanan pihak ketiga. Pengunjung dikenali lewat hash harian dari IP + user agent; IP tidak disimpan.
- Bot, pratinjau tautan (mis. WhatsApp), dan pengelola yang sedang login tidak dihitung.
- Sumber kunjungan dibaca dari `utm_source` di tautan, lalu alamat perujuk. Tautan dari WhatsApp tidak membawa perujuk, jadi bagikan tautan berpelacak dari halaman Statistik supaya tercatat.
- Negara dan kota dibaca dari header Cloudflare (`CF-IPCountry`, `CF-IPCity`). Tanpa Cloudflare, bagian lokasi kosong.
- Data disimpan 14 bulan; pembersihan berjalan lewat scheduler (`model:prune`).

## Menambah kolom teks di halaman publik

Tambahkan satu entri di `SiteContent::TABS`, lalu pakai `$content['kunci']` di `home.blade.php`. Formulir, validasi, dan penyimpanannya mengikuti otomatis.

## Menambah modul konten

Salin pola Prestasi (`app/Livewire/Content/Achievements.php` dan view-nya), lalu daftarkan:

1. Route di `routes/content.php`.
2. Izin di `PermissionSeeder::PERMISSION_GROUPS` dan `DEFAULT_ROLE_PERMISSIONS`.
3. Fitur di `Features::MODULES` beserta pola nama route-nya.
4. Menu di `Navigation::items()`.
5. Satu bagian di `$sections` pada `resources/views/livewire/layout/global-search.blade.php`.
6. Label audit di `AuditTrail::SUBJECT_LABELS`.

`FeatureTogglesTest` gagal kalau ada route yang belum terdaftar di `Features::MODULES`.
