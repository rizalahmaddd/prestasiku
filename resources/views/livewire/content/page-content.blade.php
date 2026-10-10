<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <p class="text-xs sm:text-sm text-slate-400 max-w-3xl">
            Teks dan gambar halaman depan yang bukan berupa daftar. Perubahan langsung tampil di website begitu disimpan.
        </p>
        <a href="{{ route('home') }}" target="_blank" rel="noopener"
            class="inline-flex items-center justify-center gap-2 min-h-[44px] sm:min-h-[38px] px-4 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:text-slate-100 hover:bg-slate-800/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 shrink-0">
            <i data-lucide="external-link" class="w-4 h-4"></i>
            Lihat website
        </a>
    </div>

    <div role="tablist" aria-label="{{ __('Bagian konten') }}" class="flex gap-1 p-1 rounded-xl bg-slate-900 border border-slate-800 overflow-x-auto">
        @foreach ($tabs as $key => $item)
            <button type="button" role="tab" wire:click="$set('tab', '{{ $key }}')"
                aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                @class([
                    'flex-1 min-w-[8.5rem] flex items-center gap-2.5 px-3 py-2 rounded-lg text-left min-h-[44px] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500',
                    'bg-slate-800 text-slate-100' => $tab === $key,
                    'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' => $tab !== $key,
                ])>
                <i data-lucide="{{ $item['icon'] }}" @class(['w-4 h-4 shrink-0', 'text-emerald-400' => $tab === $key])></i>
                <span class="min-w-0">
                    <span class="block text-xs font-semibold">{{ $item['label'] }}</span>
                    <span class="hidden sm:block text-[11px] text-slate-400 truncate">{{ $item['hint'] }}</span>
                </span>
            </button>
        @endforeach
    </div>

    <form wire:submit="save" wire:key="tab-{{ $tab }}" class="bg-slate-900 border border-slate-800 rounded-xl">
        <fieldset @disabled(! $this->canManage()) class="p-4 sm:p-6 space-y-5">
            @foreach ($sections as $section)
                <x-content.section :title="$section['title']" :description="$section['description'] ?? null">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @foreach ($section['fields'] as $key => $field)
                            @php
                                $type = $field['type'] ?? 'text';
                                $id = 'field-'.$key;
                                $label = $field['label'].(($field['required'] ?? false) ? ' *' : '');
                            @endphp
                            <div @class(['sm:col-span-2' => ! ($field['half'] ?? false)]) wire:key="field-{{ $key }}">
                                @if ($type === 'image')
                                    @include('livewire.content.partials.image-picker', ['field' => 'values.'.$key, 'label' => $label, 'hint' => $field['hint'] ?? null])
                                @else
                                    <x-content.field :for="$id" :label="$label" :hint="$field['hint'] ?? null" :error="'values.'.$key">
                                        @if ($type === 'textarea')
                                            <x-textarea wire:model="values.{{ $key }}" :id="$id" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}" />
                                        @else
                                            <x-text-input wire:model="values.{{ $key }}" :id="$id" class="w-full"
                                                type="{{ match ($type) { 'date' => 'date', 'email' => 'email', 'phone' => 'tel', default => 'text' } }}"
                                                placeholder="{{ $field['placeholder'] ?? '' }}" />
                                        @endif
                                    </x-content.field>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if ($tab === 'kontak' && $loop->last)
                        <div>
                            <x-input-label for="resume" value="Berkas resume (PDF)" />
                            <div class="mt-1 flex flex-col sm:flex-row sm:items-center gap-3">
                                <div class="min-w-0 flex-1">
                                    <x-file-input wire:model="resume" id="resume" accept="application/pdf" :file="$resume" icon="file-up"
                                        :label="$resumePath ? 'Ganti berkas resume' : 'Unggah berkas resume'" hint="PDF, maks. 10 MB. Tersimpan setelah menekan Simpan." />
                                </div>
                                @if ($resumePath && ! $resume)
                                    <div class="flex items-center gap-3 shrink-0">
                                        <a href="{{ route('resume') }}" target="_blank" rel="noopener" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:underline underline-offset-2">Lihat berkas saat ini</a>
                                        <x-text-button size="sm" tone="rose" wire:click="removeResume" wire:confirm="Hapus berkas resume? Tombol Resume PDF akan hilang dari website.">Hapus</x-text-button>
                                    </div>
                                @endif
                            </div>
                            <x-input-error :messages="$errors->get('resume')" class="mt-1.5" />
                        </div>
                    @endif
                </x-content.section>
            @endforeach
        </fieldset>

        @if ($this->canManage())
            <div class="px-4 sm:px-6 py-3 border-t border-slate-800 flex justify-end">
                <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="save,resume,mediaUploads" class="min-h-[44px] px-6">
                    <x-loading-label target="save" loading="Menyimpan...">Simpan {{ $tabs[$tab]['label'] }}</x-loading-label>
                </x-primary-button>
            </div>
        @endif
    </form>
</div>
