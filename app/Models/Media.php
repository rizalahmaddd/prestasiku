<?php

namespace App\Models;

use App\Support\Site\SiteContent;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Satu gambar di Pustaka Media, tersimpan di disk public supaya bisa dibuka pengunjung website.
 */
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    public const DISK = 'public';

    /**
     * Kolom yang menunjuk ke media. Dipakai untuk menghitung pemakaian sebelum gambar dihapus.
     *
     * @var array<class-string<Model>, string>
     */
    public const REFERENCES = [
        Achievement::class => 'image_id',
        GalleryItem::class => 'image_id',
        Location::class => 'image_id',
        Testimonial::class => 'image_id',
    ];

    protected $table = 'media';

    protected $fillable = ['name', 'path', 'mime', 'size', 'width', 'height'];

    protected static function booted(): void
    {
        static::deleted(function (Media $media): void {
            Storage::disk(self::DISK)->delete($media->path);
        });
    }

    public function url(): string
    {
        // Relatif ke host yang sedang dibuka, supaya gambar tetap tampil walau APP_URL belum disetel benar.
        return asset('storage/'.$this->path);
    }

    public function usageCount(): int
    {
        $count = 0;

        foreach (self::REFERENCES as $model => $column) {
            $count += $model::query()->where($column, $this->id)->count();
        }

        foreach (SiteContent::imageKeys() as $key) {
            if ((int) SiteContent::get($key) === $this->id) {
                $count++;
            }
        }

        return $count;
    }
}
