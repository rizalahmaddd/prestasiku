<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu kali halaman publik dibuka. Tidak menyimpan IP maupun cookie: `visitor` adalah hash
 * yang berganti tiap hari (lihat App\Support\Site\VisitRecorder).
 */
class SiteVisit extends Model
{
    use MassPrunable;

    public const RETENTION_MONTHS = 14;

    public const UPDATED_AT = null;

    protected $fillable = [
        'visitor', 'visited_on', 'hour', 'channel', 'source', 'referrer_host', 'campaign',
        'country', 'city', 'device', 'browser', 'os',
    ];

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('visited_on', '<', now()->subMonths(self::RETENTION_MONTHS)->toDateString());
    }

    protected function casts(): array
    {
        // visited_on sengaja tanpa cast: cast 'date' menyimpan "Y-m-d 00:00:00" di SQLite, dan
        // perbandingan rentang tanggal (string) jadi melewatkan hari terakhir.
        return ['hour' => 'integer'];
    }
}
