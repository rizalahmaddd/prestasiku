<?php

namespace App\Livewire\Content;

use App\Models\Category;
use App\Support\Site\Palette;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Layout('layouts.app', ['heading' => 'Kategori'])]
#[Title('Kategori')]
class Categories extends ContentCrud
{
    #[Url(as: 'jenis', except: Category::TYPE_ACHIEVEMENT)]
    public string $type = Category::TYPE_ACHIEVEMENT;

    public string $name = '';

    public string $icon = '';

    public function mount(): void
    {
        if (! isset(Category::TYPES[$this->type])) {
            $this->type = Category::TYPE_ACHIEVEMENT;
        }
    }

    public function updatedType(): void
    {
        if (! isset(Category::TYPES[$this->type])) {
            $this->type = Category::TYPE_ACHIEVEMENT;
        }

        $this->resetPage();
    }

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;

        if ($isEditing) {
            // Slug tidak ikut berubah supaya tautan dan saringan yang sudah ada tetap jalan.
            Category::findOrFail($this->editingId)->update(['name' => $validated['name'], 'icon' => $validated['icon'] ?: null]);
        } else {
            Category::create([
                'type' => $this->type,
                'slug' => $this->uniqueSlug($validated['name']),
                'name' => $validated['name'],
                'icon' => $validated['icon'] ?: null,
            ]);
        }

        $this->closeModal();
        $this->notify($isEditing ? __('Kategori diperbarui.') : __('Kategori ditambahkan.'));
    }

    public function render()
    {
        $categories = Category::query()
            ->ofType($this->type)
            ->withCount($this->type === Category::TYPE_GALLERY ? 'galleryItems as items_count' : 'achievements as items_count')
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->ordered()
            ->paginate($this->perPage);

        return view('livewire.content.categories', ['categories' => $categories]);
    }

    protected function modelClass(): string
    {
        return Category::class;
    }

    protected function resetForm(): void
    {
        $this->reset(['name', 'icon']);
    }

    protected function fillForm($record): void
    {
        $this->name = $record->name;
        $this->icon = (string) $record->icon;
    }

    protected function guardDelete($record): ?string
    {
        $used = $record->type === Category::TYPE_GALLERY ? $record->galleryItems()->count() : $record->achievements()->count();

        return $used > 0 ? __('Kategori ini masih dipakai :count data. Pindahkan dulu datanya ke kategori lain.', ['count' => $used]) : null;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'icon' => ['nullable', Rule::in(array_keys(Palette::GENERAL_ICONS))],
        ];
    }

    protected function validationAttributes(): array
    {
        return ['name' => 'nama kategori', 'icon' => 'ikon'];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $suffix = 2;

        while (Category::query()->ofType($this->type)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
