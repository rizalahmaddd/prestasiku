{{-- Dipakai lewat @include dari view komponen yang memakai trait WithMediaPicker.
     Variabel: $field (nama properti), $label, opsional $hint, $round. --}}
@php
    $pickerMedia = $this->mediaFor($field);
    $pickerSlug = \App\Livewire\Concerns\WithMediaPicker::mediaSlug($field);
    $pickerOpen = $mediaPickerField === $field;
    $pickerRound = $round ?? false;
@endphp

<div wire:key="image-picker-{{ $pickerSlug }}">
    <x-input-label for="upload-{{ $pickerSlug }}" :value="$label" />

    <div class="mt-1 rounded-lg border border-slate-800 bg-slate-950/40 p-3 space-y-3">
        <div class="flex flex-col sm:flex-row gap-3">
            <div @class([
                'shrink-0 overflow-hidden bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400',
                'w-24 h-24 rounded-full self-center sm:self-start' => $pickerRound,
                'w-full sm:w-40 aspect-[4/3] rounded-lg' => ! $pickerRound,
            ])>
                @if ($pickerMedia)
                    <img src="{{ $pickerMedia->url() }}" alt="{{ $pickerMedia->name }}" class="w-full h-full object-cover">
                @else
                    <span class="flex flex-col items-center gap-1 text-[11px]">
                        <i data-lucide="image" class="w-5 h-5"></i>
                        Belum ada foto
                    </span>
                @endif
            </div>

            <div class="min-w-0 flex-1 space-y-2">
                <x-file-input wire:model="mediaUploads.{{ $pickerSlug }}" id="upload-{{ $pickerSlug }}" accept="image/png,image/jpeg,image/webp"
                    icon="image-up" :label="$pickerMedia ? 'Unggah foto pengganti' : 'Unggah foto'" hint="Klik atau seret foto ke sini. JPG, PNG, atau WebP, maks. 10 MB." />

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <x-text-button size="sm" tone="emerald" wire:click="toggleMediaLibrary('{{ $field }}')" aria-expanded="{{ $pickerOpen ? 'true' : 'false' }}">
                        <i data-lucide="images" class="w-3.5 h-3.5"></i>
                        {{ $pickerOpen ? 'Tutup pustaka' : 'Pilih dari pustaka' }}
                    </x-text-button>
                    @if ($pickerMedia)
                        <x-text-button size="sm" tone="rose" wire:click="clearMedia('{{ $field }}')">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            Lepas foto
                        </x-text-button>
                        <span class="text-[11px] text-slate-400 truncate">{{ $pickerMedia->name }}</span>
                    @endif
                </div>
            </div>
        </div>

        @if ($pickerOpen)
            @php($pickerLibrary = $this->mediaLibrary())
            <div class="pt-3 border-t border-slate-800 space-y-3">
                <x-search-input variant="form" wire:model.live.debounce.300ms="mediaPickerSearch" placeholder="Cari nama foto..." aria-label="Cari di pustaka media" />

                @if ($pickerLibrary->isEmpty())
                    <p class="text-xs text-slate-400 py-4 text-center">
                        {{ trim($mediaPickerSearch) !== '' ? 'Tidak ada foto dengan nama itu.' : 'Pustaka masih kosong. Unggah foto pertama lewat kotak di atas.' }}
                    </p>
                @else
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 max-h-64 overflow-y-auto custom-scrollbar pr-1">
                        @foreach ($pickerLibrary as $libraryItem)
                            <button type="button" wire:key="library-{{ $pickerSlug }}-{{ $libraryItem->id }}" wire:click="chooseMedia('{{ $field }}', {{ $libraryItem->id }})"
                                title="{{ $libraryItem->name }}" aria-label="Pakai foto {{ $libraryItem->name }}"
                                @class([
                                    'relative aspect-square rounded-lg overflow-hidden border-2 bg-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 transition',
                                    'border-emerald-500' => $pickerMedia?->id === $libraryItem->id,
                                    'border-transparent hover:border-slate-600' => $pickerMedia?->id !== $libraryItem->id,
                                ])>
                                <img src="{{ $libraryItem->url() }}" alt="" loading="lazy" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-slate-400">Menampilkan {{ $pickerLibrary->count() }} foto terbaru. Pakai pencarian untuk foto yang lebih lama.</p>
                @endif
            </div>
        @endif
    </div>

    @if (! empty($hint))
        <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">{{ $hint }}</p>
    @endif
    <x-input-error :messages="$errors->get('mediaUploads.'.$pickerSlug)" class="mt-1.5" />
    <x-input-error :messages="$errors->get($field)" class="mt-1.5" />
</div>
