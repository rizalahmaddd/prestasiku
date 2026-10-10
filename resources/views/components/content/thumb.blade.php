@props(['media' => null, 'icon' => 'image', 'round' => false])

<span {{ $attributes->class(['inline-flex items-center justify-center shrink-0 w-12 h-12 overflow-hidden bg-slate-950 border border-slate-800 text-slate-400', 'rounded-full' => $round, 'rounded-lg' => ! $round]) }}>
    @if ($media)
        <img src="{{ $media->url() }}" alt="" loading="lazy" class="w-full h-full object-cover">
    @else
        <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
    @endif
</span>
