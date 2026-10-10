@php use App\Models\Education; @endphp
<div class="space-y-4 sm:space-y-6">
    <x-list-toolbar
        description="Kartu riwayat pendidikan di samping peta. Yang paling atas jadi lokasi awal peta."
        search-placeholder="Cari nama sekolah..."
        :can-manage="$this->canManage()"
        add-label="Tambah Pendidikan"
    />

    @if ($educations->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="graduation-cap"
                title="{{ $search ? 'Tidak ada pendidikan yang cocok' : 'Belum ada riwayat pendidikan' }}"
                description="{{ $search ? 'Coba kata kunci lain, atau hapus pencarian.' : 'Tambahkan sekolah pertama lewat tombol Tambah Pendidikan.' }}"
            />
        </div>
    @else
        <x-table :pagination="$educations">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th>Sekolah / program</x-table.th>
                    <x-table.th>Periode</x-table.th>
                    <x-table.th>Lokasi di peta</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($educations as $education)
                    <x-table.tr wire:key="education-{{ $education->id }}">
                        <x-table.td>
                            <div class="font-medium text-slate-100">{{ $education->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $education->program ?: '-' }}</div>
                        </x-table.td>
                        <x-table.td class="text-slate-400">
                            <div>{{ $education->period_label }}</div>
                            <div class="text-[11px]">{{ Education::TONES[$education->tone] ?? '' }}</div>
                        </x-table.td>
                        <x-table.td class="text-slate-400">{{ $education->location?->name ?? 'Belum ditautkan' }}</x-table.td>
                        <x-table.td><x-status-badge :active="$education->is_published" on-label="Tampil" off-label="Disembunyikan" /></x-table.td>
                        <x-table.td align="right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    @unless ($search)
                                        <x-content.order-buttons :id="$education->id" :label="$education->name"
                                            :first="$educations->onFirstPage() && $loop->first" :last="! $educations->hasMorePages() && $loop->last" />
                                    @endunless
                                    <x-content.row-actions :id="$education->id" :label="$education->name" />
                                </div>
                            @endif
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$editingId ? 'Edit Riwayat Pendidikan' : 'Tambah Riwayat Pendidikan'"
        subtitle="Satu kartu di bagian Pendidikan"
        icon="graduation-cap"
        max-width="3xl"
    >
        <form wire:submit="save" class="space-y-4 sm:space-y-6">
            <x-content.section title="Sekolah atau program">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <x-content.field for="name" label="Nama sekolah / program *">
                        <x-text-input wire:model="name" id="name" class="w-full" placeholder="Contoh: SMP Labschool Rawamangun" autofocus />
                    </x-content.field>
                    <x-content.field for="program" label="Jurusan / program">
                        <x-text-input wire:model="program" id="program" class="w-full" placeholder="Contoh: Kelas Prestasi" />
                    </x-content.field>
                    <x-content.field for="period_label" label="Periode *">
                        <x-text-input wire:model="period_label" id="period_label" class="w-full" placeholder="Contoh: 2023 - Sekarang (Aktif)" />
                    </x-content.field>
                    <x-content.field for="tone" label="Status" hint="Menentukan warna label periode.">
                        <x-select wire:model="tone" id="tone">
                            @foreach (Education::TONES as $value => $toneLabel)
                                <option value="{{ $value }}">{{ $toneLabel }}</option>
                            @endforeach
                        </x-select>
                    </x-content.field>
                    <x-content.field for="city" label="Kota">
                        <x-text-input wire:model="city" id="city" class="w-full" placeholder="Contoh: Jakarta Timur" />
                    </x-content.field>
                    <x-content.field for="location_id" label="Lokasi di peta" hint="Kartu yang ditautkan bisa diklik untuk memindahkan peta.">
                        <x-select wire:model="location_id" id="location_id">
                            <option value="">Tidak ditautkan</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </x-select>
                    </x-content.field>
                </div>
                <x-content.field for="description" label="Cerita singkat">
                    <x-textarea wire:model="description" id="description" rows="3" />
                </x-content.field>
                <x-content.field for="footnote" label="Catatan di bawah kartu">
                    <x-text-input wire:model="footnote" id="footnote" class="w-full" placeholder="Contoh: Ranking 1 Paralel (2024 & 2025)" />
                </x-content.field>
                <x-checkbox-card wire:model="is_published" label="Tampilkan di website" description="Matikan untuk menyembunyikan kartu tanpa menghapusnya." />
            </x-content.section>

            <x-content.form-actions :label="$editingId ? 'Perbarui Pendidikan' : 'Simpan Pendidikan'" />
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal title="Hapus riwayat pendidikan ini?" description="Tindakan ini tidak bisa dibatalkan." />
</div>
