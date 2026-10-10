@props(['icons', 'name', 'optional' => false])

{{-- Radio biasa supaya bisa dipilih dengan keyboard; tampilannya saja yang berupa kotak ikon. --}}
<div role="radiogroup" class="flex flex-wrap gap-1.5">
    @if ($optional)
        <label class="cursor-pointer" title="Tanpa ikon">
            <input type="radio" name="{{ $name }}" value="" class="sr-only peer" {{ $attributes->whereStartsWith('wire:model') }}>
            <span class="inline-flex items-center justify-center w-11 h-11 sm:w-9 sm:h-9 rounded-lg border border-slate-800 bg-slate-950 text-slate-400 hover:text-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-400 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-500/40 transition">
                <i data-lucide="ban" class="w-4 h-4"></i>
                <span class="sr-only">Tanpa ikon</span>
            </span>
        </label>
    @endif
    @foreach ($icons as $icon => $iconLabel)
        <label class="cursor-pointer" title="{{ $iconLabel }}">
            <input type="radio" name="{{ $name }}" value="{{ $icon }}" class="sr-only peer" {{ $attributes->whereStartsWith('wire:model') }}>
            <span class="inline-flex items-center justify-center w-11 h-11 sm:w-9 sm:h-9 rounded-lg border border-slate-800 bg-slate-950 text-slate-400 hover:text-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-400 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-500/40 transition">
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                <span class="sr-only">{{ $iconLabel }}</span>
            </span>
        </label>
    @endforeach
</div>
