@php use App\Support\Site\Palette; @endphp
<div class="space-y-4 sm:space-y-6">
    <x-list-toolbar
        description="Kartu bidang minat di bagian Profil. Tiga kartu paling pas untuk satu baris."
        search-placeholder="Cari judul..."
        :can-manage="$this->canManage()"
        add-label="Tambah Bidang Minat"
    />

    @if ($interests->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="compass"
                title="{{ $search ? 'Tidak ada bidang minat yang cocok' : 'Belum ada bidang minat' }}"
                description="{{ $search ? 'Coba kata kunci lain, atau hapus pencarian.' : 'Tambahkan bidang yang ditekuni lewat tombol Tambah Bidang Minat.' }}"
            />
        </div>
    @else
        <x-table :pagination="$interests">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th>Bidang</x-table.th>
                    <x-table.th>Label</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($interests as $interest)
                    <x-table.tr wire:key="interest-{{ $interest->id }}">
                        <x-table.td>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-800 bg-slate-950 shrink-0" style="color: {{ Palette::TONES[$interest->tone]['swatch'] ?? '#94a3b8' }}">
                                    <i data-lucide="{{ $interest->icon }}" class="w-4 h-4"></i>
                                </span>
                                <span class="font-medium text-slate-100">{{ $interest->title }}</span>
                            </div>
                        </x-table.td>
                        <x-table.td class="text-slate-400">{{ implode(', ', $interest->tags ?? []) ?: '-' }}</x-table.td>
                        <x-table.td align="right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    @unless ($search)
                                        <x-content.order-buttons :id="$interest->id" :label="$interest->title"
                                            :first="$interests->onFirstPage() && $loop->first" :last="! $interests->hasMorePages() && $loop->last" />
                                    @endunless
                                    <x-content.row-actions :id="$interest->id" :label="$interest->title" />
                                </div>
                            @endif
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$editingId ? 'Edit Bidang Minat' : 'Tambah Bidang Minat'"
        subtitle="Satu kartu di bagian Profil"
        icon="compass"
        max-width="2xl"
    >
        <form wire:submit="save" class="space-y-4">
            <x-content.field for="title" label="Judul *">
                <x-text-input wire:model="title" id="title" class="w-full" placeholder="Contoh: Robotika Otonom & IoT" autofocus />
            </x-content.field>
            <x-content.field for="description" label="Penjelasan">
                <x-textarea wire:model="description" id="description" rows="4" />
            </x-content.field>
            <x-content.field for="tags" label="Label kecil" hint="Pisahkan dengan koma. Contoh: Arduino C++, WRO RoboMission">
                <x-text-input wire:model="tags" id="tags" class="w-full" />
            </x-content.field>
            <div>
                <x-input-label value="Ikon" />
                <div class="mt-1"><x-content.icon-picker wire:model="icon" name="icon" :icons="Palette::GENERAL_ICONS" /></div>
                <x-input-error :messages="$errors->get('icon')" class="mt-1.5" />
            </div>
            <div>
                <x-input-label value="Warna" />
                <div class="mt-1"><x-content.tone-picker wire:model="tone" name="tone" /></div>
                <x-input-error :messages="$errors->get('tone')" class="mt-1.5" />
            </div>

            <x-content.form-actions :label="$editingId ? 'Perbarui Bidang Minat' : 'Simpan Bidang Minat'" />
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal title="Hapus bidang minat ini?" description="Tindakan ini tidak bisa dibatalkan." />
</div>
