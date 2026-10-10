<?php

namespace App\Livewire\Content;

use App\Models\Testimonial;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app', ['heading' => 'Testimoni'])]
#[Title('Testimoni')]
class Testimonials extends ContentCrud
{
    public string $quote = '';

    public string $name = '';

    public string $role = '';

    public ?int $image_id = null;

    public bool $is_published = true;

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;
        $attributes = collect($validated)->map(fn ($value) => $value === '' ? null : $value)->all();

        if ($isEditing) {
            Testimonial::findOrFail($this->editingId)->update($attributes);
        } else {
            Testimonial::create($attributes);
        }

        $this->closeModal();
        $this->notify($isEditing ? __('Testimoni diperbarui.') : __('Testimoni ditambahkan.'));
    }

    public function render()
    {
        $testimonials = Testimonial::query()
            ->with('image')
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('quote', 'like', "%{$this->search}%")))
            ->ordered()
            ->paginate($this->perPage);

        return view('livewire.content.testimonials', ['testimonials' => $testimonials]);
    }

    protected function modelClass(): string
    {
        return Testimonial::class;
    }

    protected function mediaFields(): array
    {
        return ['image_id'];
    }

    protected function resetForm(): void
    {
        $this->reset(['quote', 'name', 'role', 'image_id']);
        $this->is_published = true;
        $this->resetMediaPicker();
    }

    protected function fillForm($record): void
    {
        $this->quote = $record->quote;
        $this->name = $record->name;
        $this->role = (string) $record->role;
        $this->image_id = $record->image_id;
        $this->is_published = $record->is_published;
    }

    protected function rules(): array
    {
        return [
            'quote' => ['required', 'string', 'max:600'],
            'name' => ['required', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:191'],
            'image_id' => ['nullable', 'exists:media,id'],
            'is_published' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return ['quote' => 'isi testimoni', 'name' => 'nama', 'role' => 'jabatan', 'image_id' => 'foto'];
    }
}
