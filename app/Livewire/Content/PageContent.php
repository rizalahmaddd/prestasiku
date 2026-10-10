<?php

namespace App\Livewire\Content;

use App\Livewire\Concerns\WithMediaPicker;
use App\Models\Media;
use App\Support\Site\SiteContent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Teks dan gambar halaman publik yang bukan daftar. Formulirnya dibangun dari
 * SiteContent::TABS; tiap tab disimpan sendiri supaya kesalahan isian tidak tersembunyi di tab lain.
 */
#[Layout('layouts.app', ['heading' => 'Konten Halaman'])]
#[Title('Konten Halaman')]
class PageContent extends Component
{
    use WithMediaPicker;

    #[Url(as: 'tab', except: 'identitas')]
    public string $tab = 'identitas';

    /** @var array<string, mixed> */
    public array $values = [];

    public ?TemporaryUploadedFile $resume = null;

    public function mount(): void
    {
        $this->normalizeTab();

        foreach (SiteContent::fields() as $key => $field) {
            $value = SiteContent::get($key);
            $this->values[$key] = ($field['type'] ?? 'text') === 'image' ? ((int) $value ?: null) : $value;
        }
    }

    public function updatedTab(): void
    {
        $this->normalizeTab();
        $this->resetValidation();
        $this->resetMediaPicker();
    }

    public function save(): void
    {
        $this->authorizeManage();

        $fields = SiteContent::fields($this->tab);
        $rules = [];
        $attributes = [];

        foreach ($fields as $key => $field) {
            $rules["values.{$key}"] = $this->rulesFor($field);
            $attributes["values.{$key}"] = Str::lower($field['label']);
        }

        if ($this->tab === 'kontak') {
            $rules['resume'] = ['nullable', 'file', 'mimes:pdf', 'max:10240'];
            $attributes['resume'] = 'berkas resume';
        }

        $this->validate($rules, [
            'required' => ':Attribute wajib diisi.',
            'max.string' => ':Attribute maksimal :max karakter.',
            'max.file' => ':Attribute maksimal 10 MB.',
            'mimes' => ':Attribute harus berupa PDF.',
            'email' => 'Alamat email tidak valid.',
            'date' => ':Attribute bukan tanggal yang valid.',
            'before_or_equal' => ':Attribute tidak boleh di masa depan.',
            'regex' => ':Attribute hanya boleh berisi angka, spasi, tanda + dan -.',
        ], $attributes);

        SiteContent::putMany(collect($fields)->mapWithKeys(fn (array $field, string $key) => [
            $key => filled($this->values[$key] ?? null) ? (string) $this->values[$key] : null,
        ])->all());

        if ($this->resume) {
            $path = $this->resume->storeAs('resume', 'resume-'.Str::lower(Str::random(10)).'.pdf', Media::DISK);
            $this->deleteStoredResume();
            SiteContent::putMany([SiteContent::RESUME_KEY => $path]);
            $this->resume = null;
        }

        activity('content')->causedBy(auth()->user())->log('Konten halaman diubah: '.SiteContent::TABS[$this->tab]['label'].'.');

        $this->dispatch('notify', message: __('Perubahan tersimpan dan langsung tampil di website.'), type: 'success');
    }

    public function removeResume(): void
    {
        $this->authorizeManage();

        $this->deleteStoredResume();
        SiteContent::putMany([SiteContent::RESUME_KEY => null]);
        $this->resume = null;

        $this->dispatch('notify', message: __('Berkas resume dihapus.'), type: 'success');
    }

    public function canManage(): bool
    {
        return auth()->user()->can('manage-content');
    }

    public function render()
    {
        return view('livewire.content.page-content', [
            'tabs' => SiteContent::TABS,
            'sections' => SiteContent::TABS[$this->tab]['sections'],
            'resumePath' => SiteContent::resumePath(),
        ]);
    }

    protected function mediaFields(): array
    {
        return array_map(fn (string $key): string => "values.{$key}", SiteContent::imageKeys());
    }

    /**
     * @param  array<string, mixed>  $field
     * @return list<string>
     */
    private function rulesFor(array $field): array
    {
        $presence = ($field['required'] ?? false) ? 'required' : 'nullable';

        return match ($field['type'] ?? 'text') {
            'image' => [$presence, 'exists:media,id'],
            'date' => [$presence, 'date', 'before_or_equal:today'],
            'email' => [$presence, 'email', 'max:'.($field['max'] ?? 191)],
            'phone' => [$presence, 'string', 'regex:/^[0-9+\-\s]+$/', 'max:'.($field['max'] ?? 20)],
            default => [$presence, 'string', 'max:'.($field['max'] ?? 191)],
        };
    }

    private function deleteStoredResume(): void
    {
        if ($path = SiteContent::resumePath()) {
            Storage::disk(Media::DISK)->delete($path);
        }
    }

    private function normalizeTab(): void
    {
        if (! isset(SiteContent::TABS[$this->tab])) {
            $this->tab = 'identitas';
        }
    }

    private function authorizeManage(): void
    {
        abort_unless($this->canManage(), 403);
    }
}
