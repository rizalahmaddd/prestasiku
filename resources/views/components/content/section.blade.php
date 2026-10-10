@props(['title', 'description' => null])

<section {{ $attributes->class(['space-y-3.5 bg-slate-950/40 p-4 rounded-xl border border-slate-800/60']) }}>
    <div class="pb-2 border-b border-slate-800/80">
        <h4 class="text-xs font-bold text-slate-200">{{ $title }}</h4>
        @if ($description)
            <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">{{ $description }}</p>
        @endif
    </div>

    {{ $slot }}
</section>
