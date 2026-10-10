<?php

use App\Livewire\Content\Achievements;
use App\Livewire\Content\Categories;
use App\Livewire\Content\GalleryItems;
use App\Livewire\Content\Locations;
use App\Livewire\Content\MediaLibrary;
use App\Livewire\Content\PageContent;
use App\Livewire\Content\Stats;
use App\Models\Achievement;
use App\Models\Category;
use App\Models\GalleryItem;
use App\Models\Location;
use App\Models\Media;
use App\Models\Stat;
use App\Support\Site\SiteContent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('guests cannot open the content pages', function (string $route) {
    $this->get(route($route))->assertRedirect('/login');
})->with(['content.page', 'content.achievements', 'content.media']);

test('every content page renders for an admin', function (string $route) {
    actingAsAdmin();

    $this->get(route($route))->assertOk();
})->with([
    'content.page', 'content.achievements', 'content.gallery', 'content.categories', 'content.education',
    'content.locations', 'content.roadmap', 'content.interests', 'content.stats', 'content.testimonials', 'content.media',
]);

test('read-only roles can look but not change content', function () {
    actingAsRole('staff');
    $achievement = Achievement::factory()->create();

    $this->get(route('content.achievements'))->assertOk();

    Livewire::test(Achievements::class)->call('openCreateModal')->assertForbidden();
    Livewire::test(Achievements::class)->call('moveDown', $achievement->id)->assertForbidden();
    Livewire::test(PageContent::class)->set('values.name', 'Diubah')->call('save')->assertForbidden();
    Livewire::test(MediaLibrary::class)->call('confirmDelete', 1)->assertForbidden();

    expect(SiteContent::get('name'))->toBe('Nama Siswa');
});

test('admin creates an achievement with categories and it appears on the website', function () {
    actingAsAdmin();
    $category = Category::factory()->create();

    Livewire::test(Achievements::class)
        ->call('openCreateModal')
        ->set('title', 'Olimpiade Sains Kota')
        ->set('badge', 'Juara 1')
        ->set('achieved_on', '2026-03-10')
        ->set('tags', 'Tingkat Kota,  Sains , ')
        ->set('category_ids', [(string) $category->id])
        ->call('save')
        ->assertHasNoErrors();

    $achievement = Achievement::where('title', 'Olimpiade Sains Kota')->firstOrFail();

    expect($achievement->tags)->toBe(['Tingkat Kota', 'Sains'])
        ->and($achievement->categories->pluck('id')->all())->toBe([$category->id])
        ->and($achievement->sort_order)->toBe(1);

    $this->get(route('home'))->assertSee('Olimpiade Sains Kota');
});

test('achievement validation messages are in Indonesian', function () {
    actingAsAdmin();

    Livewire::test(Achievements::class)
        ->call('openCreateModal')
        ->call('save')
        ->assertHasErrors(['title', 'badge', 'achieved_on'])
        ->assertSee('Nama lomba wajib diisi.');
});

test('only one achievement can be the featured card', function () {
    actingAsAdmin();
    $old = Achievement::factory()->create(['is_featured' => true]);
    $new = Achievement::factory()->create();

    Livewire::test(Achievements::class)
        ->call('openEditModal', $new->id)
        ->set('is_featured', true)
        ->call('save')
        ->assertHasNoErrors();

    expect($new->fresh()->is_featured)->toBeTrue()
        ->and($old->fresh()->is_featured)->toBeFalse();
});

test('rows are reordered with the up and down buttons', function () {
    actingAsAdmin();
    [$first, $second, $third] = Achievement::factory()->count(3)->create()->all();

    Livewire::test(Achievements::class)->call('moveDown', $first->id)->call('moveUp', $third->id);

    expect(Achievement::query()->ordered()->pluck('id')->all())->toBe([$second->id, $third->id, $first->id]);
});

test('category order is kept separately per type', function () {
    actingAsAdmin();
    $achievementCategory = Category::factory()->create();
    $galleryA = Category::factory()->gallery()->create();
    $galleryB = Category::factory()->gallery()->create();

    Livewire::test(Categories::class, ['type' => Category::TYPE_GALLERY])->call('moveUp', $galleryB->id);

    expect(Category::query()->ofType(Category::TYPE_GALLERY)->ordered()->pluck('id')->all())->toBe([$galleryB->id, $galleryA->id])
        ->and($achievementCategory->fresh()->sort_order)->toBe(1);
});

test('a category gets a unique slug and cannot be deleted while in use', function () {
    actingAsAdmin();
    Category::factory()->create(['name' => 'Sains', 'slug' => 'sains']);

    Livewire::test(Categories::class)
        ->call('openCreateModal')
        ->set('name', 'Sains')
        ->call('save')
        ->assertHasNoErrors();

    $category = Category::where('slug', 'sains-2')->firstOrFail();
    Achievement::factory()->create()->categories()->attach($category);

    Livewire::test(Categories::class)->call('confirmDelete', $category->id)->call('delete');

    expect($category->fresh())->not->toBeNull();
});

test('an image uploaded from a form lands in the media library and is attached', function () {
    Storage::fake('public');
    actingAsAdmin();

    $component = Livewire::test(GalleryItems::class)
        ->call('openCreateModal')
        ->set('mediaUploads.image_id', UploadedFile::fake()->image('Podium Lomba.jpg', 800, 600));

    $media = Media::firstOrFail();
    Storage::disk('public')->assertExists($media->path);

    $component->assertSet('image_id', $media->id)
        ->set('title', 'Podium Lomba')
        ->call('save')
        ->assertHasNoErrors();

    expect(GalleryItem::firstOrFail()->image_id)->toBe($media->id)
        ->and($media->name)->toBe('Podium Lomba')
        ->and($media->usageCount())->toBe(1);
});

test('oversized photos are shrunk on upload', function () {
    Storage::fake('public');
    actingAsAdmin();

    Livewire::test(MediaLibrary::class)->set('uploads', [UploadedFile::fake()->image('kamera.jpg', 4000, 3000)]);

    $media = Media::firstOrFail();

    expect($media->width)->toBe(2000)->and($media->height)->toBe(1500);
})->skip(fn () => ! function_exists('imagecreatetruecolor'), 'GD tidak tersedia.');

test('uploads that are not images are rejected', function () {
    Storage::fake('public');
    actingAsAdmin();

    Livewire::test(GalleryItems::class)
        ->call('openCreateModal')
        ->set('mediaUploads.image_id', UploadedFile::fake()->create('skrip.svg', 10, 'image/svg+xml'))
        ->assertHasErrors('mediaUploads.image_id');

    expect(Media::count())->toBe(0);
});

test('the media picker only writes to fields the form declares', function () {
    actingAsAdmin();
    $media = Media::factory()->create();

    Livewire::test(GalleryItems::class)
        ->call('chooseMedia', 'image_id', $media->id)
        ->assertSet('image_id', $media->id)
        ->call('chooseMedia', 'editingId', $media->id)
        ->assertNotFound();
});

test('an image that is still in use cannot be deleted from the library', function () {
    Storage::fake('public');
    actingAsAdmin();
    $used = Media::factory()->create();
    $unused = Media::factory()->create();
    GalleryItem::factory()->create(['image_id' => $used->id]);
    SiteContent::putMany(['portrait_id' => (string) $used->id]);

    expect($used->usageCount())->toBe(2);

    Livewire::test(MediaLibrary::class)->call('confirmDelete', $used->id)->call('delete');
    Livewire::test(MediaLibrary::class)->call('confirmDelete', $unused->id)->call('delete');

    expect($used->fresh())->not->toBeNull()->and($unused->fresh())->toBeNull();
});

test('a location needs a point on the map', function () {
    actingAsAdmin();

    Livewire::test(Locations::class)
        ->call('openCreateModal')
        ->set('name', 'Gedung Lomba')
        ->call('save')
        ->assertHasErrors(['lat', 'lng'])
        ->assertSee('Tentukan titik lokasi di peta dulu.')
        ->set('lat', -6.175392)
        ->set('lng', 106.827153)
        ->set('zoom', 16)
        ->call('save')
        ->assertHasNoErrors();

    $location = Location::firstOrFail();

    expect($location->lat)->toBe(-6.175392)->and($location->zoom)->toBe(16);
});

test('stats are added to the group that is open', function () {
    actingAsAdmin();

    Livewire::test(Stats::class, ['group' => Stat::GROUP_ROADMAP])
        ->call('openCreateModal')
        ->set('value', '9')
        ->set('suffix', '+')
        ->set('label', 'Tahun belajar')
        ->call('save')
        ->assertHasNoErrors();

    expect(Stat::firstOrFail()->group)->toBe(Stat::GROUP_ROADMAP);
});

test('page content is saved per tab and shown on the website', function () {
    actingAsAdmin();

    Livewire::test(PageContent::class)
        ->set('values.name', 'Alya Putri')
        ->set('values.grade', 'Kelas VIII')
        ->call('save')
        ->assertHasNoErrors();

    Livewire::test(PageContent::class, ['tab' => 'kontak'])
        ->set('values.email', 'bukan-email')
        ->call('save')
        ->assertHasErrors('values.email')
        ->set('values.email', 'wali@example.test')
        ->set('values.whatsapp', '0812 3456 7890')
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteContent::get('name'))->toBe('Alya Putri')
        ->and(SiteContent::whatsappNumber())->toBe('6281234567890');

    $this->get(route('home'))->assertSee('Alya Putri')->assertSee('Kelas VIII')->assertSee('wali@example.test');
});

test('the resume pdf can be uploaded and removed', function () {
    Storage::fake('public');
    actingAsAdmin();

    Livewire::test(PageContent::class, ['tab' => 'kontak'])
        ->set('resume', UploadedFile::fake()->create('cv.docx', 20, 'application/msword'))
        ->call('save')
        ->assertHasErrors('resume')
        ->set('resume', UploadedFile::fake()->create('cv.pdf', 20, 'application/pdf'))
        ->call('save')
        ->assertHasNoErrors();

    $path = SiteContent::resumePath();
    Storage::disk('public')->assertExists($path);

    Livewire::test(PageContent::class, ['tab' => 'kontak'])->call('removeResume');

    Storage::disk('public')->assertMissing($path);
    expect(SiteContent::resumePath())->toBeNull();
});
