@props(['name', 'tones' => \App\Support\Site\Palette::TONES])

<div role="radiogroup" class="flex flex-wrap gap-1.5">
    @foreach ($tones as $tone => $definition)
        <label class="cursor-pointer">
            <input type="radio" name="{{ $name }}" value="{{ $tone }}" class="sr-only peer" {{ $attributes->whereStartsWith('wire:model') }}>
            <span class="inline-flex items-center gap-2 min-h-[44px] sm:min-h-[36px] px-2.5 rounded-lg border border-slate-800 bg-slate-950 text-xs font-medium text-slate-300 hover:text-slate-100 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-400 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-500/40 transition">
                <span class="w-3.5 h-3.5 rounded-full border border-white/20 shrink-0" style="background-color: {{ $definition['swatch'] }}"></span>
                {{ $definition['label'] }}
            </span>
        </label>
    @endforeach
</div>
