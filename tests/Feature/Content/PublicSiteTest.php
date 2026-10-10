<?php

use App\Models\Achievement;
use App\Models\Category;
use App\Models\Education;
use App\Models\GalleryItem;
use App\Models\Location;
use App\Models\Media;
use App\Support\Site\SiteContent;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Support\Facades\Storage;

test('the public page renders with no content at all', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Nama Siswa')
        ->assertDontSee('id="school-map"', false)
        ->assertDontSee('Resume PDF');
});

test('the public page shows managed content and hides unpublished items', function () {
    SiteContent::putMany(['name' => 'Alya Putri', 'hero_intro' => 'Portofolio **Alya** yang <b>aman</b>.']);
    $category = Category::factory()->create(['name' => 'Sains', 'slug' => 'sains']);
    Achievement::factory()->create(['title' => 'Olimpiade Kimia'])->categories()->attach($category);
    Achievement::factory()->hidden()->create(['title' => 'Lomba Rahasia']);
    GalleryItem::factory()->create(['title' => 'Foto Podium']);
    GalleryItem::factory()->create(['title' => 'Foto Draf', 'is_published' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Alya Putri')
        ->assertSee('<strong class="text-slate-900 font-semibold">Alya</strong>', false)
        ->assertSee('&lt;b&gt;aman&lt;/b&gt;', false)
        ->assertSee('Olimpiade Kimia')
        ->assertDontSee('Lomba Rahasia')
        ->assertSee('Foto Podium')
        ->assertDontSee('Foto Draf')
        ->assertSee("filterCategory('sains', this)", false);
});

test('the map opens on the first education location and lists unlinked locations as venues', function () {
    $school = Location::factory()->create(['name' => 'SMP Contoh']);
    Location::factory()->create(['name' => 'Gedung Lomba Nasional', 'short_name' => 'Gedung Lomba']);
    Education::factory()->create(['location_id' => $school->id]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('id="school-map"', false)
        ->assertSee('<span id="map-floating-label">SMP Contoh</span>', false)
        ->assertSee('Gedung Lomba');
});

test('whatsapp numbers are normalised for the chat link', function () {
    SiteContent::putMany(['whatsapp' => '0812-3456-7890', 'whatsapp_message' => 'Halo wali']);

    expect(SiteContent::whatsappUrl())->toBe('https://wa.me/6281234567890?text=Halo%20wali')
        ->and(SiteContent::whatsappLabel())->toBe('+62 812-3456-7890');

    $this->get(route('home'))->assertSee('https://wa.me/6281234567890?text=Halo%20wali', false);
});

test('the resume download only exists once a file is uploaded', function () {
    Storage::fake('public');

    $this->get(route('resume'))->assertNotFound();

    Storage::disk('public')->put('resume/resume-test.pdf', '%PDF-1.4 demo');
    SiteContent::putMany([SiteContent::RESUME_KEY => 'resume/resume-test.pdf', 'name' => 'Alya Putri']);

    $this->get(route('home'))->assertSee('Resume PDF');
    $this->get(route('resume'))->assertOk()->assertHeader('content-disposition', 'inline; filename="Resume Alya Putri.pdf"');
});

test('the demo seeder reproduces the original static page with its photos', function () {
    Storage::fake('public');

    $this->seed(DemoContentSeeder::class);

    expect(Achievement::count())->toBe(9)
        ->and(GalleryItem::count())->toBe(9)
        ->and(Location::count())->toBe(5)
        ->and(Media::count())->toBe(15)
        ->and(Achievement::where('is_featured', true)->count())->toBe(1);

    Media::all()->each(fn (Media $media) => Storage::disk('public')->assertExists($media->path));
    Storage::disk('public')->assertExists(SiteContent::resumePath());

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Kenzo Al-Fatih')
        ->assertSee('International Youth STEM Robotics Cup')
        ->assertSee('Tokyo Tech')
        ->assertSee('Drs. Rahmadi, M.Pd.');

    // Menjalankan ulang tidak menggandakan konten.
    $this->seed(DemoContentSeeder::class);
    expect(Achievement::count())->toBe(9);
});
