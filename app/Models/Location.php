<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Orderable;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use Auditable, Orderable;

    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'short_name', 'subtitle', 'badge', 'status', 'color', 'lat', 'lng', 'zoom',
        'address', 'image_id', 'caption', 'sort_order',
    ];

    /**
     * @return BelongsTo<Media, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    /**
     * @return HasMany<Education, $this>
     */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    /**
     * @return HasMany<Achievement, $this>
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    /**
     * Kunci lokasi di halaman publik (id elemen kartu pendidikan, penanda peta).
     */
    public function mapKey(): string
    {
        return 'loc-'.$this->id;
    }

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'zoom' => 'integer',
        ];
    }
}
