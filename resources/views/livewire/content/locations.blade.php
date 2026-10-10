@php use App\Support\Site\Palette; @endphp
<div class="space-y-4 sm:space-y-6">
    <x-list-toolbar
        description="Titik di peta website: sekolah, tempat latihan, dan tempat lomba."
        search-placeholder="Cari nama tempat atau alamat..."
        :can-manage="$this->canManage()"
        add-label="Tambah Lokasi"
    />

    @if ($locations->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="map-pin"
                title="{{ $search ? 'Tidak ada lokasi yang cocok' : 'Belum ada lokasi' }}"
                description="{{ $search ? 'Coba kata kunci lain, atau hapus pencarian.' : 'Peta di website baru muncul setelah ada minimal satu lokasi.' }}"
            />
        </div>
    @else
        <x-table :pagination="$locations">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th>Lokasi</x-table.th>
                    <x-table.th>Koordinat</x-table.th>
                    <x-table.th>Terhubung ke</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($locations as $location)
                    <x-table.tr wire:key="location-{{ $location->id }}">
                        <x-table.td>
                            <div class="flex items-center gap-3 min-w-0">
                                <x-content.thumb :media="$location->image" icon="map-pin" />
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 font-medium text-slate-100">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $location->color }}"></span>
                                        <span class="truncate max-w-[20rem]">{{ $location->name }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[22rem]">{{ $location->address ?: '-' }}</div>
                                </div>
                            </div>
                        </x-table.td>
                        <x-table.td class="font-mono text-slate-400 whitespace-nowrap">{{ number_format($location->lat, 5, '.', '') }}, {{ number_format($location->lng, 5, '.', '') }}</x-table.td>
                        <x-table.td class="text-slate-400">
                            @if ($location->educations_count + $location->achievements_count === 0)
                                Tombol lokasi lain
                            @else
                                {{ collect([$location->educations_count ? $location->educations_count.' pendidikan' : null, $location->achievements_count ? $location->achievements_count.' prestasi' : null])->filter()->implode(', ') }}
                            @endif
                        </x-table.td>
                        <x-table.td align="right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    @unless ($search)
                                        <x-content.order-buttons :id="$location->id" :label="$location->name"
                                            :first="$locations->onFirstPage() && $loop->first" :last="! $locations->hasMorePages() && $loop->last" />
                                    @endunless
                                    <x-content.row-actions :id="$location->id" :label="$location->name" />
                                </div>
                            @endif
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$editingId ? 'Edit Lokasi' : 'Tambah Lokasi'"
        subtitle="Titik di peta beserta kartu keterangannya"
        icon="map-pin"
        max-width="5xl"
    >
        <form wire:submit="save" class="space-y-4 sm:space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 items-start">
                <x-content.section title="Titik di peta" description="Cari tempatnya, lalu rapikan posisi penandanya.">
                    @include('livewire.content.partials.map-picker')

                    <div>
                        <x-input-label for="color" value="Warna penanda" />
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            @foreach (Palette::PIN_COLORS as $pinColor)
                                <label class="cursor-pointer">
                                    <input type="radio" name="color-preset" value="{{ $pinColor }}" wire:model="color" class="sr-only peer">
                                    <span class="block w-9 h-9 sm:w-7 sm:h-7 rounded-full border-2 border-slate-900 ring-1 ring-slate-700 peer-checked:ring-2 peer-checked:ring-emerald-400 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-400 transition" style="background-color: {{ $pinColor }}"></span>
                                    <span class="sr-only">Warna {{ $pinColor }}</span>
                                </label>
                            @endforeach
                            <input type="color" id="color" wire:model="color" aria-label="Warna lain" class="w-11 h-11 sm:w-9 sm:h-9 p-0.5 rounded-lg bg-slate-950 border border-slate-800 cursor-pointer">
                        </div>
                        <x-input-error :messages="$errors->get('color')" class="mt-1.5" />
                    </div>
                </x-content.section>

                <div class="space-y-5">
                    <x-content.section title="Keterangan tempat">
                        <x-content.field for="name" label="Nama tempat *">
                            <x-text-input wire:model="name" id="name" class="w-full" placeholder="Contoh: SMP Labschool Rawamangun" autofocus />
                        </x-content.field>
                        <x-content.field for="subtitle" label="Keterangan singkat">
                            <x-text-input wire:model="subtitle" id="subtitle" class="w-full" placeholder="Contoh: Sekolah formal aktif" />
                        </x-content.field>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <x-content.field for="badge" label="Label">
                                <x-text-input wire:model="badge" id="badge" class="w-full" placeholder="Contoh: Sekolah Utama" />
                            </x-content.field>
                            <x-content.field for="status" label="Status">
                                <x-text-input wire:model="status" id="status" class="w-full" placeholder="Contoh: Aktif (Kelas IX)" />
                            </x-content.field>
                        </div>
                        <x-content.field for="address" label="Alamat" hint="Terisi otomatis dari hasil pencarian peta kalau masih kosong.">
                            <x-textarea wire:model="address" id="address" rows="2" />
                        </x-content.field>
                        <x-content.field for="short_name" label="Nama pendek" hint="Dipakai di tombol kecil kalau lokasi ini tidak terhubung ke riwayat pendidikan.">
                            <x-text-input wire:model="short_name" id="short_name" class="w-full" placeholder="Contoh: Tokyo Tech" />
                        </x-content.field>
                    </x-content.section>

                    <x-content.section title="Foto tempat">
                        @include('livewire.content.partials.image-picker', ['field' => 'image_id', 'label' => 'Foto'])
                        <x-content.field for="caption" label="Keterangan foto">
                            <x-text-input wire:model="caption" id="caption" class="w-full" placeholder="Contoh: Gedung utama dan laboratorium sains" />
                        </x-content.field>
                    </x-content.section>
                </div>
            </div>

            <x-content.form-actions :label="$editingId ? 'Perbarui Lokasi' : 'Simpan Lokasi'" />
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal title="Hapus lokasi ini?" description="Pendidikan dan prestasi yang terhubung tetap ada, hanya tautan petanya yang dilepas." />
</div>
