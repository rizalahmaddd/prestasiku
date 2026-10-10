<?php

namespace App\Livewire\Content;

use App\Models\Interest;
use App\Support\Site\Palette;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app', ['heading' => 'Bidang Minat'])]
#[Title('Bidang Minat')]
class Interests extends ContentCrud
{
    public string $title = '';

    public string $description = '';

    public string $icon = 'star';

    public string $tone = 'royal';

    public string $tags = '';

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;
        $attributes = [...$validated, 'tags' => $this->tagsFromText($this->tags)];

        if ($isEditing) {
            Interest::findOrFail($this->editingId)->update($attributes);
        } else {
            Interest::create($attributes);
        }

        $this->closeModal();
        $this->notify($isEditing ? __('Bidang minat diperbarui.') : __('Bidang minat ditambahkan.'));
    }

    public function render()
    {
        $interests = Interest::query()
            ->when($this->search, fn ($query) => $query->where('title', 'like', "%{$this->search}%"))
            ->ordered()
            ->paginate($this->perPage);

        return view('livewire.content.interests', ['interests' => $interests]);
    }

    protected function modelClass(): string
    {
        return Interest::class;
    }

    protected function resetForm(): void
    {
        $this->reset(['title', 'description', 'tags']);
        $this->icon = 'star';
        $this->tone = 'royal';
    }

    protected function fillForm($record): void
    {
        $this->title = $record->title;
        $this->description = (string) $record->description;
        $this->icon = $record->icon;
        $this->tone = $record->tone;
        $this->tags = $this->tagsToText($record->tags);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:600'],
            'icon' => ['required', Rule::in(array_keys(Palette::GENERAL_ICONS))],
            'tone' => ['required', Rule::in(array_keys(Palette::TONES))],
            'tags' => ['nullable', 'string', 'max:300'],
        ];
    }

    protected function validationAttributes(): array
    {
        return ['title' => 'judul', 'description' => 'penjelasan', 'icon' => 'ikon', 'tone' => 'warna', 'tags' => 'label'];
    }
}
