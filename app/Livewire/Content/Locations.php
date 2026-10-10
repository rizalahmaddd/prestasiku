<?php

namespace App\Livewire\Content;

use App\Models\Location;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app', ['heading' => 'Lokasi'])]
#[Title('Lokasi')]
class Locations extends ContentCrud
{
    public string $name = '';

    public string $short_name = '';

    public string $subtitle = '';

    public string $badge = '';

    public string $status = '';

    public string $color = '#1d4ed8';

    /**
     * Tanpa tipe: pemilih peta mengirim angka, tapi kolom yang dikosongkan mengirim string kosong.
     *
     * @var float|string|null
     */
    public $lat = null;

    /** @var float|string|null */
    public $lng = null;

    public int $zoom = 15;

    public string $address = '';

    public string $caption = '';

    public ?int $image_id = null;

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;
        $attributes = collect($validated)->map(fn ($value) => $value === '' ? null : $value)->all();

        if ($isEditing) {
            Location::findOrFail($this->editingId)->update($attributes);
        } else {
            Location::create($attributes);
        }

        $this->closeModal();
        $this->notify($isEditing ? __('Lokasi diperbarui.') : __('Lokasi ditambahkan.'));
    }

    public function render()
    {
        $locations = Location::query()
            ->with('image')
            ->withCount(['educations', 'achievements'])
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('address', 'like', "%{$this->search}%")))
            ->ordered()
            ->paginate($this->perPage);

        return view('livewire.content.locations', ['locations' => $locations]);
    }

    protected function modelClass(): string
    {
        return Location::class;
    }

    protected function mediaFields(): array
    {
        return ['image_id'];
    }

    protected function resetForm(): void
    {
        $this->reset(['name', 'short_name', 'subtitle', 'badge', 'status', 'lat', 'lng', 'address', 'caption', 'image_id']);
        $this->color = '#1d4ed8';
        $this->zoom = 15;
        $this->resetMediaPicker();
    }

    protected function fillForm($record): void
    {
        $this->name = $record->name;
        $this->short_name = (string) $record->short_name;
        $this->subtitle = (string) $record->subtitle;
        $this->badge = (string) $record->badge;
        $this->status = (string) $record->status;
        $this->color = $record->color;
        $this->lat = $record->lat;
        $this->lng = $record->lng;
        $this->zoom = $record->zoom;
        $this->address = (string) $record->address;
        $this->caption = (string) $record->caption;
        $this->image_id = $record->image_id;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'short_name' => ['nullable', 'string', 'max:60'],
            'subtitle' => ['nullable', 'string', 'max:191'],
            'badge' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:80'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'zoom' => ['required', 'integer', 'between:3,18'],
            'address' => ['nullable', 'string', 'max:500'],
            'caption' => ['nullable', 'string', 'max:191'],
            'image_id' => ['nullable', 'exists:media,id'],
        ];
    }

    protected function messages(): array
    {
        return [
            ...parent::messages(),
            'lat.required' => 'Tentukan titik lokasi di peta dulu.',
            'lng.required' => 'Tentukan titik lokasi di peta dulu.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama tempat',
            'short_name' => 'nama pendek',
            'subtitle' => 'keterangan',
            'badge' => 'label',
            'status' => 'status',
            'color' => 'warna penanda',
            'lat' => 'garis lintang',
            'lng' => 'garis bujur',
            'zoom' => 'tingkat zoom',
            'address' => 'alamat',
            'caption' => 'keterangan foto',
            'image_id' => 'foto',
        ];
    }
}
