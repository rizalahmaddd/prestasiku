<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * Interaksi pengunjung di halaman publik (membuka prestasi, klik WhatsApp, dst.).
 */
class SiteEvent extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    /**
     * Nama interaksi yang diterima dari halaman publik beserta labelnya di laporan.
     *
     * @var array<string, string>
     */
    public const NAMES = [
        'section' => 'Bagian yang dilihat',
        'achievement' => 'Prestasi dibuka',
        'photo' => 'Foto dibuka',
        'location' => 'Lokasi dilihat',
        'whatsapp' => 'Klik WhatsApp',
        'email' => 'Salin email',
        'message' => 'Kirim pesan',
        'resume' => 'Unduh resume',
    ];

    protected $fillable = ['visitor', 'occurred_on', 'name', 'label'];

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('occurred_on', '<', now()->subMonths(SiteVisit::RETENTION_MONTHS)->toDateString());
    }
}
