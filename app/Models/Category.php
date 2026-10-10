<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Orderable;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use Auditable, Orderable;

    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    public const TYPE_ACHIEVEMENT = 'achievement';

    public const TYPE_GALLERY = 'gallery';

    public const TYPES = [
        self::TYPE_ACHIEVEMENT => 'Prestasi',
        self::TYPE_GALLERY => 'Galeri',
    ];

    protected $fillable = ['type', 'slug', 'name', 'icon', 'sort_order'];

    /**
     * @param  Builder<static>  $query
     */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * @return BelongsToMany<Achievement, $this>
     */
    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class);
    }

    /**
     * @return HasMany<GalleryItem, $this>
     */
    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    protected function orderGroup(): array
    {
        return ['type'];
    }
}
