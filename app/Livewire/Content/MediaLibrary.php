<?php

namespace App\Livewire\Content;

use App\Models\Media;
use App\Support\Site\MediaStore;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Semua gambar yang pernah diunggah. Gambar juga bisa diunggah langsung dari formulir konten;
 * halaman ini untuk mengunggah banyak sekaligus dan membersihkan yang tidak terpakai.
 */
#[Layout('layouts.app', ['heading' => 'Pustaka Media'])]
#[Title('Pustaka Media')]
class MediaLibrary extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    /** @var array<int, mixed> */
    public array $uploads = [];

    public ?int $confirmingDeleteId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUploads(): void
    {
        $this->authorizeManage();

        $this->validate(
            ['uploads' => ['array', 'max:20'], 'uploads.*' => MediaStore::RULES],
            ['uploads.max' => 'Unggah paling banyak 20 gambar sekaligus.'],
            ['uploads.*' => 'gambar'],
        );

        foreach ($this->uploads as $upload) {
            MediaStore::fromUpload($upload);
        }

        $count = count($this->uploads);
        $this->uploads = [];
        $this->resetPage();
        $this->dispatch('notify', message: __(':count gambar ditambahkan ke pustaka.', ['count' => $count]), type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->authorizeManage();

        $this->confirmingDeleteId = $id;
        $this->dispatch('open-modal', 'confirm-delete');
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->dispatch('close-modal', 'confirm-delete');
    }

    public function delete(): void
    {
        $this->authorizeManage();

        $media = Media::query()->find($this->confirmingDeleteId);
        $this->confirmingDeleteId = null;
        $this->dispatch('close-modal', 'confirm-delete');

        if ($media === null) {
            return;
        }

        if (($used = $media->usageCount()) > 0) {
            $this->dispatch('notify', message: __('Gambar ini masih dipakai di :count tempat. Ganti dulu gambarnya di sana.', ['count' => $used]), type: 'error');

            return;
        }

        $media->delete();
        $this->dispatch('notify', message: __('Gambar dihapus.'), type: 'success');
    }

    public function canManage(): bool
    {
        return auth()->user()->can('manage-content');
    }

    public function render()
    {
        $media = Media::query()
            ->when(trim($this->search) !== '', fn ($query) => $query->where('name', 'like', '%'.trim($this->search).'%'))
            ->latest('id')
            ->paginate(24);

        return view('livewire.content.media-library', ['media' => $media]);
    }

    private function authorizeManage(): void
    {
        abort_unless($this->canManage(), 403);
    }
}
