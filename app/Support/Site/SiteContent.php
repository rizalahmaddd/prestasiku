<?php

namespace App\Support\Site;

use App\Models\Media;
use App\Models\Setting;

/**
 * Teks dan gambar halaman publik yang bukan berupa daftar (identitas, hero, judul bagian,
 * kontak, SEO). Disimpan di tabel settings dengan awalan "site."; halaman Konten Halaman
 * membangun formulirnya langsung dari TABS, jadi kolom baru cukup ditambahkan di sini.
 */
class SiteContent
{
    public const PREFIX = 'site.';

    public const RESUME_KEY = 'resume_path';

    /**
     * @var array<string, array{label: string, icon: string, hint: string, sections: list<array{title: string, description?: string, fields: array<string, array{label: string, type?: string, hint?: string, placeholder?: string, default?: string, max?: int, required?: bool, half?: bool}>}>}>
     */
    public const TABS = [
        'identitas' => [
            'label' => 'Identitas',
            'icon' => 'user-round',
            'hint' => 'Nama, foto, dan sekolah',
            'sections' => [
                [
                    'title' => 'Identitas siswa',
                    'description' => 'Tampil di kepala halaman, kartu profil, dan kaki halaman.',
                    'fields' => [
                        'name' => ['label' => 'Nama lengkap', 'default' => 'Nama Siswa', 'max' => 80, 'required' => true, 'half' => true],
                        'grade' => ['label' => 'Kelas / jenjang', 'placeholder' => 'Contoh: Kelas IX', 'max' => 40, 'half' => true],
                        'school_line' => ['label' => 'Sekolah dan kota', 'placeholder' => 'Contoh: SMP Negeri 1, Jakarta', 'max' => 120],
                        'tagline' => ['label' => 'Keterangan singkat', 'placeholder' => 'Contoh: Pelajar & Peneliti Muda', 'max' => 80, 'half' => true],
                        'birth_date' => ['label' => 'Tanggal lahir', 'type' => 'date', 'hint' => 'Dipakai untuk menghitung umur di kartu profil. Kosongkan kalau umur tidak ingin ditampilkan.', 'half' => true],
                        'portrait_id' => ['label' => 'Foto profil', 'type' => 'image', 'hint' => 'Foto tegak (potret) paling pas di kartu profil.'],
                    ],
                ],
                [
                    'title' => 'Sorotan di kartu profil',
                    'description' => 'Tiga baris ringkas di bawah foto (hanya tampil di layar lebar). Baris yang kosong disembunyikan.',
                    'fields' => [
                        'highlight_1_label' => ['label' => 'Baris 1: judul', 'placeholder' => 'Sekolah Asal', 'max' => 40, 'half' => true],
                        'highlight_1_value' => ['label' => 'Baris 1: isi', 'max' => 80, 'half' => true],
                        'highlight_2_label' => ['label' => 'Baris 2: judul', 'placeholder' => 'Peringkat Akademik', 'max' => 40, 'half' => true],
                        'highlight_2_value' => ['label' => 'Baris 2: isi', 'max' => 80, 'half' => true],
                        'highlight_3_label' => ['label' => 'Baris 3: judul', 'placeholder' => 'Fokus Bidang', 'max' => 40, 'half' => true],
                        'highlight_3_value' => ['label' => 'Baris 3: isi', 'max' => 80, 'half' => true],
                    ],
                ],
            ],
        ],
        'hero' => [
            'label' => 'Hero',
            'icon' => 'layout-template',
            'hint' => 'Bagian paling atas',
            'sections' => [
                [
                    'title' => 'Bagian pembuka',
                    'description' => 'Yang pertama dilihat pengunjung saat membuka website.',
                    'fields' => [
                        'hero_badge' => ['label' => 'Label kecil di atas judul', 'default' => 'Portofolio Akademik', 'max' => 60],
                        'hero_headline' => ['label' => 'Judul utama', 'default' => 'Rekam Jejak Belajar &', 'max' => 80, 'required' => true, 'half' => true],
                        'hero_headline_accent' => ['label' => 'Bagian judul yang disorot', 'default' => 'Prestasi.', 'hint' => 'Ditulis miring berwarna biru dan diberi garis bawah.', 'max' => 60, 'half' => true],
                        'hero_intro' => ['label' => 'Paragraf pembuka', 'type' => 'textarea', 'hint' => 'Apit kata dengan dua bintang untuk menebalkannya, mis. **Nama Siswa**.', 'max' => 500],
                        'hero_cta_primary' => ['label' => 'Tombol utama', 'default' => 'Lihat Koleksi Prestasi', 'max' => 40, 'half' => true],
                        'hero_cta_secondary' => ['label' => 'Tombol kedua', 'default' => 'Riwayat Pendidikan', 'max' => 40, 'half' => true],
                    ],
                ],
            ],
        ],
        'bagian' => [
            'label' => 'Judul Bagian',
            'icon' => 'heading',
            'hint' => 'Judul dan pengantar tiap bagian',
            'sections' => [
                [
                    'title' => 'Profil & bidang minat',
                    'fields' => [
                        'about_eyebrow' => ['label' => 'Label kecil', 'default' => 'Profil & Karakter', 'max' => 60, 'half' => true],
                        'about_title' => ['label' => 'Judul', 'default' => 'Profil dan Bidang Minat', 'max' => 120, 'half' => true],
                        'about_body' => ['label' => 'Pengantar', 'type' => 'textarea', 'max' => 500],
                    ],
                ],
                [
                    'title' => 'Pendidikan & peta',
                    'fields' => [
                        'education_eyebrow' => ['label' => 'Label kecil', 'default' => 'Perjalanan Akademik', 'max' => 60, 'half' => true],
                        'education_title' => ['label' => 'Judul', 'default' => 'Riwayat Pendidikan & Peta Lokasi', 'max' => 120, 'half' => true],
                        'education_body' => ['label' => 'Pengantar', 'type' => 'textarea', 'max' => 500],
                        'venues_label' => ['label' => 'Judul tombol lokasi lain', 'default' => 'Lokasi Lainnya', 'hint' => 'Lokasi yang tidak terhubung ke riwayat pendidikan muncul sebagai tombol di bawah kartu.', 'max' => 40],
                    ],
                ],
                [
                    'title' => 'Rekam jejak',
                    'fields' => [
                        'roadmap_eyebrow' => ['label' => 'Label kecil', 'default' => 'Perkembangan Berkelanjutan', 'max' => 60, 'half' => true],
                        'roadmap_title' => ['label' => 'Judul', 'default' => 'Alur Rekam Jejak', 'max' => 120, 'half' => true],
                        'roadmap_body' => ['label' => 'Pengantar', 'type' => 'textarea', 'max' => 500],
                    ],
                ],
                [
                    'title' => 'Prestasi & galeri',
                    'fields' => [
                        'showcase_eyebrow' => ['label' => 'Label kecil', 'default' => 'Portofolio Prestasi', 'max' => 60, 'half' => true],
                        'showcase_title' => ['label' => 'Judul', 'default' => 'Koleksi Prestasi & Galeri Dokumentasi', 'max' => 120, 'half' => true],
                        'showcase_body' => ['label' => 'Pengantar', 'type' => 'textarea', 'max' => 500],
                        'gallery_title' => ['label' => 'Judul album galeri', 'default' => 'Album Foto Kegiatan', 'max' => 120, 'half' => true],
                        'gallery_body' => ['label' => 'Keterangan album galeri', 'max' => 200, 'half' => true],
                    ],
                ],
                [
                    'title' => 'Testimoni',
                    'fields' => [
                        'testimonials_eyebrow' => ['label' => 'Label kecil', 'default' => 'Rekomendasi Pendidik', 'max' => 60, 'half' => true],
                        'testimonials_title' => ['label' => 'Judul', 'default' => 'Catatan Guru & Pembina', 'max' => 120, 'half' => true],
                    ],
                ],
            ],
        ],
        'kontak' => [
            'label' => 'Kontak & Resume',
            'icon' => 'phone',
            'hint' => 'WhatsApp, email, berkas PDF',
            'sections' => [
                [
                    'title' => 'Narahubung',
                    'description' => 'Tombol WhatsApp dan email hanya tampil kalau diisi.',
                    'fields' => [
                        'whatsapp' => ['label' => 'Nomor WhatsApp', 'type' => 'phone', 'placeholder' => '0812xxxxxxxx', 'hint' => 'Boleh diawali 0 atau 62; ditampilkan sebagai +62 di website.', 'max' => 20, 'half' => true],
                        'email' => ['label' => 'Email', 'type' => 'email', 'max' => 120, 'half' => true],
                        'whatsapp_message' => ['label' => 'Pesan WhatsApp otomatis', 'type' => 'textarea', 'hint' => 'Teks yang sudah terisi saat pengunjung membuka chat.', 'max' => 300],
                        'contact_cta' => ['label' => 'Tombol di kepala halaman', 'default' => 'Hubungi Wali', 'max' => 30],
                    ],
                ],
                [
                    'title' => 'Bagian kontak',
                    'fields' => [
                        'contact_badge' => ['label' => 'Label kecil', 'default' => 'Narahubung Orang Tua / Wali', 'max' => 60, 'half' => true],
                        'contact_title' => ['label' => 'Judul', 'default' => 'Tertarik Mengundang atau Bekerja Sama?', 'max' => 120, 'half' => true],
                        'contact_body' => ['label' => 'Pengantar', 'type' => 'textarea', 'max' => 500],
                    ],
                ],
                [
                    'title' => 'Resume PDF',
                    'description' => 'Tombol Resume PDF di website hanya muncul setelah berkasnya diunggah.',
                    'fields' => [
                        'resume_title' => ['label' => 'Judul jendela unduh', 'default' => 'Unduh Resume', 'max' => 60, 'half' => true],
                        'resume_body' => ['label' => 'Keterangan isi berkas', 'max' => 200, 'half' => true],
                    ],
                ],
            ],
        ],
        'seo' => [
            'label' => 'SEO & Footer',
            'icon' => 'search',
            'hint' => 'Judul tab, pratinjau tautan',
            'sections' => [
                [
                    'title' => 'Mesin pencari & pratinjau tautan',
                    'description' => 'Dipakai Google dan saat tautan website dibagikan di WhatsApp atau media sosial.',
                    'fields' => [
                        'seo_title' => ['label' => 'Judul halaman', 'hint' => 'Kosongkan untuk memakai "Portofolio Prestasi" + nama.', 'max' => 70],
                        'seo_description' => ['label' => 'Deskripsi singkat', 'type' => 'textarea', 'max' => 200],
                        'og_image_id' => ['label' => 'Gambar pratinjau tautan', 'type' => 'image', 'hint' => 'Mendatar, idealnya 1200 x 630. Kosongkan untuk memakai foto profil.'],
                    ],
                ],
                [
                    'title' => 'Kaki halaman',
                    'fields' => [
                        'footer_title' => ['label' => 'Judul', 'hint' => 'Kosongkan untuk memakai nama + "Portofolio Siswa".', 'max' => 80, 'half' => true],
                        'footer_note' => ['label' => 'Catatan pengelola', 'placeholder' => 'Contoh: Dikelola oleh keluarga', 'max' => 120, 'half' => true],
                    ],
                ],
            ],
        ],
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function fields(?string $tab = null): array
    {
        $fields = [];

        foreach (self::TABS as $key => $definition) {
            if ($tab !== null && $tab !== $key) {
                continue;
            }

            foreach ($definition['sections'] as $section) {
                $fields += $section['fields'];
            }
        }

        return $fields;
    }

    public static function get(string $key): string
    {
        $value = Setting::get(self::PREFIX.$key);

        return $value ?? (string) (self::fields()[$key]['default'] ?? '');
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        $values = [];

        foreach (array_keys(self::fields()) as $key) {
            $values[$key] = self::get($key);
        }

        return $values;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function putMany(array $values): void
    {
        $known = self::fields();

        foreach ($values as $key => $value) {
            if (isset($known[$key]) || $key === self::RESUME_KEY) {
                Setting::put(self::PREFIX.$key, $value === null ? null : (string) $value);
            }
        }
    }

    /**
     * @return list<string>
     */
    public static function imageKeys(): array
    {
        return array_keys(array_filter(self::fields(), fn (array $field): bool => ($field['type'] ?? 'text') === 'image'));
    }

    public static function image(string $key): ?Media
    {
        $id = (int) self::get($key);

        return $id > 0 ? Media::query()->find($id) : null;
    }

    public static function resumePath(): ?string
    {
        return Setting::get(self::PREFIX.self::RESUME_KEY) ?: null;
    }

    /**
     * Nomor WhatsApp dalam bentuk yang diterima wa.me (62xxxxxxxxxx), atau null kalau kosong.
     */
    public static function whatsappNumber(): ?string
    {
        return self::normalizePhone(self::get('whatsapp'));
    }

    /**
     * "0812-3456-7890" atau "+62 812..." menjadi 6281234567890; null kalau tidak ada angkanya.
     */
    public static function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }

    public static function whatsappUrl(): ?string
    {
        $number = self::whatsappNumber();

        if ($number === null) {
            return null;
        }

        $message = self::get('whatsapp_message');

        return 'https://wa.me/'.$number.($message !== '' ? '?text='.rawurlencode($message) : '');
    }

    /**
     * 6281234567890 menjadi "+62 812-3456-7890" untuk label tombol.
     */
    public static function whatsappLabel(): ?string
    {
        $number = self::whatsappNumber();

        if ($number === null) {
            return null;
        }

        if (! str_starts_with($number, '62')) {
            return '+'.$number;
        }

        $local = substr($number, 2);

        return '+62 '.implode('-', array_filter([substr($local, 0, 3), substr($local, 3, 4), substr($local, 7)]));
    }
}
