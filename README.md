# Prestasiku

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3_+_Volt-FB70A9?logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Leaflet](https://img.shields.io/badge/Leaflet-1.9-199900?logo=leaflet&logoColor=white)](https://leafletjs.com)
[![Pest](https://img.shields.io/badge/Tests-Pest-F472B6?logo=pest&logoColor=white)](docs/instalasi.md#perintah-pengembangan)
[![Demo](https://img.shields.io/badge/Demo-prestasiku.solusikoding.com-0b6e4f?logo=googlechrome&logoColor=white)](https://prestasiku.solusikoding.com/)

**Stack:** Laravel 13 · PHP 8.3+ · Livewire 3 + Volt + Alpine.js · Tailwind CSS · [Leaflet](https://leafletjs.com) untuk peta · [Lucide](https://lucide.dev) untuk ikon · SQLite · [spatie/laravel-permission](https://github.com/spatie/laravel-permission) · Fonnte (OTP WhatsApp) · Laravel Reverb · Pest · Larastan · Pint

Website portofolio prestasi siswa beserta CMS untuk mengelolanya. Halaman publik menampilkan profil, kartu prestasi berfoto, galeri, riwayat pendidikan, dan peta lokasi lomba. Semua isinya diatur dari dashboard setelah login, tanpa menyentuh kode.

🌐 **Coba demo:** [prestasiku.solusikoding.com](https://prestasiku.solusikoding.com/)

## Fitur

- **Halaman portofolio publik** di `/`, dengan navigasi mobile ala aplikasi
- **Kartu prestasi** berisi foto, detail piagam, kategori, dan lokasi; pengunjung bisa menyaringnya per kategori
- **Galeri foto** dokumentasi beserta keterangannya
- **Profil & perjalanan**: angka ringkasan, bidang minat, riwayat pendidikan, rekam jejak, dan testimoni
- **Peta lokasi** lomba dan sekolah, titiknya dipilih lewat map picker dengan pencarian alamat
- **Konten halaman** bisa diedit dari dashboard: identitas siswa, hero, judul tiap bagian, kontak, resume PDF, SEO, dan footer
- **Formulir kontak** dengan lampiran foto atau PDF; pesan masuk bisa dibalas lewat WhatsApp atau email dan diberi catatan tindak lanjut
- **Statistik pengunjung** tanpa cookie dan tanpa layanan pihak ketiga: jumlah kunjungan, sumber, lokasi, perangkat, dan jam ramai
- **Pustaka media**: gambar yang pernah diunggah bisa dipakai ulang, dan gambar besar dikecilkan otomatis
- **Peran & perizinan**, sakelar fitur, log aktivitas, serta backup & restore

## Cara kerja

1. Login ke dashboard, lalu isi identitas siswa dan teks halaman di menu Konten Halaman.
2. Tambahkan prestasi, foto galeri, riwayat pendidikan, dan lokasi.
3. Bagikan tautan halaman publik. Tautan berpelacak dari halaman Statistik mencatat asal pengunjungnya.
4. Pantau statistik kunjungan dan balas pesan yang masuk dari formulir kontak.

## Mulai cepat

```bash
composer run setup   # instal dependency, siapkan .env, buat akun superadmin
composer run dev     # buka http://localhost:8000
```

Jawab "ya" saat installer menawarkan konten contoh supaya halaman publik langsung terisi portofolio demo beserta fotonya.

## Dokumentasi

- [Instalasi & konfigurasi](docs/instalasi.md): kebutuhan server, installer, data demo, perintah pengembangan
- [Arsitektur](docs/arsitektur.md): peta kode, cara kerja statistik pengunjung, cara menambah kolom teks dan modul konten
- [Catatan starter](docs/starter.md): fondasi JagoDev Laravel Starter yang ikut terpakai

## Dukung & Donasi

Jika proyek ini bermanfaat bagi Anda, dukung pengembangannya melalui **QRIS**:

<p align="center">
  <img src="docs/qris.png" width="240" alt="QRIS Donasi - RZ Printing" />
  <br>
  <em>Scan QRIS menggunakan BCA, Mandiri, BRI, GoPay, OVO, DANA, ShopeePay, atau mobile banking lainnya.</em>
</p>

## Kontak

Dikembangkan oleh **rizalahmaddd**:
- **WhatsApp**: [+62 857-7777-5477](https://wa.me/6285777775477)
- **GitHub**: [@rizalahmaddd](https://github.com/rizalahmaddd)
- **Lokasi**: Kota Malang, Jawa Timur, Indonesia
