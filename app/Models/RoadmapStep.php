<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Orderable;
use Database\Factories\RoadmapStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadmapStep extends Model
{
    use Auditable, Orderable;

    /** @use HasFactory<RoadmapStepFactory> */
    use HasFactory;

    public const STATES = [
        'normal' => 'Biasa',
        'peak' => 'Puncak (disorot)',
        'next' => 'Tahap berikutnya (garis putus)',
    ];

    protected $fillable = ['period', 'title', 'description', 'tag', 'state', 'jump_target', 'sort_order'];
}
