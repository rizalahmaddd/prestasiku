<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">
        <p class="text-xs text-slate-400 max-w-2xl">Semua gambar yang dipakai website. Gambar lebar otomatis dikecilkan ke {{ \App\Support\Site\MediaStore::MAX_EDGE }}px supaya halaman tetap ringan.</p>
        <x-search-input class="lg:w-64" wire:model.live.debounce.400ms="search" placeholder="Cari nama gambar..." />
    </div>

    @if ($this->canManage())
        <div>
            <x-file-input wire:model="uploads" id="uploads" multiple accept="image/png,image/jpeg,image/webp" icon="image-up"
                label="Unggah gambar" hint="Klik atau seret beberapa gambar sekaligus. JPG, PNG, atau WebP, maks. 10 MB per gambar." />
            <x-input-error :messages="array_unique([...$errors->get('uploads'), ...collect($errors->get('uploads.*'))->flatten()->all()])" class="mt-1.5" />
        </div>
    @endif

    @if ($media->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="images"
                title="{{ trim($search) !== '' ? 'Tidak ada gambar yang cocok' : 'Pustaka media masih kosong' }}"
                description="{{ trim($search) !== '' ? 'Coba kata kunci lain, atau hapus pencarian.' : 'Unggah gambar di sini, atau langsung dari formulir prestasi, galeri, dan lainnya.' }}"
            />
        </div>
    @else
        {{-- Masonry lewat kolom CSS: tiap kartu setinggi gambarnya, tanpa ruang kosong di bawah. --}}
        <ul class="columns-2 sm:columns-3 lg:columns-4 xl:columns-6 gap-3">
            @foreach ($media as $item)
                @php($used = $item->usageCount())
                <li wire:key="media-{{ $item->id }}" class="mb-3 break-inside-avoid bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
                    <a href="{{ $item->url() }}" target="_blank" rel="noopener" class="block bg-slate-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" aria-label="Buka {{ $item->name }} di tab baru">
                        <img src="{{ $item->url() }}" alt="" loading="lazy" @if ($item->width && $item->height) width="{{ $item->width }}" height="{{ $item->height }}" @endif class="block w-full h-auto">
                    </a>
                    <div class="p-2.5 flex items-start gap-1">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-slate-200 truncate" title="{{ $item->name }}">{{ $item->name }}</p>
                            <p class="text-[11px] text-slate-400">
                                {{ $item->width && $item->height ? $item->width.' x '.$item->height.' · ' : '' }}{{ \Illuminate\Support\Number::fileSize($item->size, 1) }}
                            </p>
                            <p class="text-[11px] {{ $used ? 'text-emerald-400' : 'text-slate-400' }}">{{ $used ? 'Dipakai di '.$used.' tempat' : 'Belum dipakai' }}</p>
                        </div>
                        @if ($this->canManage())
                            <x-icon-button icon="trash" :label="'Hapus '.$item->name" tone="danger" @click="$dispatch('open-modal', 'confirm-delete')" wire:click="confirmDelete({{ $item->id }})" />
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        @if ($media->hasPages())
            <div class="bg-slate-900/80 rounded-xl border border-slate-800/80">
                <x-table.pagination :paginator="$media" />
            </div>
        @endif
    @endif

    <x-confirm-delete-modal title="Hapus gambar ini?" description="Berkasnya dihapus permanen. Gambar yang masih dipakai tidak bisa dihapus." />
</div>
