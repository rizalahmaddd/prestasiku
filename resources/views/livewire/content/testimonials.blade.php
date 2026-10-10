<div class="space-y-4 sm:space-y-6">
    <x-list-toolbar
        description="Catatan guru dan pembina. Tiga testimoni paling pas untuk satu baris."
        search-placeholder="Cari nama atau isi testimoni..."
        :can-manage="$this->canManage()"
        add-label="Tambah Testimoni"
    />

    @if ($testimonials->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="message-square-quote"
                title="{{ $search ? 'Tidak ada testimoni yang cocok' : 'Belum ada testimoni' }}"
                description="{{ $search ? 'Coba kata kunci lain, atau hapus pencarian.' : 'Bagian testimoni di website disembunyikan selama masih kosong.' }}"
            />
        </div>
    @else
        <x-table :pagination="$testimonials">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th>Dari</x-table.th>
                    <x-table.th>Isi</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($testimonials as $testimonial)
                    <x-table.tr wire:key="testimonial-{{ $testimonial->id }}">
                        <x-table.td>
                            <div class="flex items-center gap-3 min-w-0">
                                <x-content.thumb :media="$testimonial->image" icon="user-round" round />
                                <div class="min-w-0">
                                    <div class="font-medium text-slate-100">{{ $testimonial->name }}</div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[16rem]">{{ $testimonial->role ?: '-' }}</div>
                                </div>
                            </div>
                        </x-table.td>
                        <x-table.td class="text-slate-400"><span class="line-clamp-2 max-w-md">{{ $testimonial->quote }}</span></x-table.td>
                        <x-table.td><x-status-badge :active="$testimonial->is_published" on-label="Tampil" off-label="Disembunyikan" /></x-table.td>
                        <x-table.td align="right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    @unless ($search)
                                        <x-content.order-buttons :id="$testimonial->id" :label="'testimoni '.$testimonial->name"
                                            :first="$testimonials->onFirstPage() && $loop->first" :last="! $testimonials->hasMorePages() && $loop->last" />
                                    @endunless
                                    <x-content.row-actions :id="$testimonial->id" :label="'testimoni '.$testimonial->name" />
                                </div>
                            @endif
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$editingId ? 'Edit Testimoni' : 'Tambah Testimoni'"
        subtitle="Catatan dari guru atau pembina"
        icon="message-square-quote"
        max-width="2xl"
    >
        <form wire:submit="save" class="space-y-4">
            <x-content.field for="quote" label="Isi testimoni *" hint="Tanda kutip ditambahkan otomatis di website.">
                <x-textarea wire:model="quote" id="quote" rows="5" autofocus />
            </x-content.field>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <x-content.field for="name" label="Nama *">
                    <x-text-input wire:model="name" id="name" class="w-full" placeholder="Contoh: Drs. Rahmadi, M.Pd." />
                </x-content.field>
                <x-content.field for="role" label="Jabatan">
                    <x-text-input wire:model="role" id="role" class="w-full" placeholder="Contoh: Guru Pembina Olimpiade Sains" />
                </x-content.field>
            </div>
            @include('livewire.content.partials.image-picker', ['field' => 'image_id', 'label' => 'Foto', 'round' => true, 'hint' => 'Foto wajah berbentuk persegi paling rapi.'])
            <x-checkbox-card wire:model="is_published" label="Tampilkan di website" description="Matikan untuk menyembunyikan testimoni tanpa menghapusnya." />

            <x-content.form-actions :label="$editingId ? 'Perbarui Testimoni' : 'Simpan Testimoni'" />
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal title="Hapus testimoni ini?" description="Tindakan ini tidak bisa dibatalkan." />
</div>
