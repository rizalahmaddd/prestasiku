<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\GalleryItem;
use App\Models\Interest;
use App\Models\Location;
use App\Models\Media;
use App\Models\RoadmapStep;
use App\Models\Setting;
use App\Models\Stat;
use App\Models\Testimonial;
use App\Support\Features;
use App\Support\Site\Palette;
use App\Support\Site\SiteContent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Halaman publik portofolio. Markup dan interaksinya ada di resources/views/site/home.blade.php
 * dan public/site/site.js; controller ini hanya menyiapkan datanya dari konten yang dikelola
 * lewat menu Website.
 */
class SiteController extends Controller
{
    private const PLACEHOLDER_IMAGE = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9'%3E%3Crect width='16' height='9' fill='%23e2e8f0'/%3E%3C/svg%3E";

    public function home(): View
    {
        $content = SiteContent::all();

        $gallery = GalleryItem::query()->where('is_published', true)->with(['image', 'category'])->ordered()->get();
        $galleryIndex = $gallery->pluck('id')->flip();

        $achievements = Achievement::query()
            ->where('is_published', true)
            ->with(['image', 'galleryItem.image', 'categories', 'location'])
            ->orderByDesc('is_featured')
            ->ordered()
            ->get();

        $locations = Location::query()->with('image')->ordered()->get();
        $educations = Education::query()->where('is_published', true)->with('location')->ordered()->get();
        $defaultLocation = $educations->pluck('location')->filter()->first() ?? $locations->first();
        $educationLocationIds = $educations->pluck('location_id')->filter()->all();

        $galleryCategories = Category::query()->ofType(Category::TYPE_GALLERY)->ordered()->get()
            ->map(fn (Category $category) => $category->setAttribute('items_count', $gallery->where('category_id', $category->id)->count()))
            ->filter(fn (Category $category) => $category->items_count > 0)
            ->values();

        $portrait = SiteContent::image('portrait_id');
        $resumePath = SiteContent::resumePath();
        $resumeExists = $resumePath !== null && Storage::disk(Media::DISK)->exists($resumePath);

        return view('site.home', [
            'content' => $content,
            'name' => $content['name'],
            'firstName' => Str::before($content['name'], ' '),
            'age' => $this->age($content['birth_date']),
            'portrait' => $portrait,
            'ogImage' => SiteContent::image('og_image_id') ?? $portrait,
            'updatedLabel' => $this->lastUpdated()?->translatedFormat('F Y'),
            'summaryStats' => Stat::query()->inGroup(Stat::GROUP_SUMMARY)->ordered()->get(),
            'roadmapStats' => Stat::query()->inGroup(Stat::GROUP_ROADMAP)->ordered()->get(),
            'interests' => Interest::query()->ordered()->get(),
            'educations' => $educations,
            'locations' => $locations,
            'defaultLocation' => $defaultLocation,
            'venues' => $locations->reject(fn (Location $location) => in_array($location->id, $educationLocationIds, true))->values(),
            'roadmapSteps' => RoadmapStep::query()->ordered()->get(),
            'achievements' => $achievements,
            'achievementCategories' => Category::query()->ofType(Category::TYPE_ACHIEVEMENT)->ordered()->get(),
            'gallery' => $gallery,
            'galleryCategories' => $galleryCategories,
            'testimonials' => Testimonial::query()->where('is_published', true)->with('image')->ordered()->get(),
            'whatsappUrl' => SiteContent::whatsappUrl(),
            'whatsappLabel' => SiteContent::whatsappLabel(),
            'contactFormEnabled' => Features::enabled('content.messages'),
            'contactTopics' => ContactMessage::TOPICS,
            'trackVisits' => Features::enabled('content.analytics') && auth()->guest(),
            'resumeSize' => $resumeExists ? Storage::disk(Media::DISK)->size($resumePath) : null,
            'payload' => [
                'name' => $content['name'],
                'email' => $content['email'],
                'placeholder' => self::PLACEHOLDER_IMAGE,
                'levels' => Achievement::LEVELS,
                'defaultLocation' => $defaultLocation?->mapKey(),
                'locations' => $this->locationPayload($locations),
                'gallery' => $this->galleryPayload($gallery),
                'achievements' => $this->achievementPayload($achievements, $galleryIndex),
                'certs' => $this->certificatePayload($achievements),
            ],
        ]);
    }

    public function resume(): StreamedResponse
    {
        $path = SiteContent::resumePath();

        abort_unless($path !== null && Storage::disk(Media::DISK)->exists($path), 404);

        return Storage::disk(Media::DISK)->response($path, 'Resume '.SiteContent::get('name').'.pdf');
    }

    private function age(string $birthDate): ?int
    {
        if ($birthDate === '') {
            return null;
        }

        try {
            return (int) Carbon::parse($birthDate)->age;
        } catch (\Throwable) {
            return null;
        }
    }

    private function lastUpdated(): ?Carbon
    {
        $latest = collect([
            Achievement::query()->max('updated_at'),
            GalleryItem::query()->max('updated_at'),
            Education::query()->max('updated_at'),
            Setting::query()->where('key', 'like', SiteContent::PREFIX.'%')->max('updated_at'),
        ])->filter()->max();

        return $latest ? Carbon::parse($latest) : null;
    }

    /**
     * @param  Collection<int, Location>  $locations
     * @return array<string, array<string, mixed>>
     */
    private function locationPayload(Collection $locations): array
    {
        return $locations->mapWithKeys(function (Location $location): array {
            $lat = $location->lat;
            $lng = $location->lng;

            return [$location->mapKey() => [
                'id' => $location->mapKey(),
                'name' => $location->name,
                'subtitle' => (string) $location->subtitle,
                'badge' => (string) ($location->badge ?: 'Lokasi'),
                'status' => (string) $location->status,
                'color' => $location->color,
                'lat' => $lat,
                'lng' => $lng,
                'zoom' => $location->zoom,
                'address' => (string) $location->address,
                'photo' => $location->image?->url() ?? self::PLACEHOLDER_IMAGE,
                'caption' => (string) $location->caption,
                'navUrl' => "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}",
                'osmEmbedUrl' => 'https://www.openstreetmap.org/export/embed.html?bbox='.($lng - 0.005).','.($lat - 0.005).','.($lng + 0.005).','.($lat + 0.005)."&layer=mapnik&marker={$lat},{$lng}",
            ]];
        })->all();
    }

    /**
     * @param  Collection<int, GalleryItem>  $gallery
     * @return list<array<string, string>>
     */
    private function galleryPayload(Collection $gallery): array
    {
        return $gallery->map(fn (GalleryItem $item): array => [
            'id' => 'gal-'.$item->id,
            'category' => (string) $item->category?->slug,
            'badge' => (string) ($item->badge ?: $item->category?->name),
            'meta' => collect([$item->taken_on?->translatedFormat('F Y'), $item->place])->filter()->implode(' • '),
            'title' => $item->title,
            'desc' => (string) $item->description,
            'img' => $item->image?->url() ?? self::PLACEHOLDER_IMAGE,
        ])->values()->all();
    }

    /**
     * @param  Collection<int, Achievement>  $achievements
     * @param  Collection<int|string, int>  $galleryIndex
     * @return list<array<string, mixed>>
     */
    private function achievementPayload(Collection $achievements, Collection $galleryIndex): array
    {
        return $achievements->map(fn (Achievement $achievement): array => [
            'key' => 'ach-'.$achievement->id,
            'featured' => $achievement->is_featured,
            'cat' => $achievement->categories->pluck('slug')->all(),
            'medal' => $achievement->medal,
            'level' => $achievement->level,
            'when' => $achievement->achieved_on->format('Y-m-d'),
            'badge' => $achievement->badge,
            'icon' => $achievement->icon,
            // Abu gelap terlalu berat sebagai warna ikon medali perak di atas label putih.
            'toneClass' => $achievement->tone === 'slate' ? 'text-slate-500' : Palette::text($achievement->tone),
            'title' => $achievement->title,
            'org' => (string) $achievement->organizer,
            'desc' => (string) $achievement->description,
            'tags' => $achievement->tags ?? [],
            'no' => (string) $achievement->reference_no,
            'photo' => $galleryIndex->get($achievement->gallery_item_id),
            'img' => $achievement->image?->url() ?? $achievement->galleryItem?->image?->url(),
            'loc' => $achievement->location?->mapKey(),
            'kw' => (string) $achievement->keywords,
        ])->values()->all();
    }

    /**
     * @param  Collection<int, Achievement>  $achievements
     * @return array<string, array<string, mixed>>
     */
    private function certificatePayload(Collection $achievements): array
    {
        return $achievements->mapWithKeys(fn (Achievement $achievement): array => ['ach-'.$achievement->id => [
            'title' => $achievement->cert_title ?: $achievement->title,
            'org' => (string) ($achievement->cert_organizer ?: $achievement->organizer),
            'number' => (string) ($achievement->cert_number ?: $achievement->reference_no),
            'date' => $achievement->achieved_on->translatedFormat('j F Y'),
            'predikat' => $achievement->cert_predicate ?: $achievement->badge,
            'desc' => (string) ($achievement->cert_description ?: $achievement->description),
            'tags' => $achievement->cert_tags ?: ($achievement->tags ?? []),
        ]])->all();
    }
}
