@php
    use App\Models\ContactMessage;

    $filters = [
        'open' => ['label' => 'Perlu ditindaklanjuti', 'count' => $counts['open']],
        ContactMessage::STATUS_NEW => ['label' => 'Baru', 'count' => $counts[ContactMessage::STATUS_NEW]],
        ContactMessage::STATUS_IN_PROGRESS => ['label' => 'Ditindaklanjuti', 'count' => $counts[ContactMessage::STATUS_IN_PROGRESS]],
        ContactMessage::STATUS_DONE => ['label' => 'Selesai', 'count' => $counts[ContactMessage::STATUS_DONE]],
        'all' => ['label' => 'Semua', 'count' => null],
    ];
@endphp
<div class="space-y-4 sm:space-y-6">
    <x-segmented class="w-full sm:w-fit overflow-x-auto" aria-label="Saring status pesan">
        @foreach ($filters as $value => $item)
            <x-tab-button size="sm" :active="$filter === $value" wire:click="$set('filter', '{{ $value }}')" class="flex-1 sm:flex-none">
                {{ $item['label'] }}@if ($item['count'] !== null) <span class="tabular-nums opacity-70">{{ $item['count'] }}</span>@endif
            </x-tab-button>
        @endforeach
    </x-segmented>

    <x-list-toolbar
        description="Pesan dari formulir kontak di website. Buka pesannya untuk membalas dan mencatat tindak lanjut."
        search-placeholder="Cari nama, instansi, atau isi pesan..."
        :can-manage="false"
    />

    @if ($messages->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="inbox"
                title="{{ $search ? 'Tidak ada pesan yang cocok' : ($filter === 'open' ? 'Tidak ada pesan yang menunggu' : 'Belum ada pesan di sini') }}"
                description="{{ $search ? 'Coba kata kunci lain, atau hapus pencarian.' : 'Pesan baru dari formulir kontak website akan muncul di sini dan di lonceng notifikasi.' }}"
            />
        </div>
    @else
        <x-table :pagination="$messages">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th>Pengirim</x-table.th>
                    <x-table.th>Pesan</x-table.th>
                    <x-table.th>Masuk</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($messages as $message)
                    <x-table.tr wire:key="message-{{ $message->id }}">
                        <x-table.td>
                            <div @class(['text-slate-100', 'font-bold' => $message->status === ContactMessage::STATUS_NEW, 'font-medium' => $message->status !== ContactMessage::STATUS_NEW])>{{ $message->name }}</div>
                            <div class="text-[11px] text-slate-400 truncate max-w-[14rem]">{{ $message->organization ?: ($message->phone ?: $message->email) }}</div>
                        </x-table.td>
                        <x-table.td>
                            <div class="text-[11px] font-semibold text-slate-300">{{ $message->topicLabel() }}</div>
                            <div class="text-slate-400 line-clamp-2 max-w-md">{{ $message->message }}</div>
                            @if ($attachmentCount = count($message->attachmentList()))
                                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400 mt-1">
                                    <i data-lucide="paperclip" class="w-3 h-3"></i>{{ $attachmentCount }} lampiran
                                </div>
                            @endif
                        </x-table.td>
                        <x-table.td class="text-slate-400 whitespace-nowrap" title="{{ $message->created_at->translatedFormat('j F Y, H.i') }}">{{ $message->created_at->diffForHumans() }}</x-table.td>
                        <x-table.td>
                            <x-badge :color="ContactMessage::STATUSES[$message->status]['color']">{{ ContactMessage::STATUSES[$message->status]['label'] }}</x-badge>
                            @if ($message->handler)
                                <div class="text-[11px] text-slate-400 mt-1">oleh {{ $message->handler->name }}</div>
                            @endif
                        </x-table.td>
                        <x-table.td align="right">
                            <div class="flex items-center justify-end gap-1">
                                @if ($this->canManage())
                                    @if ($message->status !== ContactMessage::STATUS_DONE)
                                        <x-icon-button icon="check-check" :label="'Tandai selesai pesan '.$message->name" tone="edit" wire:click="markAs({{ $message->id }}, '{{ ContactMessage::STATUS_DONE }}')" />
                                    @endif
                                    <x-icon-button icon="mail-open" :label="'Buka pesan '.$message->name" @click="$dispatch('open-modal', 'record-form')" wire:click="openEditModal({{ $message->id }})" />
                                    <x-icon-button icon="trash" :label="'Hapus pesan '.$message->name" tone="danger" @click="$dispatch('open-modal', 'confirm-delete')" wire:click="confirmDelete({{ $message->id }})" />
                                @endif
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$current ? 'Pesan dari '.$current->name : 'Pesan'"
        :subtitle="$current ? $current->topicLabel().' · '.$current->created_at->translatedFormat('j F Y, H.i') : null"
        icon="mail-open"
        max-width="3xl"
    >
        @if ($current)
            <form wire:submit="save" class="space-y-4 sm:space-y-5">
                <x-content.section title="Isi pesan">
                    <p class="text-sm text-slate-200 leading-relaxed whitespace-pre-line break-words">{{ $current->message }}</p>
                    @if ($current->attachmentList())
                        <div class="pt-3 border-t border-slate-800/80">
                            <h5 class="text-[11px] text-slate-400 mb-1.5">Lampiran</h5>
                            <ul class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                @foreach ($current->attachmentList() as $index => $attachment)
                                    @php($attachmentUrl = route('content.messages.attachment', [$current, $index]))
                                    <li>
                                        <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener"
                                            class="flex items-center gap-2.5 p-2 rounded-lg border border-slate-800 bg-slate-950 hover:border-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 transition">
                                            <span class="shrink-0 w-11 h-11 rounded-md overflow-hidden bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400">
                                                @if (str_starts_with($attachment['mime'], 'image/'))
                                                    <img src="{{ $attachmentUrl }}" alt="" loading="lazy" class="w-full h-full object-cover">
                                                @else
                                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                                @endif
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block text-xs font-medium text-slate-200 truncate" title="{{ $attachment['name'] }}">{{ $attachment['name'] }}</span>
                                                <span class="block text-[11px] text-slate-400">{{ \Illuminate\Support\Number::fileSize($attachment['size'], 1) }}</span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-800/80 text-xs">
                        <div class="min-w-0">
                            <dt class="text-[11px] text-slate-400">Instansi</dt>
                            <dd class="text-slate-200 break-words">{{ $current->organization ?: '-' }}</dd>
                        </div>
                        <div class="min-w-0">
                            <dt class="text-[11px] text-slate-400">WhatsApp</dt>
                            <dd class="text-slate-200 font-mono break-words">{{ $current->phone ?: '-' }}</dd>
                        </div>
                        <div class="min-w-0">
                            <dt class="text-[11px] text-slate-400">Email</dt>
                            <dd class="text-slate-200 break-words">{{ $current->email ?: '-' }}</dd>
                        </div>
                    </dl>
                    <div class="flex flex-col sm:flex-row gap-2">
                        @if ($replyUrl = $current->whatsappReplyUrl())
                            <a href="{{ $replyUrl }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-2 min-h-[44px] px-4 rounded-lg border border-emerald-500/30 text-xs font-semibold text-emerald-400 hover:bg-emerald-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                Balas lewat WhatsApp
                            </a>
                        @endif
                        @if ($mailUrl = $current->emailReplyUrl())
                            <a href="{{ $mailUrl }}"
                                class="inline-flex items-center justify-center gap-2 min-h-[44px] px-4 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:bg-slate-800/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                                Balas lewat email
                            </a>
                        @endif
                    </div>
                </x-content.section>

                <x-content.section title="Tindak lanjut" description="Hanya terlihat di dashboard, tidak dikirim ke pengirim pesan.">
                    <div>
                        <x-input-label value="Status" />
                        <div role="radiogroup" class="mt-1 flex flex-wrap gap-1.5">
                            @foreach (ContactMessage::STATUSES as $value => $definition)
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="{{ $value }}" wire:model="status" class="sr-only peer">
                                    <span class="inline-flex items-center min-h-[44px] sm:min-h-[36px] px-3 rounded-lg border border-slate-800 bg-slate-950 text-xs font-medium text-slate-300 hover:text-slate-100 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-400 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-500/40 transition">{{ $definition['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('status')" class="mt-1.5" />
                    </div>
                    <x-content.field for="note" label="Catatan tindak lanjut" hint="Contoh: sudah dibalas lewat WhatsApp, menunggu proposal dari panitia.">
                        <x-textarea wire:model="note" id="note" rows="3" />
                    </x-content.field>
                    @if ($current->handler)
                        <p class="text-[11px] text-slate-400">Ditangani {{ $current->handler->name }} sejak {{ $current->handled_at?->translatedFormat('j F Y, H.i') }}.</p>
                    @endif
                </x-content.section>

                <x-content.form-actions label="Simpan Tindak Lanjut" />
            </form>
        @endif
    </x-record-form-modal>

    <x-confirm-delete-modal title="Hapus pesan ini?" description="Pesan, lampiran, dan catatan tindak lanjutnya dihapus permanen." />
</div>
