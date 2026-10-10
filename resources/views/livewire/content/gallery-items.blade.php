<div class="space-y-4 sm:space-y-6">
    <x-list-toolbar
        description="Foto dokumentasi di tab Galeri. Urutan di sini sama dengan urutannya di website."
        search-placeholder="Cari judul atau tempat..."
        :can-manage="$this->canManage()"
        add-label="Tambah Foto"
    />

    @if ($items->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="camera"
                title="{{ $search ? 'Tidak ada foto yang cocok' : 'Galeri masih kosong' }}"
                description="{{ $search ? 'Coba kata kunci lain, atau hapus pencarian.' : 'Tambahkan foto pertama lewat tombol Tambah Foto.' }}"
            />
        </div>
    @else
        <x-table :pagination="$items">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th>Foto</x-table.th>
                    <x-table.th>Kategori</x-table.th>
                    <x-table.th>Tempat & waktu</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($items as $item)
                    <x-table.tr wire:key="gallery-{{ $item->id }}">
                        <x-table.td>
                            <div class="flex items-center gap-3 min-w-0">
                                <x-content.thumb :media="$item->image" icon="camera" />
                                <div class="min-w-0">
                                    <div class="font-medium text-slate-100 truncate max-w-[22rem]">{{ $item->title }}</div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[22rem]">{{ $item->badge ?: '-' }}</div>
                                </div>
                            </div>
                        </x-table.td>
                        <x-table.td class="text-slate-400">{{ $item->category?->name ?? '-' }}</x-table.td>
                        <x-table.td class="text-slate-400">
                            <div>{{ $item->place ?: '-' }}</div>
                            <div class="text-[11px]">{{ $item->taken_on?->translatedFormat('F Y') }}</div>
                        </x-table.td>
                        <x-table.td><x-status-badge :active="$item->is_published" on-label="Tampil" off-label="Disembunyikan" /></x-table.td>
                        <x-table.td align="right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    @unless ($search)
                                        <x-content.order-buttons :id="$item->id" :label="$item->title"
                                            :first="$items->onFirstPage() && $loop->first" :last="! $items->hasMorePages() && $loop->last" />
                                    @endunless
                                    <x-content.row-actions :id="$item->id" :label="$item->title" />
                                </div>
                            @endif
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$editingId ? 'Edit Foto Galeri' : 'Tambah Foto Galeri'"
        subtitle="Foto dokumentasi beserta keterangannya"
        icon="camera"
        max-width="4xl"
    >
        <form wire:submit="save" class="space-y-4 sm:space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 items-start">
                <x-content.section title="Foto">
                    @include('livewire.content.partials.image-picker', ['field' => 'image_id', 'label' => 'Foto *', 'hint' => 'Foto mendatar (4:3) paling pas di kartu galeri.'])
                    <x-content.field for="badge" label="Label di atas foto" hint="Teks pendek di pojok kiri atas foto.">
                        <x-text-input wire:model="badge" id="badge" class="w-full" placeholder="Contoh: Podium Tokyo Tech 2025" />
                    </x-content.field>
                    <x-checkbox-card wire:model="is_published" label="Tampilkan di website" description="Matikan untuk menyembunyikan foto tanpa menghapusnya." />
                </x-content.section>

                <x-content.section title="Keterangan">
                    <x-content.field for="title" label="Judul foto *">
                        <x-text-input wire:model="title" id="title" class="w-full" placeholder="Contoh: Penganugerahan Juara 1 Robotika di Tokyo" autofocus />
                    </x-content.field>
                    <x-content.field for="description" label="Keterangan" hint="Dua baris pertama tampil di kartu, selengkapnya saat foto dibuka.">
                        <x-textarea wire:model="description" id="description" rows="4" />
                    </x-content.field>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <x-content.field for="place" label="Tempat">
                            <x-text-input wire:model="place" id="place" class="w-full" placeholder="Contoh: Tokyo, Jepang" />
                        </x-content.field>
                        <x-content.field for="taken_on" label="Tanggal" hint="Yang ditampilkan hanya bulan dan tahun.">
                            <x-text-input wire:model="taken_on" id="taken_on" type="date" class="w-full" />
                        </x-content.field>
                    </div>
                    <x-content.field for="category_id" label="Kategori">
                        <x-select wire:model="category_id" id="category_id">
                            <option value="">Tanpa kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-select>
                    </x-content.field>
                </x-content.section>
            </div>

            <x-content.form-actions :label="$editingId ? 'Perbarui Foto' : 'Simpan Foto'" />
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal title="Hapus foto ini dari galeri?" description="Berkas gambarnya tetap ada di Pustaka Media. Tindakan ini tidak bisa dibatalkan." />
</div>
