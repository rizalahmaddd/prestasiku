@php
    use App\Models\Category;
    use App\Support\Site\Palette;
@endphp
<div class="space-y-4 sm:space-y-6">
    <x-segmented class="w-full sm:w-fit">
        @foreach (Category::TYPES as $value => $typeLabel)
            <x-tab-button :active="$type === $value" wire:click="$set('type', '{{ $value }}')" class="flex-1 sm:flex-none">Kategori {{ $typeLabel }}</x-tab-button>
        @endforeach
    </x-segmented>

    <x-list-toolbar
        :description="$type === Category::TYPE_GALLERY ? 'Tombol saringan di tab Galeri.' : 'Tombol saringan di atas daftar prestasi.'"
        search-placeholder="Cari nama kategori..."
        :can-manage="$this->canManage()"
        add-label="Tambah Kategori"
    />

    @if ($categories->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="tags"
                title="{{ $search ? 'Tidak ada kategori yang cocok' : 'Belum ada kategori' }}"
                description="{{ $search ? 'Coba kata kunci lain, atau hapus pencarian.' : 'Tanpa kategori, website hanya menampilkan saringan Semua.' }}"
            />
        </div>
    @else
        <x-table :pagination="$categories">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th>Kategori</x-table.th>
                    <x-table.th>Dipakai</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($categories as $category)
                    <x-table.tr wire:key="category-{{ $category->id }}">
                        <x-table.td>
                            <div class="flex items-center gap-2.5 font-medium text-slate-100">
                                @if ($category->icon)
                                    <i data-lucide="{{ $category->icon }}" class="w-4 h-4 text-slate-400"></i>
                                @endif
                                {{ $category->name }}
                            </div>
                        </x-table.td>
                        <x-table.td class="text-slate-400">{{ $category->items_count }} {{ $type === Category::TYPE_GALLERY ? 'foto' : 'prestasi' }}</x-table.td>
                        <x-table.td align="right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    @unless ($search)
                                        <x-content.order-buttons :id="$category->id" :label="$category->name"
                                            :first="$categories->onFirstPage() && $loop->first" :last="! $categories->hasMorePages() && $loop->last" />
                                    @endunless
                                    <x-content.row-actions :id="$category->id" :label="$category->name" />
                                </div>
                            @endif
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="($editingId ? 'Edit Kategori ' : 'Tambah Kategori ').Category::TYPES[$type]"
        subtitle="Nama tombol saringan di website"
        icon="tags"
        max-width="lg"
    >
        <form wire:submit="save" class="space-y-4">
            <x-content.field for="name" label="Nama kategori *">
                <x-text-input wire:model="name" id="name" class="w-full" placeholder="{{ $type === Category::TYPE_GALLERY ? 'Contoh: Podium & Medali' : 'Contoh: Sains & Matematika' }}" autofocus />
            </x-content.field>

            @if ($type === Category::TYPE_GALLERY)
                <div>
                    <x-input-label value="Ikon tombol" />
                    <div class="mt-1"><x-content.icon-picker wire:model="icon" name="icon" :icons="Palette::GENERAL_ICONS" optional /></div>
                    <x-input-error :messages="$errors->get('icon')" class="mt-1.5" />
                </div>
            @endif

            <x-content.form-actions :label="$editingId ? 'Perbarui Kategori' : 'Simpan Kategori'" />
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal title="Hapus kategori ini?" description="Kategori yang masih dipakai tidak bisa dihapus." />
</div>
