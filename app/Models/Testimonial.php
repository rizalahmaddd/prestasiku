<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Orderable;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use Auditable, Orderable;

    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    protected $fillable = ['quote', 'name', 'role', 'image_id', 'is_published', 'sort_order'];

    /**
     * @return BelongsTo<Media, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
