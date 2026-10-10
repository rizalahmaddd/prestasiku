@php
    use App\Models\Achievement;
    use App\Support\Site\Palette;
@endphp
<div class="space-y-4 sm:space-y-6">
    <x-list-toolbar
        description="Prestasi yang tampil di halaman depan. Urutan di sini sama dengan urutan kartunya di website."
        search-placeholder="Cari nama lomba atau penyelenggara..."
        :can-manage="$this->canManage()"
        add-label="Tambah Prestasi"
    />

    @if ($achievements->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="trophy"
                title="{{ $search ? 'Tidak ada prestasi yang cocok' : 'Belum ada prestasi' }}"
                description="{{ $search ? 'Coba kata kunci lain, atau hapus pencarian.' : 'Tambahkan prestasi pertama lewat tombol Tambah Prestasi.' }}"
            />
        </div>
    @else
        <x-table :pagination="$achievements">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th>Prestasi</x-table.th>
                    <x-table.th>Hasil</x-table.th>
                    <x-table.th>Tingkat</x-table.th>
                    <x-table.th>Tanggal</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($achievements as $achievement)
                    <x-table.tr wire:key="achievement-{{ $achievement->id }}">
                        <x-table.td>
                            <div class="flex items-center gap-3 min-w-0">
                                <x-content.thumb :media="$achievement->image ?? $achievement->galleryItem?->image" icon="trophy" />
                                <div class="min-w-0">
                                    <div class="font-medium text-slate-100 truncate max-w-[22rem]">{{ $achievement->title }}</div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[22rem]">
                                        {{ $achievement->organizer ?: '-' }}
                                        @if ($achievement->categories->isNotEmpty())
                                            &middot; {{ $achievement->categories->pluck('name')->implode(', ') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </x-table.td>
                        <x-table.td class="text-slate-300">{{ $achievement->badge }}</x-table.td>
                        <x-table.td class="text-slate-400">{{ Achievement::LEVELS[$achievement->level] ?? $achievement->level }}</x-table.td>
                        <x-table.td class="text-slate-400 whitespace-nowrap">{{ $achievement->achieved_on->translatedFormat('j M Y') }}</x-table.td>
                        <x-table.td>
                            <div class="flex flex-wrap gap-1">
                                <x-status-badge :active="$achievement->is_published" on-label="Tampil" off-label="Disembunyikan" />
                                @if ($achievement->is_featured)
                                    <x-badge color="amber">Kartu utama</x-badge>
                                @endif
                            </div>
                        </x-table.td>
                        <x-table.td align="right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    @unless ($search)
                                        <x-content.order-buttons :id="$achievement->id" :label="$achievement->title"
                                            :first="$achievements->onFirstPage() && $loop->first" :last="! $achievements->hasMorePages() && $loop->last" />
                                    @endunless
                                    <x-content.row-actions :id="$achievement->id" :label="$achievement->title" />
                                </div>
                            @endif
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$editingId ? 'Edit Prestasi' : 'Tambah Prestasi'"
        subtitle="Kartu prestasi, detail piagam, foto, dan lokasinya"
        icon="trophy"
        max-width="5xl"
    >
        <form wire:submit="save" class="space-y-4 sm:space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 items-start">
                <div class="space-y-5">
                    <x-content.section title="Lomba dan hasilnya">
                        <x-content.field for="title" label="Nama lomba *">
                            <x-text-input wire:model="title" id="title" class="w-full" placeholder="Contoh: Olimpiade Sains Nasional (OSN) SMP - Bidang IPA" autofocus />
                        </x-content.field>
                        <x-content.field for="organizer" label="Penyelenggara">
                            <x-text-input wire:model="organizer" id="organizer" class="w-full" placeholder="Contoh: Puspresnas, Kemendikbudristek RI" />
                        </x-content.field>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <x-content.field for="badge" label="Hasil yang diraih *" hint="Tampil sebagai label di atas foto kartu.">
                                <x-text-input wire:model="badge" id="badge" class="w-full" placeholder="Contoh: Medali Perak Nasional" />
                            </x-content.field>
                            <x-content.field for="achieved_on" label="Tanggal *">
                                <x-text-input wire:model="achieved_on" id="achieved_on" type="date" class="w-full" />
                            </x-content.field>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <x-content.field for="level" label="Tingkat *">
                                <x-select wire:model="level" id="level">
                                    @foreach (Achievement::LEVELS as $value => $levelLabel)
                                        <option value="{{ $value }}">{{ $levelLabel }}</option>
                                    @endforeach
                                </x-select>
                            </x-content.field>
                            <x-content.field for="medal" label="Medali" hint="Dipakai penghitung medali di website.">
                                <x-select wire:model="medal" id="medal">
                                    <option value="">Bukan medali / lainnya</option>
                                    @foreach (Achievement::MEDALS as $value => $medalLabel)
                                        <option value="{{ $value }}">{{ $medalLabel }}</option>
                                    @endforeach
                                </x-select>
                            </x-content.field>
                        </div>
                        <x-content.field for="description" label="Cerita singkat" hint="Dua sampai tiga kalimat: apa yang dikerjakan dan apa hasilnya.">
                            <x-textarea wire:model="description" id="description" rows="4" />
                        </x-content.field>
                    </x-content.section>

                    <x-content.section title="Pengelompokan" description="Menentukan di saringan mana prestasi ini muncul.">
                        <div>
                            <x-input-label value="Kategori" />
                            @if ($categories->isEmpty())
                                <p class="text-xs text-slate-400 mt-1">Belum ada kategori prestasi. Tambahkan dulu di menu Kategori.</p>
                            @else
                                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                                    @foreach ($categories as $category)
                                        <x-checkbox wire:model="category_ids" value="{{ $category->id }}" :label="$category->name" />
                                    @endforeach
                                </div>
                            @endif
                            <x-input-error :messages="$errors->get('category_ids')" class="mt-1.5" />
                        </div>
                        <x-content.field for="tags" label="Label kecil" hint="Pisahkan dengan koma. Contoh: Tingkat Nasional, 38 Provinsi">
                            <x-text-input wire:model="tags" id="tags" class="w-full" />
                        </x-content.field>
                        <x-content.field for="keywords" label="Kata kunci pencarian" hint="Kata tambahan supaya mudah ditemukan lewat kolom cari di website.">
                            <x-text-input wire:model="keywords" id="keywords" class="w-full" placeholder="Contoh: osn ipa perak" />
                        </x-content.field>
                    </x-content.section>
                </div>

                <div class="space-y-5">
                    <x-content.section title="Foto dan tampilan kartu">
                        @include('livewire.content.partials.image-picker', ['field' => 'image_id', 'label' => 'Foto kartu', 'hint' => 'Kalau dikosongkan, kartu memakai foto galeri yang ditautkan di bawah.'])

                        <div>
                            <x-input-label value="Ikon hasil" />
                            <div class="mt-1"><x-content.icon-picker wire:model="icon" name="icon" :icons="Palette::ACHIEVEMENT_ICONS" /></div>
                            <x-input-error :messages="$errors->get('icon')" class="mt-1.5" />
                        </div>
                        <div>
                            <x-input-label value="Warna ikon" />
                            <div class="mt-1"><x-content.tone-picker wire:model="tone" name="tone" /></div>
                            <x-input-error :messages="$errors->get('tone')" class="mt-1.5" />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <x-content.field for="gallery_item_id" label="Foto galeri terkait" hint="Memunculkan tombol Lihat Foto di detail prestasi.">
                                <x-select wire:model="gallery_item_id" id="gallery_item_id">
                                    <option value="">Tidak ditautkan</option>
                                    @foreach ($galleryItems as $galleryItem)
                                        <option value="{{ $galleryItem->id }}">{{ $galleryItem->title }}</option>
                                    @endforeach
                                </x-select>
                            </x-content.field>
                            <x-content.field for="location_id" label="Lokasi di peta" hint="Memunculkan tombol Lokasi di kartu.">
                                <x-select wire:model="location_id" id="location_id">
                                    <option value="">Tidak ditautkan</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </x-select>
                            </x-content.field>
                        </div>
                        <x-checkbox-card wire:model="is_featured" label="Jadikan kartu utama" description="Tampil paling besar dengan label Prestasi Tertinggi. Hanya satu prestasi yang bisa jadi kartu utama." />
                        <x-checkbox-card wire:model="is_published" label="Tampilkan di website" description="Matikan untuk menyimpan sebagai draf tanpa menghapusnya." />
                    </x-content.section>

                    <x-content.section title="Detail piagam" description="Tampil saat kartu diklik. Kolom yang dikosongkan memakai isian di sebelah kiri.">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <x-content.field for="cert_number" label="Nomor piagam">
                                <x-text-input wire:model="cert_number" id="cert_number" class="w-full font-mono" placeholder="PUSPRESNAS/OSN-SMP/2024/0912" />
                            </x-content.field>
                            <x-content.field for="reference_no" label="Nomor singkat" hint="Tampil di pojok kartu.">
                                <x-text-input wire:model="reference_no" id="reference_no" class="w-full font-mono" placeholder="OSN-24-IPA-41" />
                            </x-content.field>
                        </div>
                        <x-content.field for="cert_predicate" label="Predikat">
                            <x-text-input wire:model="cert_predicate" id="cert_predicate" class="w-full" placeholder="Contoh: Medali Perak Tingkat Nasional" />
                        </x-content.field>
                        <x-content.field for="cert_title" label="Judul di piagam">
                            <x-text-input wire:model="cert_title" id="cert_title" class="w-full" />
                        </x-content.field>
                        <x-content.field for="cert_organizer" label="Penerbit piagam">
                            <x-text-input wire:model="cert_organizer" id="cert_organizer" class="w-full" />
                        </x-content.field>
                        <x-content.field for="cert_description" label="Keterangan piagam">
                            <x-textarea wire:model="cert_description" id="cert_description" rows="3" />
                        </x-content.field>
                        <x-content.field for="cert_tags" label="Label piagam" hint="Pisahkan dengan koma.">
                            <x-text-input wire:model="cert_tags" id="cert_tags" class="w-full" />
                        </x-content.field>
                    </x-content.section>
                </div>
            </div>

            <x-content.form-actions :label="$editingId ? 'Perbarui Prestasi' : 'Simpan Prestasi'" />
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal title="Hapus prestasi ini?" description="Prestasi langsung hilang dari website. Tindakan ini tidak bisa dibatalkan." />
</div>
