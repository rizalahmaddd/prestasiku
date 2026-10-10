@php use App\Models\RoadmapStep; @endphp
<div class="space-y-4 sm:space-y-6">
    <x-list-toolbar
        description="Tahapan di garis Rekam Jejak, dari yang paling awal ke yang terbaru."
        search-placeholder="Cari judul atau periode..."
        :can-manage="$this->canManage()"
        add-label="Tambah Tahap"
    />

    @if ($steps->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="milestone"
                title="{{ $search ? 'Tidak ada tahap yang cocok' : 'Belum ada tahap rekam jejak' }}"
                description="{{ $search ? 'Coba kata kunci lain, atau hapus pencarian.' : 'Bagian Rekam Jejak di website disembunyikan selama belum ada tahap.' }}"
            />
        </div>
    @else
        <x-table :pagination="$steps">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th>Tahap</x-table.th>
                    <x-table.th>Periode</x-table.th>
                    <x-table.th>Tampilan</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($steps as $step)
                    <x-table.tr wire:key="step-{{ $step->id }}">
                        <x-table.td>
                            <div class="font-medium text-slate-100">{{ $step->title }}</div>
                            <div class="text-[11px] text-slate-400">{{ $step->tag ?: '-' }}</div>
                        </x-table.td>
                        <x-table.td class="text-slate-400 whitespace-nowrap">{{ $step->period }}</x-table.td>
                        <x-table.td class="text-slate-400">{{ RoadmapStep::STATES[$step->state] ?? $step->state }}</x-table.td>
                        <x-table.td align="right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    @unless ($search)
                                        <x-content.order-buttons :id="$step->id" :label="$step->title"
                                            :first="$steps->onFirstPage() && $loop->first" :last="! $steps->hasMorePages() && $loop->last" />
                                    @endunless
                                    <x-content.row-actions :id="$step->id" :label="$step->title" />
                                </div>
                            @endif
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$editingId ? 'Edit Tahap' : 'Tambah Tahap'"
        subtitle="Satu titik di garis Rekam Jejak"
        icon="milestone"
        max-width="2xl"
    >
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <x-content.field for="period" label="Periode *">
                    <x-text-input wire:model="period" id="period" class="w-full" placeholder="Contoh: 2023 – 2024" autofocus />
                </x-content.field>
                <x-content.field for="tag" label="Label kecil">
                    <x-text-input wire:model="tag" id="tag" class="w-full" placeholder="Contoh: Kontingen DKI" />
                </x-content.field>
            </div>
            <x-content.field for="title" label="Judul tahap *">
                <x-text-input wire:model="title" id="title" class="w-full" placeholder="Contoh: SMP Labschool & OSN" />
            </x-content.field>
            <x-content.field for="description" label="Cerita singkat">
                <x-textarea wire:model="description" id="description" rows="3" />
            </x-content.field>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <x-content.field for="state" label="Tampilan *">
                    <x-select wire:model="state" id="state">
                        @foreach (RoadmapStep::STATES as $value => $stateLabel)
                            <option value="{{ $value }}">{{ $stateLabel }}</option>
                        @endforeach
                    </x-select>
                </x-content.field>
                <x-content.field for="jump_target" label="Saat diklik, saring prestasi" hint="Pengunjung dibawa ke daftar prestasi yang sudah tersaring.">
                    <x-select wire:model="jump_target" id="jump_target" :searchable="false">
                        <option value="">Tampilkan semua prestasi</option>
                        @foreach ($jumpTargets as $groupLabel => $options)
                            @if ($options !== [])
                                <optgroup label="{{ $groupLabel }}">
                                    @foreach ($options as $value => $optionLabel)
                                        <option value="{{ $value }}">{{ $optionLabel }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </x-select>
                </x-content.field>
            </div>

            <x-content.form-actions :label="$editingId ? 'Perbarui Tahap' : 'Simpan Tahap'" />
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal title="Hapus tahap ini?" description="Tindakan ini tidak bisa dibatalkan." />
</div>
