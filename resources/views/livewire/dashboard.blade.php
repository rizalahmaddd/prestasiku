<div class="space-y-4 sm:space-y-6">
    {{-- 1. TITIK FOKUS UTAMA (DESIGN.md): Panel Eksekutif Profil & Status Publikasi Portofolio --}}
    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gradient-to-r dark:from-slate-900 dark:via-slate-900 dark:to-slate-800/90 border border-slate-200 dark:border-slate-800 p-5 sm:p-7 md:p-8 shadow-sm dark:shadow-xl">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3 min-w-0 max-w-3xl">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ __('Portofolio Aktif') }}
                    </span>
                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                </div>

                <div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        {{ __('Selamat datang, :name', ['name' => auth()->user()->name]) }}
                    </h1>
                    @if (auth()->user()->getRoleNames()->isEmpty())
                        <p class="mt-2 text-xs md:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ __('Akun Anda belum punya peran. Menu kerja muncul setelah Superadmin menetapkan peran untuk akun ini.') }}
                        </p>
                    @else
                        <p class="mt-1 text-xs sm:text-sm text-slate-600 dark:text-slate-300 flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="font-bold text-slate-900 dark:text-slate-100">{{ $siteProfile['name'] }}</span>
                            <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                            <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $siteProfile['grade'] }}</span>
                            <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                            <span>{{ $siteProfile['school'] }}</span>
                            @if (! empty($siteProfile['tagline']))
                                <span class="hidden sm:inline text-slate-300 dark:text-slate-700">&bull;</span>
                                <span class="hidden sm:inline text-slate-500 dark:text-slate-400 italic">{{ $siteProfile['tagline'] }}</span>
                            @endif
                        </p>
                    @endif
                </div>

                {{-- Indikator Status & Alert Pesan --}}
                @if (! auth()->user()->getRoleNames()->isEmpty())
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        @if ($messagesSummary && $messagesSummary['new'] > 0)
                            <a href="{{ route('content.messages') }}" wire:navigate
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 hover:bg-amber-100 dark:hover:bg-amber-500/20 transition-colors">
                                <i data-lucide="bell-ring" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400"></i>
                                <span>{{ __(':count pesan baru menunggu tanggapan', ['count' => $messagesSummary['new']]) }}</span>
                            </a>
                        @elseif ($messagesSummary && $messagesSummary['open'] > 0)
                            <a href="{{ route('content.messages') }}" wire:navigate
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-sky-50 dark:bg-sky-500/10 text-sky-800 dark:text-sky-400 border border-sky-200 dark:border-sky-500/20 hover:bg-sky-100 dark:hover:bg-sky-500/20 transition-colors">
                                <i data-lucide="inbox" class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400"></i>
                                <span>{{ __(':count pesan sedang ditindaklanjuti', ['count' => $messagesSummary['open']]) }}</span>
                            </a>
                        @elseif ($messagesSummary)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                                <span>{{ __('Kotak masuk rapi') }}</span>
                            </span>
                        @endif

                        @if ($siteProfile['hasResume'])
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                <i data-lucide="file-check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                                <span>{{ __('Resume PDF Siap') }}</span>
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                @if ($siteProfile['hasResume'])
                    <a href="{{ route('resume') }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 min-h-[44px] px-4 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                        <i data-lucide="file-text" class="w-4 h-4 text-slate-500 dark:text-slate-400"></i>
                        {{ __('Resume PDF') }}
                    </a>
                @endif
                <a href="{{ route('home') }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 min-h-[44px] px-4 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 shadow-sm">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    {{ __('Lihat website') }}
                </a>
            </div>
        </div>
    </div>

    {{-- 2. METRIK KUNCI (KPI CARDS) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($stats as $stat)
            <x-dashboard.stat :title="$stat['title']" :value="$stat['value']" :icon="$stat['icon']" :tone="$stat['tone']"
                :trend="$stat['trend'] ?? null" :trend-label="$stat['trendLabel'] ?? null"
                :subtitle="$stat['subtitle']" :href="$stat['href']" />
        @endforeach
    </div>

    {{-- 3. AREA UTAMA: 2 KOLOM SEIMBANG --}}
    @if (! auth()->user()->getRoleNames()->isEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start">
            {{-- KOLOM KIRI (7/12): ARUS MASUK & DATA PRESTASI --}}
            <div class="lg:col-span-7 space-y-4 sm:space-y-6">
                {{-- WIDGET PESAN MASUK TERBARU --}}
                @if ($messagesSummary !== null)
                    <x-dashboard.panel :title="__('Pesan Masuk Terbaru')" icon="inbox"
                        :count="$messagesSummary['new'] > 0 ? (string) $messagesSummary['new'].' baru' : ($messagesSummary['open'] > 0 ? (string) $messagesSummary['open'].' terbuka' : null)"
                        :tone="$messagesSummary['new'] > 0 ? 'amber' : ($messagesSummary['open'] > 0 ? 'sky' : 'slate')"
                        :href="route('content.messages')" :link-label="__('Semua Pesan')">

                        @if ($messagesSummary['recent']->isEmpty())
                            <x-dashboard.empty icon="inbox" :message="__('Belum ada pesan dari formulir kontak website.')" />
                        @else
                            <ul class="text-xs divide-y divide-slate-200 dark:divide-slate-800/80">
                                @foreach ($messagesSummary['recent'] as $message)
                                    <li class="py-3 flex items-start justify-between gap-3" wire:key="msg-{{ $message->id }}">
                                        <div class="min-w-0 space-y-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-bold text-slate-900 dark:text-slate-100">{{ $message->name }}</span>
                                                @if ($message->organization)
                                                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $message->organization }}</span>
                                                @endif
                                                <x-badge :color="\App\Models\ContactMessage::STATUSES[$message->status]['color'] ?? 'slate'" class="text-[10px] px-1.5 py-0.5">
                                                    {{ \App\Models\ContactMessage::STATUSES[$message->status]['label'] ?? $message->status }}
                                                </x-badge>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                                <span class="text-emerald-600 dark:text-emerald-400">{{ $message->topicLabel() }}</span>
                                                &middot; {{ $message->created_at->diffForHumans() }}
                                            </p>
                                            <p class="text-slate-600 dark:text-slate-300 line-clamp-1 text-xs">
                                                {{ $message->message }}
                                            </p>
                                        </div>
                                        <div class="shrink-0 flex items-center gap-1.5 mt-0.5">
                                            @if ($replyUrl = $message->whatsappReplyUrl())
                                                <a href="{{ $replyUrl }}" target="_blank" rel="noopener"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/20 transition-colors"
                                                    title="{{ __('Balas via WhatsApp') }}">
                                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('content.messages') }}" wire:navigate
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
                                                title="{{ __('Buka detail pesan') }}">
                                                <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                                            </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </x-dashboard.panel>
                @endif

                {{-- WIDGET PRESTASI TERBARU & UNGGULAN --}}
                @if ($achievementsSummary !== null)
                    <x-dashboard.panel :title="__('Prestasi Terbaru & Unggulan')" icon="trophy"
                        :count="$achievementsSummary['total'] > 0 ? (string) $achievementsSummary['total'] : null"
                        :href="route('content.achievements')" :link-label="__('Kelola Prestasi')">

                        @if ($achievementsSummary['recent']->isEmpty())
                            <x-dashboard.empty icon="trophy" :message="__('Belum ada prestasi yang dicatat.')" />
                        @else
                            <ul class="text-xs divide-y divide-slate-200 dark:divide-slate-800/80">
                                @foreach ($achievementsSummary['recent'] as $achievement)
                                    @php
                                        $medalTone = match ($achievement->medal) {
                                            'emas' => 'amber',
                                            'perak' => 'slate',
                                            'perunggu' => 'amber',
                                            default => 'slate',
                                        };
                                    @endphp
                                    <li class="py-3 flex items-start justify-between gap-3" wire:key="ach-{{ $achievement->id }}">
                                        <div class="min-w-0 space-y-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-bold text-slate-900 dark:text-slate-100 truncate max-w-sm">{{ $achievement->title }}</span>
                                                @if ($achievement->medal)
                                                    <x-badge :color="$medalTone" class="text-[10px] px-1.5 py-0.5">
                                                        {{ \App\Models\Achievement::MEDALS[$achievement->medal] ?? $achievement->medal }}
                                                    </x-badge>
                                                @endif
                                                <x-badge color="sky" class="text-[10px] px-1.5 py-0.5">
                                                    {{ \App\Models\Achievement::LEVELS[$achievement->level] ?? ucfirst($achievement->level) }}
                                                </x-badge>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                                {{ $achievement->organizer }} &middot; {{ $achievement->achieved_on?->isoFormat('MMMM Y') }}
                                            </p>
                                        </div>
                                        <div class="shrink-0 flex items-center gap-2 mt-0.5">
                                            @if ($achievement->is_published)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    {{ __('Tayang') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                                                    {{ __('Draft') }}
                                                </span>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </x-dashboard.panel>
                @endif
            </div>

            {{-- KOLOM KANAN (5/12): VISUAL CHARTS, DISTRIBUSI & PINTASAN --}}
            <div class="lg:col-span-5 space-y-4 sm:space-y-6">
                {{-- WIDGET TREN PENGUNJUNG 7 HARI --}}
                @if ($analyticsSeries !== null)
                    <x-dashboard.panel :title="__('Pengunjung 7 Hari Terakhir')" icon="chart-no-axes-combined"
                        :href="route('content.analytics')" :link-label="__('Detail Statistik')">
                        <x-bar-chart :data="$analyticsSeries" color="emerald" />
                    </x-dashboard.panel>
                @endif

                {{-- WIDGET DISTRIBUSI PRESTASI PER BIDANG --}}
                @if ($achievementsSummary !== null && $achievementsSummary['categories']->isNotEmpty())
                    <x-dashboard.panel :title="__('Distribusi Prestasi per Bidang')" icon="chart-pie"
                        :href="route('content.categories')" :link-label="__('Kategori')">
                        <x-content.bar-list :items="$achievementsSummary['categories']" :total="$achievementsSummary['total']" unit="prestasi" />
                    </x-dashboard.panel>
                @endif

                {{-- PINTASAN AKSI CEPAT --}}
                <x-dashboard.shortcuts :links="$shortcuts" />

                {{-- FEED LOG AKTIVITAS TERBARU (HANYA UNTUK USER BERIZIN) --}}
                @if ($activities !== null)
                    <x-dashboard.activity-feed :activities="$activities" />
                @endif
            </div>
        </div>
    @else
        <x-dashboard.shortcuts :links="$shortcuts" />
    @endif
</div>
