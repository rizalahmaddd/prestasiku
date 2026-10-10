<?php

namespace App\Livewire\Concerns;

use App\Models\Media;
use App\Support\Site\MediaStore;
use Illuminate\Support\Collection;
use Livewire\WithFileUploads;

/**
 * Pemilih gambar untuk formulir konten: unggah langsung atau ambil dari Pustaka Media. Kolom
 * yang boleh diisi didaftarkan di mediaFields(); nilainya id media. Tampilannya ada di
 * livewire/content/partials/image-picker.blade.php.
 */
trait WithMediaPicker
{
    use WithFileUploads;

    /** @var array<string, mixed> */
    public array $mediaUploads = [];

    public ?string $mediaPickerField = null;

    public string $mediaPickerSearch = '';

    /**
     * Nama properti (boleh bertitik, mis. "values.portrait_id") yang menyimpan id media.
     *
     * @return list<string>
     */
    abstract protected function mediaFields(): array;

    public function updatedMediaUploads(mixed $value, string $slug): void
    {
        $field = $this->mediaFieldFromSlug($slug);
        $this->authorizeMediaPicker();

        $this->validate(
            ["mediaUploads.{$slug}" => ['required', ...MediaStore::RULES]],
            [],
            ["mediaUploads.{$slug}" => 'gambar'],
        );

        $media = MediaStore::fromUpload($this->mediaUploads[$slug]);

        data_set($this, $field, $media->id);
        unset($this->mediaUploads[$slug]);
        $this->mediaPickerField = null;
    }

    public function toggleMediaLibrary(string $field): void
    {
        $this->assertMediaField($field);

        $this->mediaPickerField = $this->mediaPickerField === $field ? null : $field;
        $this->mediaPickerSearch = '';
    }

    public function chooseMedia(string $field, int $mediaId): void
    {
        $this->assertMediaField($field);
        $this->authorizeMediaPicker();

        data_set($this, $field, Media::query()->findOrFail($mediaId)->id);
        $this->mediaPickerField = null;
        $this->resetValidation('mediaUploads.'.self::mediaSlug($field));
    }

    public function clearMedia(string $field): void
    {
        $this->assertMediaField($field);

        data_set($this, $field, null);
    }

    public function mediaFor(string $field): ?Media
    {
        $id = (int) data_get($this, $field);

        return $id > 0 ? Media::query()->find($id) : null;
    }

    /**
     * @return Collection<int, Media>
     */
    public function mediaLibrary(): Collection
    {
        return Media::query()
            ->when(trim($this->mediaPickerSearch) !== '', fn ($query) => $query->where('name', 'like', '%'.trim($this->mediaPickerSearch).'%'))
            ->latest('id')
            ->limit(24)
            ->get();
    }

    public static function mediaSlug(string $field): string
    {
        return str_replace('.', '__', $field);
    }

    protected function resetMediaPicker(): void
    {
        $this->mediaUploads = [];
        $this->mediaPickerField = null;
        $this->mediaPickerSearch = '';
    }

    protected function authorizeMediaPicker(): void
    {
        abort_unless(auth()->user()->can('manage-content'), 403);
    }

    private function mediaFieldFromSlug(string $slug): string
    {
        $field = str_replace('__', '.', $slug);
        $this->assertMediaField($field);

        return $field;
    }

    private function assertMediaField(string $field): void
    {
        abort_unless(in_array($field, $this->mediaFields(), true), 404);
    }
}
