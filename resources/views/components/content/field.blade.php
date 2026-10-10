@props(['for', 'label', 'hint' => null, 'error' => null])

<div {{ $attributes }}>
    <x-input-label :for="$for" :value="$label" />
    <div class="mt-1">
        {{ $slot }}
    </div>
    @if ($hint)
        <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">{{ $hint }}</p>
    @endif
    <x-input-error :messages="$errors->get($error ?? $for)" class="mt-1.5" />
</div>
