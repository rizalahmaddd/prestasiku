@php
    $previewLogoUrl = $logo && ! $errors->has('logo') ? $logo->temporaryUrl() : null;
@endphp

<div class="space-y-4 sm:space-y-6">
    <p class="text-xs sm:text-sm text-slate-400 max-w-3xl">
        Nama dan logo dashboard ini. Identitas yang tampil di website publik diatur di menu Konten Halaman.
    </p>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start">
            <form wire:submit="saveBranding" class="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-xl">
                <div class="p-4 sm:p-6 space-y-5">
                    <div>
                        <h2 class="font-bold text-sm text-slate-100">Branding Aplikasi</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Tampil di sidebar, halaman login, dan judul tab browser.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="app_name" value="Nama Aplikasi *" />
                            <x-text-input wire:model.live.debounce.150ms="app_name" id="app_name" type="text" class="w-full mt-1" placeholder="Nama singkat dashboard" />
                            <x-input-error :messages="$errors->get('app_name')" class="mt-1.5" />
                        </div>
                        <div>
                            <x-input-label for="app_tagline" value="Tagline" />
                            <x-text-input wire:model.live.debounce.150ms="app_tagline" id="app_tagline" type="text" class="w-full mt-1" placeholder="Keterangan singkat di bawah nama" />
                            <x-input-error :messages="$errors->get('app_tagline')" class="mt-1.5" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="logo" value="Logo" />
                        <div class="mt-1 flex flex-col sm:flex-row sm:items-center gap-3 p-3 rounded-lg border border-slate-800">
                            <div class="shrink-0">
                                @if ($previewLogoUrl)
                                    <img src="{{ $previewLogoUrl }}" alt="" class="w-14 h-14 rounded-lg object-contain bg-white p-1">
                                @else
                                    <x-brand-mark size="w-8 h-8" padding="p-3" radius="rounded-lg" />
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <x-file-input wire:model="logo" id="logo" accept="image/png,image/jpeg,image/webp" :file="$logo" icon="image-up" label="Pilih logo" hint="PNG, JPG, atau WebP, maks. 1 MB. Bentuk persegi paling rapi." />
                            </div>
                            @if (\App\Support\Branding::logoUrl() && ! $logo)
                                <button type="button" wire:click="removeLogo"
                                    class="shrink-0 self-start sm:self-center text-xs font-semibold text-rose-400 hover:text-rose-300 min-h-[44px] px-2 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                                    Hapus logo
                                </button>
                            @endif
                        </div>
                        <x-input-error :messages="$errors->get('logo')" class="mt-1.5" />
                    </div>
                </div>

                <div class="px-4 sm:px-6 py-3 border-t border-slate-800 flex justify-end">
                    <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="saveBranding,logo" class="min-h-[44px] px-6">
                        <span wire:loading.remove wire:target="saveBranding">Simpan Branding</span>
                        <span wire:loading wire:target="saveBranding">Menyimpan...</span>
                    </x-primary-button>
                </div>
            </form>

            {{-- Titik fokus tab ini (DESIGN.md): pratinjau tempat branding benar-benar muncul. --}}
            <aside class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-xl p-4 sm:p-6 space-y-4 shadow-xl">
                <h2 class="font-bold text-sm text-slate-100">Pratinjau</h2>

                <div class="space-y-1.5">
                    <p class="text-[11px] font-semibold text-slate-400">Tab browser</p>
                    <div class="flex items-center gap-2 px-3 py-2 rounded-t-lg bg-slate-800 border border-slate-700 text-xs text-slate-200 max-w-xs">
                        @if ($favicon = $previewLogoUrl ?? \App\Support\Branding::logoUrl())
                            <img src="{{ $favicon }}" alt="" class="w-4 h-4 object-contain shrink-0">
                        @else
                            <i data-lucide="globe" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        @endif
                        <span class="truncate">Dashboard - {{ $app_name ?: 'Nama Aplikasi' }}</span>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <p class="text-[11px] font-semibold text-slate-400">Sidebar</p>
                    <div class="flex items-center gap-2.5 p-3 rounded-lg bg-slate-950 border border-slate-800">
                        @if ($previewLogoUrl)
                            <img src="{{ $previewLogoUrl }}" alt="" class="w-8 h-8 rounded-lg object-contain bg-white p-0.5 shrink-0">
                        @else
                            <x-brand-mark size="w-5 h-5" padding="p-1.5" radius="rounded-lg" class="shrink-0" />
                        @endif
                        <div class="min-w-0">
                            <div class="font-bold text-slate-100 text-sm leading-tight truncate">{{ $app_name ?: 'Nama Aplikasi' }}</div>
                            @if ($app_tagline)
                                <div class="text-[10px] text-slate-400 font-medium truncate">{{ $app_tagline }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <p class="text-[11px] font-semibold text-slate-400">Halaman login</p>
                    <div class="p-4 rounded-lg bg-slate-950 border border-slate-800 flex flex-col items-center text-center gap-2">
                        @if ($previewLogoUrl)
                            <img src="{{ $previewLogoUrl }}" alt="" class="w-12 h-12 rounded-xl object-contain bg-white p-1">
                        @else
                            <x-brand-mark />
                        @endif
                        <div>
                            <div class="font-bold text-slate-100 text-sm">{{ $app_name ?: 'Nama Aplikasi' }}</div>
                            @if ($app_tagline)
                                <div class="text-[11px] text-slate-400">{{ $app_tagline }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </aside>
        </div>
</div>
