@props(['items', 'total' => null, 'empty' => 'Belum ada data pada rentang ini.', 'unit' => 'pengunjung'])

@php
    $items = collect($items);
    $max = max(1, (int) $items->max('value'));
    $base = $total ?: null;
@endphp

@if ($items->isEmpty())
    <p class="text-xs text-slate-400 py-3">{{ $empty }}</p>
@else
    <ul class="space-y-2.5">
        @foreach ($items as $item)
            <li>
                <div class="flex items-baseline justify-between gap-3 text-xs">
                    <span class="min-w-0 truncate font-medium text-slate-200" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                    <span class="shrink-0 tabular-nums text-slate-400">
                        <span class="font-semibold text-slate-100">{{ number_format($item['value'], 0, ',', '.') }}</span>
                        <span class="sr-only">{{ $unit }}</span>
                        @if ($base)
                            &middot; {{ number_format($item['value'] / $base * 100, 0) }}%
                        @endif
                    </span>
                </div>
                <div class="mt-1 h-1.5 rounded-full bg-slate-800 overflow-hidden" aria-hidden="true">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ max(2, round($item['value'] / $max * 100)) }}%"></div>
                </div>
            </li>
        @endforeach
    </ul>
@endif
