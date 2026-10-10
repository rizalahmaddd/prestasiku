<?php

namespace App\Livewire\Content;

use App\Models\ContactMessage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

/**
 * Pesan dari formulir kontak website. Tidak ada tambah manual: baris hanya datang dari
 * pengunjung; di sini pesan dibaca, dibalas, dan ditandai status tindak lanjutnya.
 */
#[Layout('layouts.app', ['heading' => 'Pesan Masuk'])]
#[Title('Pesan Masuk')]
class Messages extends ContentCrud
{
    #[Url(as: 'status', except: 'open')]
    public string $filter = 'open';

    public string $status = ContactMessage::STATUS_NEW;

    public string $note = '';

    public function mount(): void
    {
        $this->normalizeFilter();
    }

    public function updatedFilter(): void
    {
        $this->normalizeFilter();
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        abort(404);
    }

    public function save(): void
    {
        $this->authorizeManage();
        abort_unless($this->editingId !== null, 404);

        $validated = $this->validate();
        $message = ContactMessage::findOrFail($this->editingId);

        $message->update([
            'status' => $validated['status'],
            'note' => $validated['note'] ?: null,
            // Dicatat sekali saat pesan pertama kali ditangani, supaya terlihat siapa yang mengambilnya.
            'handled_by' => $validated['status'] === ContactMessage::STATUS_NEW ? null : ($message->handled_by ?? auth()->id()),
            'handled_at' => $validated['status'] === ContactMessage::STATUS_NEW ? null : ($message->handled_at ?? now()),
        ]);

        $this->closeModal();
        $this->notify(__('Tindak lanjut tersimpan.'));
    }

    public function markAs(int $id, string $status): void
    {
        $this->authorizeManage();
        abort_unless(isset(ContactMessage::STATUSES[$status]), 404);

        $message = ContactMessage::findOrFail($id);
        $message->update([
            'status' => $status,
            'handled_by' => $status === ContactMessage::STATUS_NEW ? null : ($message->handled_by ?? auth()->id()),
            'handled_at' => $status === ContactMessage::STATUS_NEW ? null : ($message->handled_at ?? now()),
        ]);

        $this->notify(__('Pesan ditandai :status.', ['status' => mb_strtolower(ContactMessage::STATUSES[$status]['label'])]));
    }

    /**
     * @return array<string, int>
     */
    public function counts(): array
    {
        $perStatus = ContactMessage::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'open' => (int) ($perStatus[ContactMessage::STATUS_NEW] ?? 0) + (int) ($perStatus[ContactMessage::STATUS_IN_PROGRESS] ?? 0),
            ContactMessage::STATUS_NEW => (int) ($perStatus[ContactMessage::STATUS_NEW] ?? 0),
            ContactMessage::STATUS_IN_PROGRESS => (int) ($perStatus[ContactMessage::STATUS_IN_PROGRESS] ?? 0),
            ContactMessage::STATUS_DONE => (int) ($perStatus[ContactMessage::STATUS_DONE] ?? 0),
        ];
    }

    public function render()
    {
        $messages = ContactMessage::query()
            ->with('handler:id,name')
            ->when($this->filter === 'open', fn ($query) => $query->whereIn('status', [ContactMessage::STATUS_NEW, ContactMessage::STATUS_IN_PROGRESS]))
            ->when(isset(ContactMessage::STATUSES[$this->filter]), fn ($query) => $query->where('status', $this->filter))
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('organization', 'like', "%{$this->search}%")
                ->orWhere('message', 'like', "%{$this->search}%")))
            ->latest('id')
            ->paginate($this->perPage);

        return view('livewire.content.messages', [
            'messages' => $messages,
            'counts' => $this->counts(),
            'current' => $this->editingId ? ContactMessage::with('handler:id,name')->find($this->editingId) : null,
        ]);
    }

    protected function modelClass(): string
    {
        return ContactMessage::class;
    }

    protected function resetForm(): void
    {
        $this->status = ContactMessage::STATUS_NEW;
        $this->note = '';
    }

    protected function fillForm($record): void
    {
        $this->status = $record->status;
        $this->note = (string) $record->note;
    }

    protected function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_keys(ContactMessage::STATUSES))],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return ['status' => 'status', 'note' => 'catatan tindak lanjut'];
    }

    private function normalizeFilter(): void
    {
        if ($this->filter !== 'open' && $this->filter !== 'all' && ! isset(ContactMessage::STATUSES[$this->filter])) {
            $this->filter = 'open';
        }
    }
}
