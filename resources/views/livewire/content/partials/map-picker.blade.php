{{-- Pemilih titik peta. Properti Livewire: lat, lng, zoom, color; "address" ikut diisi dari
     hasil pencarian kalau masih kosong. Logikanya di resources/js/map-picker.js. --}}
<div x-data="mapPicker({ lat: $wire.entangle('lat'), lng: $wire.entangle('lng'), zoom: $wire.entangle('zoom'), color: $wire.entangle('color'), addressField: 'address' })" class="space-y-3">
    <div class="flex gap-2">
        <div class="relative flex-1 min-w-0">
            <x-text-input x-model="query" @keydown.enter.prevent="search()" type="search" class="w-full" placeholder="Cari nama tempat atau alamat, lalu tekan Enter" aria-label="Cari tempat di peta" />
        </div>
        <x-secondary-button type="button" size="sm" @click="search()" x-bind:disabled="searching || query.trim().length < 3">
            <i data-lucide="search" class="w-4 h-4"></i>
            <span x-text="searching ? 'Mencari...' : 'Cari'">Cari</span>
        </x-secondary-button>
    </div>

    <p x-show="message" x-text="message" class="text-[11px] text-amber-300" style="display: none;"></p>

    <ul x-show="results.length" class="rounded-lg border border-slate-800 divide-y divide-slate-800 overflow-hidden" style="display: none;">
        <template x-for="result in results" :key="result.place_id">
            <li>
                <button type="button" @click="pick(result)" class="w-full flex items-start gap-2 px-3 py-2 min-h-[44px] text-left text-xs text-slate-300 hover:bg-slate-800/60 focus:outline-none focus-visible:bg-slate-800/60">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 mt-0.5 shrink-0 text-emerald-400"></i>
                    <span x-text="result.display_name"></span>
                </button>
            </li>
        </template>
    </ul>

    <div class="relative rounded-lg overflow-hidden border border-slate-800 bg-slate-900">
        <div wire:ignore x-ref="map" class="w-full h-64 sm:h-80 z-0" role="application" aria-label="Peta untuk memilih titik lokasi"></div>
        <p x-show="failed" class="absolute inset-0 flex items-center justify-center p-4 text-center text-xs text-slate-400" style="display: none;">
            Peta tidak bisa dimuat. Periksa koneksi internet, atau isi garis lintang dan bujur di bawah secara manual.
        </p>
    </div>

    <p class="text-[11px] text-slate-400 leading-relaxed">
        Klik peta untuk menaruh penanda, atau geser penandanya ke titik yang tepat. Tingkat zoom peta saat ini ikut tersimpan sebagai tampilan awal di website.
    </p>

    <div class="grid grid-cols-3 gap-3">
        <div>
            <x-input-label for="lat" value="Garis lintang" />
            <x-text-input x-model.number.lazy="lat" id="lat" type="number" step="any" class="w-full mt-1 font-mono" placeholder="-6.193262" />
        </div>
        <div>
            <x-input-label for="lng" value="Garis bujur" />
            <x-text-input x-model.number.lazy="lng" id="lng" type="number" step="any" class="w-full mt-1 font-mono" placeholder="106.883713" />
        </div>
        <div>
            <x-input-label for="zoom" value="Zoom" />
            <x-text-input x-model.number.lazy="zoom" id="zoom" type="number" min="3" max="18" class="w-full mt-1 font-mono" />
        </div>
    </div>
    <x-input-error :messages="array_unique([...$errors->get('lat'), ...$errors->get('lng')])" class="mt-1.5" />
    <x-input-error :messages="$errors->get('zoom')" class="mt-1.5" />
</div>
