<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Orderable;
use Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    use Auditable, Orderable;

    /** @use HasFactory<AchievementFactory> */
    use HasFactory;

    public const MEDALS = [
        'emas' => 'Emas / Juara 1',
        'perak' => 'Perak / Juara 2',
        'perunggu' => 'Perunggu / Juara 3',
    ];

    public const LEVELS = [
        'internasional' => 'Internasional',
        'nasional' => 'Nasional',
        'provinsi' => 'Provinsi',
        'kota' => 'Kota / Kabupaten',
        'sekolah' => 'Sekolah',
    ];

    protected $fillable = [
        'title', 'organizer', 'description', 'badge', 'icon', 'tone', 'medal', 'level',
        'achieved_on', 'tags', 'reference_no', 'keywords', 'is_featured', 'image_id',
        'gallery_item_id', 'location_id', 'cert_title', 'cert_organizer', 'cert_number',
        'cert_predicate', 'cert_description', 'cert_tags', 'is_published', 'sort_order',
    ];

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    /**
     * @return BelongsTo<GalleryItem, $this>
     */
    public function galleryItem(): BelongsTo
    {
        return $this->belongsTo(GalleryItem::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    protected function casts(): array
    {
        return [
            'achieved_on' => 'date',
            'tags' => 'array',
            'cert_tags' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }
}
