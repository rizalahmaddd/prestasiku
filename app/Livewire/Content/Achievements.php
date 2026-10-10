<?php

namespace App\Livewire\Content;

use App\Models\Achievement;
use App\Models\Category;
use App\Models\GalleryItem;
use App\Models\Location;
use App\Support\Site\Palette;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app', ['heading' => 'Prestasi'])]
#[Title('Prestasi')]
class Achievements extends ContentCrud
{
    public string $title = '';

    public string $organizer = '';

    public string $description = '';

    public string $badge = '';

    public string $icon = 'medal';

    public string $tone = 'amber';

    public string $medal = '';

    public string $level = 'nasional';

    public string $achieved_on = '';

    public string $tags = '';

    public string $reference_no = '';

    public string $keywords = '';

    public bool $is_featured = false;

    public bool $is_published = true;

    public ?int $image_id = null;

    public string $gallery_item_id = '';

    public string $location_id = '';

    /** @var list<string> */
    public array $category_ids = [];

    public string $cert_title = '';

    public string $cert_organizer = '';

    public string $cert_number = '';

    public string $cert_predicate = '';

    public string $cert_description = '';

    public string $cert_tags = '';

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;

        $attributes = [
            ...collect($validated)->except(['category_ids', 'tags', 'cert_tags'])->map(fn ($value) => $value === '' ? null : $value)->all(),
            'tags' => $this->tagsFromText($this->tags),
            'cert_tags' => $this->tagsFromText($this->cert_tags),
            'image_id' => $this->image_id,
        ];

        $achievement = $isEditing ? Achievement::findOrFail($this->editingId) : new Achievement;
        $achievement->fill($attributes)->save();
        $achievement->categories()->sync($this->category_ids);

        // Hanya satu prestasi yang tampil sebagai kartu besar di halaman depan.
        if ($achievement->is_featured) {
            Achievement::query()->where('is_featured', true)->whereKeyNot($achievement->id)->get()
                ->each(fn (Achievement $other) => $other->update(['is_featured' => false]));
        }

        $this->closeModal();
        $this->notify($isEditing ? __('Prestasi diperbarui.') : __('Prestasi ditambahkan.'));
    }

    public function render()
    {
        $achievements = Achievement::query()
            ->with(['image', 'galleryItem.image', 'categories'])
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$this->search}%")
                ->orWhere('organizer', 'like', "%{$this->search}%")
                ->orWhere('badge', 'like', "%{$this->search}%")))
            ->ordered()
            ->paginate($this->perPage);

        return view('livewire.content.achievements', [
            'achievements' => $achievements,
            'categories' => Category::query()->ofType(Category::TYPE_ACHIEVEMENT)->ordered()->get(),
            'galleryItems' => GalleryItem::query()->ordered()->get(['id', 'title']),
            'locations' => Location::query()->ordered()->get(['id', 'name']),
        ]);
    }

    protected function modelClass(): string
    {
        return Achievement::class;
    }

    protected function mediaFields(): array
    {
        return ['image_id'];
    }

    protected function resetForm(): void
    {
        $this->reset([
            'title', 'organizer', 'description', 'badge', 'medal', 'achieved_on', 'tags', 'reference_no',
            'keywords', 'is_featured', 'image_id', 'gallery_item_id', 'location_id', 'category_ids',
            'cert_title', 'cert_organizer', 'cert_number', 'cert_predicate', 'cert_description', 'cert_tags',
        ]);
        $this->icon = 'medal';
        $this->tone = 'amber';
        $this->level = 'nasional';
        $this->is_published = true;
        $this->resetMediaPicker();
    }

    protected function fillForm($record): void
    {
        $this->title = $record->title;
        $this->organizer = (string) $record->organizer;
        $this->description = (string) $record->description;
        $this->badge = $record->badge;
        $this->icon = $record->icon;
        $this->tone = $record->tone;
        $this->medal = (string) $record->medal;
        $this->level = $record->level;
        $this->achieved_on = $record->achieved_on->format('Y-m-d');
        $this->tags = $this->tagsToText($record->tags);
        $this->reference_no = (string) $record->reference_no;
        $this->keywords = (string) $record->keywords;
        $this->is_featured = $record->is_featured;
        $this->is_published = $record->is_published;
        $this->image_id = $record->image_id;
        $this->gallery_item_id = (string) $record->gallery_item_id;
        $this->location_id = (string) $record->location_id;
        $this->category_ids = $record->categories()->pluck('categories.id')->map(fn ($id) => (string) $id)->all();
        $this->cert_title = (string) $record->cert_title;
        $this->cert_organizer = (string) $record->cert_organizer;
        $this->cert_number = (string) $record->cert_number;
        $this->cert_predicate = (string) $record->cert_predicate;
        $this->cert_description = (string) $record->cert_description;
        $this->cert_tags = $this->tagsToText($record->cert_tags);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:191'],
            'organizer' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:1000'],
            'badge' => ['required', 'string', 'max:80'],
            'icon' => ['required', Rule::in(array_keys(Palette::ACHIEVEMENT_ICONS))],
            'tone' => ['required', Rule::in(array_keys(Palette::TONES))],
            'medal' => ['nullable', Rule::in(array_keys(Achievement::MEDALS))],
            'level' => ['required', Rule::in(array_keys(Achievement::LEVELS))],
            'achieved_on' => ['required', 'date'],
            'tags' => ['nullable', 'string', 'max:300'],
            'reference_no' => ['nullable', 'string', 'max:80'],
            'keywords' => ['nullable', 'string', 'max:191'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'gallery_item_id' => ['nullable', Rule::exists('gallery_items', 'id')],
            'location_id' => ['nullable', Rule::exists('locations', 'id')],
            'category_ids' => ['array'],
            'category_ids.*' => [Rule::exists('categories', 'id')->where('type', Category::TYPE_ACHIEVEMENT)],
            'cert_title' => ['nullable', 'string', 'max:191'],
            'cert_organizer' => ['nullable', 'string', 'max:191'],
            'cert_number' => ['nullable', 'string', 'max:120'],
            'cert_predicate' => ['nullable', 'string', 'max:191'],
            'cert_description' => ['nullable', 'string', 'max:1000'],
            'cert_tags' => ['nullable', 'string', 'max:300'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'title' => 'nama lomba',
            'organizer' => 'penyelenggara',
            'description' => 'cerita singkat',
            'badge' => 'hasil',
            'icon' => 'ikon',
            'tone' => 'warna ikon',
            'medal' => 'medali',
            'level' => 'tingkat',
            'achieved_on' => 'tanggal',
            'tags' => 'label',
            'reference_no' => 'nomor singkat',
            'keywords' => 'kata kunci',
            'gallery_item_id' => 'foto galeri',
            'location_id' => 'lokasi',
            'category_ids' => 'kategori',
            'cert_title' => 'judul piagam',
            'cert_organizer' => 'penerbit piagam',
            'cert_number' => 'nomor piagam',
            'cert_predicate' => 'predikat',
            'cert_description' => 'keterangan piagam',
            'cert_tags' => 'label piagam',
        ];
    }
}
