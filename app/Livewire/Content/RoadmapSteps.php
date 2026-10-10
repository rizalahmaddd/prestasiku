<?php

namespace App\Livewire\Content;

use App\Models\Achievement;
use App\Models\Category;
use App\Models\RoadmapStep;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app', ['heading' => 'Rekam Jejak'])]
#[Title('Rekam Jejak')]
class RoadmapSteps extends ContentCrud
{
    public string $period = '';

    public string $title = '';

    public string $description = '';

    public string $tag = '';

    public string $state = 'normal';

    public string $jump_target = '';

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;
        $attributes = collect($validated)->map(fn ($value) => $value === '' ? null : $value)->all();

        if ($isEditing) {
            RoadmapStep::findOrFail($this->editingId)->update($attributes);
        } else {
            RoadmapStep::create($attributes);
        }

        $this->closeModal();
        $this->notify($isEditing ? __('Tahap diperbarui.') : __('Tahap ditambahkan.'));
    }

    /**
     * Tujuan klik tahap di halaman publik: saringan prestasi per kategori, medali, atau tingkat.
     *
     * @return array<string, array<string, string>>
     */
    public function jumpTargets(): array
    {
        return [
            'Kategori prestasi' => Category::query()->ofType(Category::TYPE_ACHIEVEMENT)->ordered()->pluck('name', 'slug')->all(),
            'Medali' => ['emas' => 'Juara 1 & Emas', 'perak' => 'Medali Perak'],
            'Tingkat' => ['internasional' => Achievement::LEVELS['internasional'], 'nasional' => Achievement::LEVELS['nasional']],
        ];
    }

    public function render()
    {
        $steps = RoadmapStep::query()
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$this->search}%")
                ->orWhere('period', 'like', "%{$this->search}%")))
            ->ordered()
            ->paginate($this->perPage);

        return view('livewire.content.roadmap-steps', [
            'steps' => $steps,
            'jumpTargets' => $this->jumpTargets(),
        ]);
    }

    protected function modelClass(): string
    {
        return RoadmapStep::class;
    }

    protected function resetForm(): void
    {
        $this->reset(['period', 'title', 'description', 'tag', 'jump_target']);
        $this->state = 'normal';
    }

    protected function fillForm($record): void
    {
        $this->period = $record->period;
        $this->title = $record->title;
        $this->description = (string) $record->description;
        $this->tag = (string) $record->tag;
        $this->state = $record->state;
        $this->jump_target = (string) $record->jump_target;
    }

    protected function rules(): array
    {
        return [
            'period' => ['required', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:500'],
            'tag' => ['nullable', 'string', 'max:80'],
            'state' => ['required', Rule::in(array_keys(RoadmapStep::STATES))],
            'jump_target' => ['nullable', Rule::in(collect($this->jumpTargets())->flatMap(fn (array $group) => array_keys($group))->all())],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'period' => 'periode',
            'title' => 'judul tahap',
            'description' => 'cerita singkat',
            'tag' => 'label',
            'state' => 'tampilan',
            'jump_target' => 'tujuan klik',
        ];
    }
}
