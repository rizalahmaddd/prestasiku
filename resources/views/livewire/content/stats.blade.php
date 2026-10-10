@php
    use App\Models\Stat;
    use App\Support\Site\Palette;
@endphp
<div class="space-y-4 sm:space-y-6">
    <x-segmented class="w-full sm:w-fit">
        @foreach (Stat::GROUPS as $value => $groupLabel)
            <x-tab-button :active="$group === $value" wire:click="$set('group', '{{ $value }}')" class="flex-1 sm:flex-none">{{ $groupLabel }}</x-tab-button>
        @endforeach
    </x-segmented>

    <x-list-toolbar
        :description="$group === Stat::GROUP_ROADMAP ? 'Tiga angka kecil di samping judul Rekam Jejak.' : 'Empat angka besar tepat di bawah hero.'"
        search-placeholder="Cari judul angka..."
        :can-manage="$this->canManage()"
        add-label="Tambah Angka"
    />

    @if ($stats->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="hash"
                title="{{ $search ? 'Tidak ada angka yang cocok' : 'Belum ada angka di kelompok ini' }}"
                description="{{ $search ? 'Coba kata kunci lain, atau hapus pencarian.' : 'Barisan angka ini disembunyikan di website selama masih kosong.' }}"
            />
        </div>
    @else
        <x-table :pagination="$stats">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th>Angka</x-table.th>
                    <x-table.th>Judul</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($stats as $stat)
                    <x-table.tr wire:key="stat-{{ $stat->id }}">
                        <x-table.td>
                            <span class="inline-flex items-center gap-2 font-mono font-bold text-slate-100">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ Palette::TONES[$stat->tone]['swatch'] ?? '#94a3b8' }}"></span>
                                {{ $stat->value }}{{ $stat->suffix }}
                            </span>
                        </x-table.td>
                        <x-table.td>
                            <div class="font-medium text-slate-100">{{ $stat->label }}</div>
                            <div class="text-[11px] text-slate-400">{{ $stat->caption }}</div>
                        </x-table.td>
                        <x-table.td align="right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    @unless ($search)
                                        <x-content.order-buttons :id="$stat->id" :label="$stat->label"
                                            :first="$stats->onFirstPage() && $loop->first" :last="! $stats->hasMorePages() && $loop->last" />
                                    @endunless
                                    <x-content.row-actions :id="$stat->id" :label="$stat->label" />
                                </div>
                            @endif
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$editingId ? 'Edit Angka' : 'Tambah Angka'"
        :subtitle="Stat::GROUPS[$group]"
        icon="hash"
        max-width="lg"
    >
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-3 gap-3.5">
                <x-content.field for="value" label="Angka *" class="col-span-2">
                    <x-text-input wire:model="value" id="value" type="number" min="0" class="w-full font-mono" placeholder="18" autofocus />
                </x-content.field>
                <x-content.field for="suffix" label="Akhiran">
                    <x-text-input wire:model="suffix" id="suffix" class="w-full font-mono" placeholder="+" />
                </x-content.field>
            </div>
            <x-content.field for="label" label="Judul *">
                <x-text-input wire:model="label" id="label" class="w-full" placeholder="Contoh: Medali Emas & Juara 1" />
            </x-content.field>
            @if ($group === Stat::GROUP_SUMMARY)
                <x-content.field for="caption" label="Keterangan kecil">
                    <x-text-input wire:model="caption" id="caption" class="w-full" placeholder="Contoh: Olimpiade Sains & Robotika" />
                </x-content.field>
            @endif
            <div>
                <x-input-label value="Warna angka" />
                <div class="mt-1"><x-content.tone-picker wire:model="tone" name="tone" /></div>
                <x-input-error :messages="$errors->get('tone')" class="mt-1.5" />
            </div>

            <x-content.form-actions :label="$editingId ? 'Perbarui Angka' : 'Simpan Angka'" />
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal title="Hapus angka ini?" description="Tindakan ini tidak bisa dibatalkan." />
</div>
