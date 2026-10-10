<?php

namespace App\Livewire\Content;

use App\Models\Education;
use App\Models\Location;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app', ['heading' => 'Pendidikan'])]
#[Title('Pendidikan')]
class Educations extends ContentCrud
{
    public string $name = '';

    public string $program = '';

    public string $period_label = '';

    public string $tone = 'done';

    public string $city = '';

    public string $description = '';

    public string $footnote = '';

    public string $location_id = '';

    public bool $is_published = true;

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;
        $attributes = collect($validated)->map(fn ($value) => $value === '' ? null : $value)->all();

        if ($isEditing) {
            Education::findOrFail($this->editingId)->update($attributes);
        } else {
            Education::create($attributes);
        }

        $this->closeModal();
        $this->notify($isEditing ? __('Riwayat pendidikan diperbarui.') : __('Riwayat pendidikan ditambahkan.'));
    }

    public function render()
    {
        $educations = Education::query()
            ->with('location')
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('program', 'like', "%{$this->search}%")))
            ->ordered()
            ->paginate($this->perPage);

        return view('livewire.content.educations', [
            'educations' => $educations,
            'locations' => Location::query()->ordered()->get(['id', 'name']),
        ]);
    }

    protected function modelClass(): string
    {
        return Education::class;
    }

    protected function resetForm(): void
    {
        $this->reset(['name', 'program', 'period_label', 'city', 'description', 'footnote', 'location_id']);
        $this->tone = 'done';
        $this->is_published = true;
    }

    protected function fillForm($record): void
    {
        $this->name = $record->name;
        $this->program = (string) $record->program;
        $this->period_label = $record->period_label;
        $this->tone = $record->tone;
        $this->city = (string) $record->city;
        $this->description = (string) $record->description;
        $this->footnote = (string) $record->footnote;
        $this->location_id = (string) $record->location_id;
        $this->is_published = $record->is_published;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'program' => ['nullable', 'string', 'max:191'],
            'period_label' => ['required', 'string', 'max:80'],
            'tone' => ['required', Rule::in(array_keys(Education::TONES))],
            'city' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'footnote' => ['nullable', 'string', 'max:191'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'is_published' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama sekolah',
            'program' => 'program',
            'period_label' => 'periode',
            'tone' => 'status',
            'city' => 'kota',
            'description' => 'cerita singkat',
            'footnote' => 'catatan',
            'location_id' => 'lokasi di peta',
        ];
    }
}
