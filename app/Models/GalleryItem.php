<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Orderable;
use Database\Factories\GalleryItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryItem extends Model
{
    use Auditable, Orderable;

    /** @use HasFactory<GalleryItemFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id', 'title', 'description', 'badge', 'place', 'taken_on', 'image_id',
        'is_published', 'sort_order',
    ];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    protected function casts(): array
    {
        return [
            'taken_on' => 'date',
            'is_published' => 'boolean',
        ];
    }
}
