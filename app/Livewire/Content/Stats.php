<?php

namespace App\Livewire\Content;

use App\Models\Stat;
use App\Support\Site\Palette;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Layout('layouts.app', ['heading' => 'Angka Ringkasan'])]
#[Title('Angka Ringkasan')]
class Stats extends ContentCrud
{
    #[Url(as: 'kelompok', except: Stat::GROUP_SUMMARY)]
    public string $group = Stat::GROUP_SUMMARY;

    public string $value = '';

    public string $suffix = '';

    public string $label = '';

    public string $caption = '';

    public string $tone = 'slate';

    public function mount(): void
    {
        $this->normalizeGroup();
    }

    public function updatedGroup(): void
    {
        $this->normalizeGroup();
        $this->resetPage();
    }

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;
        $attributes = collect($validated)->map(fn ($value) => $value === '' ? null : $value)->all();

        if ($isEditing) {
            Stat::findOrFail($this->editingId)->update($attributes);
        } else {
            Stat::create([...$attributes, 'group' => $this->group]);
        }

        $this->closeModal();
        $this->notify($isEditing ? __('Angka diperbarui.') : __('Angka ditambahkan.'));
    }

    public function render()
    {
        $stats = Stat::query()
            ->inGroup($this->group)
            ->when($this->search, fn ($query) => $query->where('label', 'like', "%{$this->search}%"))
            ->ordered()
            ->paginate($this->perPage);

        return view('livewire.content.stats', ['stats' => $stats]);
    }

    protected function modelClass(): string
    {
        return Stat::class;
    }

    protected function resetForm(): void
    {
        $this->reset(['value', 'suffix', 'label', 'caption']);
        $this->tone = 'slate';
    }

    protected function fillForm($record): void
    {
        $this->value = (string) $record->value;
        $this->suffix = (string) $record->suffix;
        $this->label = $record->label;
        $this->caption = (string) $record->caption;
        $this->tone = $record->tone;
    }

    protected function rules(): array
    {
        return [
            'value' => ['required', 'integer', 'min:0', 'max:1000000'],
            'suffix' => ['nullable', 'string', 'max:10'],
            'label' => ['required', 'string', 'max:80'],
            'caption' => ['nullable', 'string', 'max:120'],
            'tone' => ['required', Rule::in(array_keys(Palette::TONES))],
        ];
    }

    protected function validationAttributes(): array
    {
        return ['value' => 'angka', 'suffix' => 'akhiran', 'label' => 'judul', 'caption' => 'keterangan', 'tone' => 'warna angka'];
    }

    private function normalizeGroup(): void
    {
        if (! isset(Stat::GROUPS[$this->group])) {
            $this->group = Stat::GROUP_SUMMARY;
        }
    }
}
