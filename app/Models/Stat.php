<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Orderable;
use Database\Factories\StatFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use Auditable, Orderable;

    /** @use HasFactory<StatFactory> */
    use HasFactory;

    public const GROUP_SUMMARY = 'summary';

    public const GROUP_ROADMAP = 'roadmap';

    public const GROUPS = [
        self::GROUP_SUMMARY => 'Ringkasan di bawah hero',
        self::GROUP_ROADMAP => 'Ringkasan rekam jejak',
    ];

    protected $fillable = ['group', 'value', 'suffix', 'label', 'caption', 'tone', 'sort_order'];

    /**
     * @param  Builder<static>  $query
     */
    public function scopeInGroup(Builder $query, string $group): void
    {
        $query->where('group', $group);
    }

    protected function orderGroup(): array
    {
        return ['group'];
    }

    protected function casts(): array
    {
        return ['value' => 'integer'];
    }
}
