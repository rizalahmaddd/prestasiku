<?php

namespace App\Livewire\Content;

use App\Models\Category;
use App\Models\GalleryItem;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app', ['heading' => 'Galeri'])]
#[Title('Galeri')]
class GalleryItems extends ContentCrud
{
    public string $title = '';

    public string $description = '';

    public string $badge = '';

    public string $place = '';

    public string $taken_on = '';

    public string $category_id = '';

    public ?int $image_id = null;

    public bool $is_published = true;

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;
        $attributes = collect($validated)->map(fn ($value) => $value === '' ? null : $value)->all();

        if ($isEditing) {
            GalleryItem::findOrFail($this->editingId)->update($attributes);
        } else {
            GalleryItem::create($attributes);
        }

        $this->closeModal();
        $this->notify($isEditing ? __('Foto diperbarui.') : __('Foto ditambahkan ke galeri.'));
    }

    public function render()
    {
        $items = GalleryItem::query()
            ->with(['image', 'category'])
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$this->search}%")
                ->orWhere('place', 'like', "%{$this->search}%")
                ->orWhere('badge', 'like', "%{$this->search}%")))
            ->ordered()
            ->paginate($this->perPage);

        return view('livewire.content.gallery-items', [
            'items' => $items,
            'categories' => Category::query()->ofType(Category::TYPE_GALLERY)->ordered()->get(),
        ]);
    }

    protected function modelClass(): string
    {
        return GalleryItem::class;
    }

    protected function mediaFields(): array
    {
        return ['image_id'];
    }

    protected function resetForm(): void
    {
        $this->reset(['title', 'description', 'badge', 'place', 'taken_on', 'category_id', 'image_id']);
        $this->is_published = true;
        $this->resetMediaPicker();
    }

    protected function fillForm($record): void
    {
        $this->title = $record->title;
        $this->description = (string) $record->description;
        $this->badge = (string) $record->badge;
        $this->place = (string) $record->place;
        $this->taken_on = (string) $record->taken_on?->format('Y-m-d');
        $this->category_id = (string) $record->category_id;
        $this->image_id = $record->image_id;
        $this->is_published = $record->is_published;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:1000'],
            'badge' => ['nullable', 'string', 'max:80'],
            'place' => ['nullable', 'string', 'max:191'],
            'taken_on' => ['nullable', 'date'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('type', Category::TYPE_GALLERY)],
            'image_id' => ['required', Rule::exists('media', 'id')],
            'is_published' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'title' => 'judul foto',
            'description' => 'keterangan',
            'badge' => 'label foto',
            'place' => 'tempat',
            'taken_on' => 'tanggal',
            'category_id' => 'kategori',
            'image_id' => 'foto',
        ];
    }
}
