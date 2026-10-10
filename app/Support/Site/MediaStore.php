<?php

namespace App\Support\Site;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyimpan gambar ke Pustaka Media. Foto dari kamera HP biasanya 4000px ke atas dan beberapa MB,
 * jadi gambar yang terlalu lebar dikecilkan dulu supaya halaman publik tetap ringan.
 */
class MediaStore
{
    public const MAX_EDGE = 2000;

    public const MAX_UPLOAD_KB = 10240;

    public const DIRECTORY = 'media';

    /**
     * Tanpa SVG: berkas disajikan dari domain aplikasi sendiri, SVG bisa membawa skrip.
     *
     * @var list<string>
     */
    public const RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_UPLOAD_KB];

    public static function fromUpload(UploadedFile $file): Media
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'gambar';

        return self::fromPath($file->getRealPath(), $name, $file->extension() ?: 'jpg');
    }

    public static function fromPath(string $sourcePath, string $name, ?string $extension = null): Media
    {
        $extension = strtolower($extension ?: pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg');
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        $contents = self::resized($sourcePath, $extension) ?? (string) file_get_contents($sourcePath);

        $path = self::DIRECTORY.'/'.now()->format('Y/m').'/'.Str::slug(Str::limit($name, 60, '')).'-'.Str::lower(Str::random(8)).'.'.$extension;
        Storage::disk(Media::DISK)->put($path, $contents);

        [$width, $height] = @getimagesizefromstring($contents) ?: [null, null];

        return Media::create([
            'name' => Str::limit($name, 150, ''),
            'path' => $path,
            'mime' => match ($extension) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            },
            'size' => strlen($contents),
            'width' => $width,
            'height' => $height,
        ]);
    }

    /**
     * Isi berkas hasil pengecilan, atau null kalau gambarnya sudah cukup kecil / GD tidak tersedia.
     */
    private static function resized(string $sourcePath, string $extension): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $info = @getimagesize($sourcePath);

        if ($info === false || max($info[0], $info[1]) <= self::MAX_EDGE) {
            return null;
        }

        $source = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if ($source === false) {
            return null;
        }

        $source = self::upright($source, $sourcePath, $info[2]);
        $width = imagesx($source);
        $height = imagesy($source);
        $ratio = self::MAX_EDGE / max($width, $height);
        $target = imagecreatetruecolor(max(1, (int) round($width * $ratio)), max(1, (int) round($height * $ratio)));

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, imagesx($target), imagesy($target), $width, $height);

        ob_start();
        match ($extension) {
            'png' => imagepng($target, null, 6),
            'webp' => imagewebp($target, null, 84),
            default => imagejpeg($target, null, 84),
        };

        return ob_get_clean() ?: null;
    }

    /**
     * GD membuang data EXIF, jadi foto HP yang disimpan miring harus diputar dulu sebelum
     * dikecilkan; kalau tidak, hasilnya tampil rebah di website.
     */
    private static function upright(\GdImage $image, string $sourcePath, int $type): \GdImage
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        $angle = match ((int) (@exif_read_data($sourcePath)['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        return $angle === 0 ? $image : (imagerotate($image, $angle, 0) ?: $image);
    }
}
