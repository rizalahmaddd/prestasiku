@php
    use App\Livewire\Content\Analytics;
    use App\Models\SiteEvent;
    use App\Support\Site\AnalyticsReport;

    $number = fn (int|float $value, int $decimals = 0) => number_format($value, $decimals, ',', '.');
    $rangeLabel = $report->from->translatedFormat('j M Y').' – '.$report->to->translatedFormat('j M Y');
    $deviceLabels = ['mobile' => 'HP / Smartphone', 'tablet' => 'Tablet', 'desktop' => 'Laptop / Komputer'];
    $hours = collect($report->hours());
    $peakHour = $hours->sortByDesc('value')->first();
    $maxHour = max(1, (int) $hours->max('value'));
    $sections = $report->topEvents('section', 4);
    $totalInteractions = $events['whatsapp'] + $events['email'] + ($events['message'] ?? 0) + $events['resume'];

    $locations = $report->topEvents('location', 4);
    $campaigns = $report->breakdown('campaign', 5, skipEmpty: true);
    $cities = $report->breakdown('city', 6, skipEmpty: true);
    $deviceBreakdown = $report->breakdown('device')->map(fn ($row) => ['label' => $deviceLabels[$row['label']] ?? $row['label'], 'value' => $row['value']]);

    $peakSubtitle = ($peakHour && ($peakHour['value'] ?? 0) > 0)
        ? __('Paling ramai sekitar pukul :hour.00 (:val kunjungan)', ['hour' => $peakHour['label'], 'val' => $peakHour['value']])
        : __('Distribusi waktu pembacaan website');

    $peakAriaLabel = $peakHour
        ? __('Jumlah kunjungan per jam. Paling ramai pukul :hour.00 dengan :val kunjungan.', ['hour' => $peakHour['label'], 'val' => $peakHour['value']])
        : __('Jumlah kunjungan per jam.');
@endphp

<div class="space-y-5 sm:space-y-6">
    {{-- 1. HEADER & FILTER WAKTU --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
        <div class="space-y-1 max-w-2xl">
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ __('Statistik Pengunjung') }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                {{ __('Pantau siapa saja yang membuka portofolio, dari mana mereka datang, dan konten apa yang paling diminati. Kunjungan Anda saat login ke dashboard tidak ikut dihitung.') }}
            </p>
        </div>
        <x-segmented class="w-full sm:w-fit shrink-0" aria-label="{{ __('Rentang waktu') }}">
            @foreach (Analytics::RANGES as $value => $label)
                <x-tab-button size="sm" :active="$days === $value" wire:click="$set('days', {{ $value }})" class="flex-1 sm:flex-none">{{ $label }}</x-tab-button>
            @endforeach
        </x-segmented>
    </div>

    {{-- 2. METRIK KUNCI (KPI CARDS) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <x-dashboard.stat title="{{ __('Pengunjung') }}" :value="$number($totals['visitors'])" icon="users" tone="emerald"
            :trend="$totals['visitors_change']" :trend-label="'Dibanding '.$days.' hari sebelumnya'" :subtitle="$totals['today'].' hari ini'" />

        <x-dashboard.stat title="{{ __('Halaman dibuka') }}" :value="$number($totals['views'])" icon="eye"
            :trend="$totals['views_change']" :trend-label="'Dibanding '.$days.' hari sebelumnya'"
            :subtitle="$totals['visitors'] > 0 ? $number($totals['views'] / $totals['visitors'], 1).' per orang' : 'Termasuk kunjungan ulang'" />

        <x-dashboard.stat title="{{ __('Rata-rata per hari') }}" :value="$number($totals['per_day'], 1)" icon="calendar-days"
            subtitle="{{ __('Pengunjung') }}" />

        <x-dashboard.stat title="{{ __('Menghubungi') }}" :value="$number($totalInteractions)" icon="message-circle"
            :tone="$totalInteractions > 0 ? 'emerald' : 'slate'"
            :subtitle="$events['whatsapp'].' WhatsApp, '.($events['message'] ?? 0).' pesan, '.$events['email'].' email'" />
    </div>

    @if ($isEmpty)
        {{-- EMPTY STATE SAAT BELUM ADA TRAFIK --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden p-6 sm:p-10 shadow-sm dark:shadow-none">
            <x-empty-state icon="chart-no-axes-combined" title="{{ __('Belum ada kunjungan pada rentang ini') }}"
                description="{{ __('Data muncul begitu ada orang yang membuka website. Bagikan tautan berpelacak di bawah supaya terlihat dari mana pengunjung datang.') }}" />
        </div>
    @else
        {{-- 3. TITIK FOKUS UTAMA (DESIGN.md): PANEL TREN PENGUNJUNG --}}
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-6 md:p-7 shadow-sm dark:shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                <div class="space-y-0.5">
                    <h2 class="font-extrabold text-base sm:text-lg text-slate-900 dark:text-slate-100 tracking-tight">
                        {{ __('Pengunjung :unit', ['unit' => $report->seriesUnit()]) }}
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        {{ __('Dinamika grafik kunjungan harian selama periode :range', ['range' => $rangeLabel]) }}
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 font-semibold tabular-nums">
                        {{ $number($totals['visitors']) }} {{ __('total pengunjung unik') }}
                    </span>
                </div>
            </div>
            <x-bar-chart :data="$report->series()" color="emerald" />
        </section>

        {{-- 4. KELOMPOK 1: KONTEN POPULER & MINAT PENGUNJUNG --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 items-start">
            {{-- PRESTASI TERBANYAK DIBUKA --}}
            <x-dashboard.panel title="{{ __('Prestasi paling banyak dibuka') }}" icon="trophy" :count="$events['achievement'] ?: null"
                :href="route('content.achievements')" link-label="{{ __('Kelola') }}" subtitle="{{ __('Pencapaian yang menarik minat pembaca') }}">
                <x-content.bar-list :items="$report->topEvents('achievement')" empty="{{ __('Belum ada prestasi yang dibuka detailnya.') }}" />
            </x-dashboard.panel>

            {{-- FOTO, LOKASI & UNDUHAN RESUME --}}
            <x-dashboard.panel title="{{ __('Foto dan lokasi yang dilihat') }}" icon="camera" :count="($events['photo'] + $events['location']) ?: null"
                subtitle="{{ __('Dokumentasi visual & venue kegiatan') }}">
                <x-content.bar-list :items="$report->topEvents('photo', 4)" empty="{{ __('Belum ada foto galeri yang dibuka.') }}" />
                @if ($locations->isNotEmpty())
                    <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-slate-200">{{ __('Lokasi di peta') }}</h4>
                        <x-content.bar-list :items="$locations" />
                    </div>
                @endif
                <div class="pt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between gap-2 border-t border-slate-100 dark:border-slate-800 mt-2">
                    <span>{{ __('Resume PDF diunduh :count kali pada rentang ini.', ['count' => $events['resume']]) }}</span>
                    @if ($events['resume'] > 0)
                        <span class="font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $events['resume'] }}x</span>
                    @endif
                </div>
            </x-dashboard.panel>
        </div>

        {{-- 5. KELOMPOK 2: ASAL PENGUNJUNG & SALURAN KAMPANYE --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 items-start">
            {{-- DARI MANA PENGUNJUNG DATANG --}}
            <x-dashboard.panel title="{{ __('Dari mana pengunjung datang') }}" icon="signpost" subtitle="{{ __('Sumber kunjungan langsung & rujukan') }}">
                <x-content.bar-list :items="$report->breakdown('source')" :total="$totals['visitors']" />
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed pt-2 border-t border-slate-100 dark:border-slate-800 mt-2">
                    {{ __('"Langsung" berarti alamat diketik sendiri atau dibuka dari aplikasi yang tidak menyertakan rujukan (seperti chat). Gunakan tautan berpelacak di bawah agar tercatat spesifik.') }}
                </p>
            </x-dashboard.panel>

            {{-- JENIS SALURAN & KAMPANYE --}}
            <x-dashboard.panel title="{{ __('Jenis saluran') }}" icon="split" subtitle="{{ __('Pengelompokan kanal trafik') }}">
                <x-content.bar-list :items="$report->channels()" :total="$totals['visitors']" />
                @if ($campaigns->isNotEmpty())
                    <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-slate-200">{{ __('Kampanye (utm_campaign)') }}</h4>
                        <x-content.bar-list :items="$campaigns" />
                    </div>
                @endif
            </x-dashboard.panel>
        </div>

        {{-- 6. KELOMPOK 3: LOKASI GEOGRAFIS & PERANGKAT --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 items-start">
            {{-- LOKASI PENGUNJUNG --}}
            <x-dashboard.panel title="{{ __('Lokasi pengunjung') }}" icon="map-pinned" subtitle="{{ __('Negara dan kota pembaca') }}">
                @if ($report->hasLocationData())
                    <x-content.bar-list :items="$report->countries()" :total="$totals['visitors']" />
                    @if ($cities->isNotEmpty())
                        <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                            <h4 class="text-xs font-bold text-slate-900 dark:text-slate-200">{{ __('Kota teratas') }}</h4>
                            <x-content.bar-list :items="$cities" />
                        </div>
                    @endif
                @else
                    <div class="py-3 px-3.5 rounded-lg bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ __('Lokasi belum tersedia. Data negara dan kota dibaca dari Cloudflare: begitu domain website dilewatkan lewat Cloudflare (dan opsi "Add visitor location headers" dinyalakan untuk data kota), bagian ini terisi otomatis.') }}
                        </p>
                    </div>
                @endif
            </x-dashboard.panel>

            {{-- PERANGKAT --}}
            <x-dashboard.panel title="{{ __('Perangkat') }}" icon="smartphone" subtitle="{{ __('Perangkat & peramban yang digunakan') }}">
                <x-content.bar-list :items="$deviceBreakdown" :total="$totals['visitors']" />
                <div class="grid grid-cols-2 gap-4 pt-3 mt-2 border-t border-slate-100 dark:border-slate-800">
                    <div class="space-y-2 min-w-0">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-slate-200">{{ __('Browser') }}</h4>
                        <x-content.bar-list :items="$report->breakdown('browser', 4)" />
                    </div>
                    <div class="space-y-2 min-w-0">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-slate-200">{{ __('Sistem operasi') }}</h4>
                        <x-content.bar-list :items="$report->breakdown('os', 4)" />
                    </div>
                </div>
            </x-dashboard.panel>
        </div>

        {{-- 7. KELOMPOK 4: WAKTU RAMAI & KEDALAMAN MEMBACA --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 items-start">
            {{-- JAM RAMAI DENGAN KONTRAS TINGGI DI LIGHT & DARK MODE --}}
            <x-dashboard.panel title="{{ __('Jam ramai') }}" icon="clock" :subtitle="$peakSubtitle">
                <div class="flex items-end gap-[3px] h-28 pt-2" role="img" aria-label="{{ $peakAriaLabel }}">
                    @foreach ($hours as $hour)
                        <div class="flex-1 h-full flex items-end group" title="{{ $hour['label'] }}.00: {{ $hour['value'] }} kunjungan">
                            <div @class([
                                'w-full rounded-sm transition-colors',
                                'bg-emerald-600 dark:bg-emerald-500 shadow-sm' => ($hour['value'] === ($peakHour['value'] ?? null) && $hour['value'] > 0),
                                'bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700' => ! ($hour['value'] === ($peakHour['value'] ?? null) && $hour['value'] > 0),
                            ]) style="height: {{ $hour['value'] > 0 ? max(6, round($hour['value'] / $maxHour * 100)) : 3 }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between text-[11px] text-slate-500 dark:text-slate-400 tabular-nums font-mono pt-1" aria-hidden="true">
                    <span>00</span><span>06</span><span>12</span><span>18</span><span>23</span>
                </div>
            </x-dashboard.panel>

            {{-- KEDALAMAN BACAAN --}}
            <x-dashboard.panel title="{{ __('Seberapa jauh halaman dibaca') }}" icon="arrow-down-to-line" subtitle="{{ __('Tingkat gulir pembaca hingga ke bagian akhir') }}">
                <x-content.bar-list :items="$sections" :total="$totals['visitors']" empty="{{ __('Belum ada pengunjung yang menggulir halaman.') }}" />
            </x-dashboard.panel>
        </div>

        {{-- 8. TABEL KUNJUNGAN TERBARU (SEMPURNA DI LIGHT & DARK MODE) --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm dark:shadow-none">
            <div class="px-4 py-3 sm:px-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-slate-100 tracking-tight">{{ __('Kunjungan Terbaru') }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('12 catatan log interaksi terakhir di website') }}</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-2.5 sm:px-5">{{ __('Waktu') }}</th>
                            <th class="px-4 py-2.5 sm:px-5">{{ __('Sumber / Kanal') }}</th>
                            <th class="px-4 py-2.5 sm:px-5">{{ __('Lokasi') }}</th>
                            <th class="px-4 py-2.5 sm:px-5">{{ __('Perangkat & Peramban') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @foreach ($report->recent() as $visit)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors" wire:key="visit-{{ $visit->id }}">
                                <td class="px-4 py-3 sm:px-5 text-slate-600 dark:text-slate-300 whitespace-nowrap tabular-nums">
                                    {{ $visit->created_at->translatedFormat('j M, H.i') }}
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $visit->source }}</div>
                                    @if ($visit->campaign)
                                        <span class="inline-block text-[10px] font-medium text-emerald-600 dark:text-emerald-400">{{ $visit->campaign }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 sm:px-5 text-slate-600 dark:text-slate-400">
                                    {{ collect([$visit->city, $visit->country ? AnalyticsReport::countryName($visit->country) : null])->filter()->implode(', ') ?: '-' }}
                                </td>
                                <td class="px-4 py-3 sm:px-5 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ $deviceLabels[$visit->device] ?? $visit->device }} &middot; {{ $visit->browser }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- 9. PEMBUAT TAUTAN BERPELACAK (SMART SHARE LINKS) --}}
    <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 sm:p-5 space-y-3.5 shadow-sm dark:shadow-none" x-data="{ copied: null }">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
            <h2 class="font-bold text-sm sm:text-base text-slate-900 dark:text-slate-100 tracking-tight">
                {{ __('Tautan Berpelacak (Smart Share)') }}
            </h2>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5 leading-relaxed max-w-3xl">
                {{ __('Bagikan tautan yang telah dipasangi parameter pelacak saat membagikan portofolio ke media sosial, pesan WhatsApp, atau bio akun agar asal kunjungan terdata secara akurat.') }}
            </p>
        </div>

        <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
            @foreach ($shareLinks as $link)
                <li class="flex items-center gap-2 p-2.5 pl-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 transition-colors">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-900 dark:text-slate-200">{{ $link['label'] }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate font-mono mt-0.5">{{ $link['url'] }}</p>
                    </div>
                    <x-secondary-button type="button" size="xs" class="shrink-0"
                        @click="navigator.clipboard.writeText(@js($link['url'])).then(() => { copied = @js($link['label']); setTimeout(() => copied = null, 2000) })">
                        <span x-text="copied === @js($link['label']) ? 'Tersalin ✓' : 'Salin'">{{ __('Salin') }}</span>
                    </x-secondary-button>
                </li>
            @endforeach
        </ul>

        <div class="pt-2 text-[11px] text-slate-500 dark:text-slate-400 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 dark:border-slate-800">
            <span>{{ __('Statistik disimpan :months bulan terakhir secara anonim, tanpa cookie pelacak dan tanpa menyimpan IP pengunjung.', ['months' => \App\Models\SiteVisit::RETENTION_MONTHS]) }}</span>
            <span class="inline-flex items-center gap-1 font-medium text-emerald-600 dark:text-emerald-400">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                {{ __('Privasi Terjaga') }}
            </span>
        </div>
    </section>
</div>
