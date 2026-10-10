<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Orderable;
use Database\Factories\EducationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    use Auditable, Orderable;

    /** @use HasFactory<EducationFactory> */
    use HasFactory;

    /**
     * Warna label periode di kartu pendidikan.
     */
    public const TONES = [
        'active' => 'Sedang berjalan',
        'done' => 'Sudah selesai',
        'program' => 'Program pembinaan',
    ];

    protected $table = 'educations';

    protected $fillable = [
        'name', 'program', 'period_label', 'tone', 'city', 'description', 'footnote',
        'location_id', 'is_published', 'sort_order',
    ];

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
