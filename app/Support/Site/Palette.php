<?php

namespace App\Support\Site;

/**
 * Pilihan warna dan ikon yang ditawarkan di formulir konten, beserta kelas Tailwind yang
 * dipakai halaman publik. Halaman publik memakai palet terang sendiri (royal = biru utama).
 */
class Palette
{
    /**
     * @var array<string, array{label: string, swatch: string, text: string, soft: string}>
     */
    public const TONES = [
        'royal' => ['label' => 'Biru', 'swatch' => '#1d4ed8', 'text' => 'text-royal-700', 'soft' => 'bg-royal-50 text-royal-700'],
        'amber' => ['label' => 'Kuning emas', 'swatch' => '#d97706', 'text' => 'text-amber-600', 'soft' => 'bg-amber-50 text-amber-700'],
        'emerald' => ['label' => 'Hijau', 'swatch' => '#047857', 'text' => 'text-emerald-700', 'soft' => 'bg-emerald-50 text-emerald-700'],
        'sky' => ['label' => 'Biru langit', 'swatch' => '#0284c7', 'text' => 'text-sky-700', 'soft' => 'bg-sky-50 text-sky-700'],
        'rose' => ['label' => 'Merah', 'swatch' => '#e11d48', 'text' => 'text-rose-700', 'soft' => 'bg-rose-50 text-rose-700'],
        'slate' => ['label' => 'Abu gelap', 'swatch' => '#0f172a', 'text' => 'text-slate-900', 'soft' => 'bg-slate-100 text-slate-700'],
    ];

    /**
     * Warna penanda peta yang ditawarkan sebagai pilihan cepat.
     *
     * @var list<string>
     */
    public const PIN_COLORS = ['#1d4ed8', '#0f172a', '#d97706', '#dc2626', '#0284c7', '#047857', '#7c3aed'];

    /**
     * @var array<string, string>
     */
    public const ACHIEVEMENT_ICONS = [
        'medal' => 'Medali',
        'trophy' => 'Piala',
        'award' => 'Penghargaan',
        'crown' => 'Mahkota',
        'star' => 'Bintang',
        'mic' => 'Pidato',
        'book-open' => 'Karya tulis',
        'cpu' => 'Teknologi',
        'flask-conical' => 'Sains',
        'calculator' => 'Matematika',
        'palette' => 'Seni',
        'music' => 'Musik',
        'dumbbell' => 'Olahraga',
        'languages' => 'Bahasa',
        'graduation-cap' => 'Akademik',
        'globe' => 'Internasional',
    ];

    /**
     * @var array<string, string>
     */
    public const GENERAL_ICONS = [
        'flask-conical' => 'Sains',
        'cpu' => 'Teknologi',
        'crown' => 'Catur',
        'calculator' => 'Matematika',
        'book-open' => 'Membaca',
        'pen-line' => 'Menulis',
        'mic' => 'Berbicara',
        'languages' => 'Bahasa',
        'palette' => 'Seni',
        'music' => 'Musik',
        'dumbbell' => 'Olahraga',
        'camera' => 'Fotografi',
        'code' => 'Pemrograman',
        'globe' => 'Global',
        'trophy' => 'Podium',
        'school' => 'Sekolah',
        'users' => 'Organisasi',
        'heart' => 'Sosial',
        'leaf' => 'Lingkungan',
        'star' => 'Lainnya',
    ];

    public static function text(?string $tone): string
    {
        return (self::TONES[$tone] ?? self::TONES['slate'])['text'];
    }

    public static function soft(?string $tone): string
    {
        return (self::TONES[$tone] ?? self::TONES['slate'])['soft'];
    }
}
