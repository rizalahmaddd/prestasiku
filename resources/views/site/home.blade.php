@php
    use App\Support\Branding;
    use App\Support\Site\Palette;
    use Illuminate\Support\Js;
    use Illuminate\Support\Number;
    use Illuminate\Support\Str;

    $pageTitle = $content['seo_title'] ?: 'Portofolio Prestasi '.$name;
    $initial = Str::upper(Str::substr($name, 0, 1));
    $hasResume = $resumeSize !== null;
    $hasContact = $whatsappUrl || $content['email'] !== '' || $contactFormEnabled;
    $hasMap = $defaultLocation !== null;
    $profileCaption = collect([$age ? $age.' Tahun' : null, $content['tagline'] ?: null])->filter()->implode(' • ');
    $highlights = collect([1, 2, 3])
        ->map(fn (int $row) => ['label' => $content["highlight_{$row}_label"], 'value' => $content["highlight_{$row}_value"]])
        ->filter(fn (array $row) => $row['label'] !== '' && $row['value'] !== '')
        ->values();
    $headlineWords = preg_split('/\s+/', trim($content['hero_headline']), -1, PREG_SPLIT_NO_EMPTY);
    $accentWords = preg_split('/\s+/', trim($content['hero_headline_accent']), -1, PREG_SPLIT_NO_EMPTY);
    $introHtml = preg_replace('/\*\*(.+?)\*\*/', '<strong class="text-slate-900 font-semibold">$1</strong>', e($content['hero_intro']));
    $educationTones = [
        'active' => ['badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200', 'program' => 'text-royal-700'],
        'done' => ['badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'program' => 'text-slate-600'],
        'program' => ['badge' => 'bg-amber-50 text-amber-800 border-amber-200', 'program' => 'text-amber-700'],
    ];
    $asset = fn (string $file) => asset('site/'.$file).'?v='.filemtime(public_path('site/'.$file));
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#ffffff">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="{{ $name }}">
  <meta property="og:type" content="profile">
  <meta property="og:locale" content="id_ID">
  <meta property="og:title" content="{{ $pageTitle }}">
  <meta property="og:description" content="{{ $content['seo_description'] }}">
  @if ($ogImage)
    <meta property="og:image" content="{{ $ogImage->url() }}">
  @endif
  <meta name="twitter:card" content="summary_large_image">
  @if ($brandLogoUrl = Branding::logoUrl())
    <link rel="icon" href="{{ $brandLogoUrl }}">
  @else
    <link rel="icon" href="data:image/svg+xml,{{ rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><rect width='64' height='64' rx='14' fill='#0f172a'/><text x='32' y='44' font-family='Arial,sans-serif' font-size='34' font-weight='700' text-anchor='middle' fill='#fff'>".e($initial).'</text></svg>') }}">
  @endif
  <title>{{ $pageTitle }}</title>
  <meta name="description" content="{{ $content['seo_description'] }}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,600;1,6..72,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="https://unpkg.com/lucide@1.54.0/dist/umd/lucide.min.js"></script>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
            serif: ['"Newsreader"', 'serif'],
          },
          colors: {
            slate: { 850: '#151f32', 950: '#0a0f1d' },
            royal: { 50: '#eff6ff', 100: '#dbeafe', 600: '#1d4ed8', 700: '#1e40af', 800: '#1e3a8a', 900: '#172554' },
            amber: { 50: '#fffbeb', 100: '#fef3c7', 600: '#d97706', 700: '#b45309' }
          }
        }
      }
    }
  </script>
  <script>
    document.documentElement.classList.add('js');
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
      document.documentElement.classList.add('js-anim');
    }
  </script>

  <link rel="stylesheet" href="{{ $asset('site.css') }}">
</head>
<body class="selection:bg-royal-100 selection:text-royal-900">

  <header id="site-header" class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 transition-all">
    <div id="scroll-progress" aria-hidden="true"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div id="nav-inner" class="h-14 lg:h-20 flex items-center justify-between gap-4 lg:gap-6">

        <a href="#beranda" class="flex items-center gap-3 lg:gap-3.5 group min-w-0 lg:shrink-0">
          <div class="w-9 h-9 lg:w-10 lg:h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-base tracking-tight group-hover:bg-royal-700 group-hover:-rotate-6 group-hover:scale-105 transition-all duration-300 shadow-sm shrink-0">
            {{ $initial }}
          </div>
          <span id="appbar-title" class="lg:hidden font-bold text-slate-900 text-base tracking-tight truncate">{{ $name }}</span>
          <div class="hidden lg:block shrink-0">
            <div class="flex items-center gap-2">
              <span class="font-bold text-slate-900 text-base tracking-tight group-hover:text-royal-700 transition-colors whitespace-nowrap">{{ $name }}</span>
              @if ($content['grade'] !== '')
                <span class="px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 rounded-full border border-slate-200 shrink-0">{{ $content['grade'] }}</span>
              @endif
            </div>
            @if ($content['school_line'] !== '')
              <p class="text-xs text-slate-500 font-medium whitespace-nowrap">{{ $content['school_line'] }}</p>
            @endif
          </div>
        </a>

        <nav class="hidden lg:flex items-center gap-1 xl:gap-1.5 relative isolate shrink-0">
          <span id="nav-pill" class="slide-pill" aria-hidden="true"></span>
          <a href="#beranda" class="nav-link px-3 py-2 text-xs xl:text-sm text-slate-600 hover:text-slate-900 font-medium rounded-lg hover:bg-slate-100/70 transition-colors whitespace-nowrap shrink-0">Beranda</a>
          <a href="#tentang" class="nav-link px-3 py-2 text-xs xl:text-sm text-slate-600 hover:text-slate-900 font-medium rounded-lg hover:bg-slate-100/70 transition-colors whitespace-nowrap shrink-0">Profil</a>
          <a href="#pendidikan" class="nav-link px-3 py-2 text-xs xl:text-sm text-slate-600 hover:text-slate-900 font-medium rounded-lg hover:bg-slate-100/70 transition-colors whitespace-nowrap shrink-0">Pendidikan & Kampus</a>
          <a href="#prestasi" class="nav-link px-3 py-2 text-xs xl:text-sm text-slate-600 hover:text-slate-900 font-medium rounded-lg hover:bg-slate-100/70 transition-colors flex items-center gap-1.5 whitespace-nowrap shrink-0">
            <span>Prestasi & Galeri</span>
            @if ($achievements->isNotEmpty())
              <span class="px-1.5 py-0.2 text-[10px] bg-royal-100 text-royal-700 font-bold rounded-full">{{ $achievements->count() }}</span>
            @endif
          </a>
        </nav>

        <div class="flex items-center gap-0.5 lg:gap-3 shrink-0 -mr-2 lg:mr-0">
          @if ($hasResume)
            <button onclick="downloadPortfolioModal()" data-nudge="y" class="hidden lg:inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 transition-all shadow-sm shrink-0 whitespace-nowrap">
              <i data-lucide="file-text" class="w-3.5 h-3.5 text-slate-500 shrink-0"></i>
              <span class="whitespace-nowrap">Resume PDF</span>
            </button>
          @endif

          @if ($hasContact)
            <a href="#kontak" data-nudge="x" class="btn-shine hidden lg:inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-royal-700 rounded-xl transition-all shadow-sm shrink-0 whitespace-nowrap">
              <span class="whitespace-nowrap">{{ $content['contact_cta'] }}</span>
              <i data-lucide="arrow-right" class="w-3.5 h-3.5 shrink-0"></i>
            </a>
          @endif

          @if ($hasResume)
            <button type="button" onclick="downloadPortfolioModal()" aria-label="Resume PDF" class="appbar-btn lg:hidden">
              <i data-lucide="file-text" class="w-5 h-5"></i>
            </button>
          @endif
          @if ($whatsappUrl)
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp" class="appbar-btn lg:hidden">
              <i data-lucide="message-circle" class="w-5 h-5"></i>
            </a>
          @endif
        </div>

      </div>
    </div>
  </header>

  <section id="beranda" class="relative pt-6 pb-16 md:pt-20 md:pb-24 bg-white border-b border-slate-200/80 bg-paper-grid">
    <div class="hero-dots" aria-hidden="true"></div>
    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

        <div class="lg:col-span-7 space-y-6">
          <div data-hero style="--h:0" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
            <span>{{ $content['hero_badge'] }}</span>
            @if ($updatedLabel)
              <span class="text-slate-300">•</span>
              <span class="text-slate-500">Update {{ $updatedLabel }}</span>
            @endif
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
            @foreach ($headlineWords as $word)<span class="w" style="--w:{{ $loop->index }}"><span>{{ $word }}</span></span> @endforeach
            @if ($accentWords)
              <span class="hero-mark font-serif italic font-normal text-royal-700">@foreach ($accentWords as $word)<span class="w" style="--w:{{ count($headlineWords) + $loop->index }}"><span>{{ $word }}</span></span>{{ $loop->last ? '' : ' ' }}@endforeach<svg class="hero-stroke" viewBox="0 0 300 12" preserveAspectRatio="none" aria-hidden="true"><path pathLength="1" d="M2 8 C 60 2, 110 11, 160 6 S 250 3, 298 7" /></svg></span>
            @endif
          </h1>

          @if ($content['hero_intro'] !== '')
            <p data-hero style="--h:4" class="text-slate-600 text-base sm:text-lg leading-relaxed max-w-xl">{!! $introHtml !!}</p>
          @endif

          <div data-hero style="--h:8" class="flex flex-wrap items-center gap-3 pt-2">
            <a href="#prestasi" data-magnetic data-nudge="y" class="btn-shine px-6 py-3 rounded-xl bg-slate-900 hover:bg-royal-700 text-white font-semibold text-sm transition-all shadow-sm flex items-center gap-2">
              <span>{{ $content['hero_cta_primary'] }}</span>
              <i data-lucide="arrow-down" class="w-4 h-4"></i>
            </a>
            <a href="#pendidikan" class="px-5 py-3 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-semibold text-sm transition-all">
              {{ $content['hero_cta_secondary'] }}
            </a>
          </div>
        </div>

        <div class="lg:col-span-5 order-first lg:order-none flex justify-center [perspective:1100px]">
          <div id="hero-card" class="w-full max-w-sm">
            <div class="clean-card rounded-2xl p-3 lg:p-6 relative">
              <div class="aspect-[16/10] lg:aspect-[4/4.8] rounded-xl overflow-hidden relative border border-slate-200 {{ $highlights->isNotEmpty() ? 'lg:mb-5' : '' }} bg-slate-100 shadow-sm group">
                @if ($portrait)
                  <img src="{{ $portrait->url() }}" alt="{{ $name }}" class="w-full h-full object-cover object-[center_68%] lg:object-top group-hover:scale-105 transition-transform duration-500">
                @else
                  <div class="w-full h-full flex items-center justify-center text-7xl font-extrabold text-slate-300">{{ $initial }}</div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>
                <div class="glare"></div>
                <div class="absolute bottom-3 left-3 right-3 bg-white/95 backdrop-blur-sm border border-slate-200/90 rounded-lg p-2.5 shadow-sm flex items-center justify-between text-xs">
                  <div>
                    <span class="block font-bold text-slate-900">{{ $name }}</span>
                    @if ($profileCaption !== '')
                      <span class="text-[11px] text-slate-500">{{ $profileCaption }}</span>
                    @endif
                  </div>
                </div>
              </div>

              @if ($highlights->isNotEmpty())
                <div class="hidden lg:block space-y-3 pt-1 border-t border-slate-100 text-xs text-slate-600">
                  @foreach ($highlights as $highlight)
                    <div class="flex justify-between items-center gap-3 py-1">
                      <span class="text-slate-500 shrink-0">{{ $highlight['label'] }}:</span>
                      <span class="font-semibold text-right {{ $loop->last ? 'text-royal-700' : 'text-slate-900' }}">{{ $highlight['value'] }}</span>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  @if ($summaryStats->isNotEmpty())
    <section class="py-12 bg-white border-b border-slate-200">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div data-stagger="scale" class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
          @foreach ($summaryStats as $stat)
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
              <div data-count="{{ $stat->value }}" @if ($stat->suffix) data-suffix="{{ $stat->suffix }}" @endif class="tabular-nums text-3xl sm:text-4xl font-extrabold {{ Palette::text($stat->tone) }} tracking-tight">{{ $stat->value }}{{ $stat->suffix }}</div>
              <div class="text-xs sm:text-sm font-bold text-slate-900 mt-1">{{ $stat->label }}</div>
              @if ($stat->caption)
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $stat->caption }}</p>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  <section id="tentang" class="py-20 bg-slate-50 border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

      <div data-reveal class="max-w-3xl mb-12">
        <span class="text-xs font-bold uppercase tracking-wider text-royal-700">{{ $content['about_eyebrow'] }}</span>
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">{{ $content['about_title'] }}</h2>
        @if ($content['about_body'] !== '')
          <p class="text-slate-600 text-sm sm:text-base mt-2 leading-relaxed">{{ $content['about_body'] }}</p>
        @endif
      </div>

      <div data-stagger="up" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach ($interests as $interest)
          <div class="clean-card rounded-2xl p-6 space-y-3">
            <div class="w-10 h-10 rounded-xl {{ Palette::soft($interest->tone) }} flex items-center justify-center font-bold text-lg">
              <i data-lucide="{{ $interest->icon }}" class="w-5 h-5"></i>
            </div>
            <h3 class="font-bold text-base text-slate-900">{{ $interest->title }}</h3>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $interest->description }}</p>
            @if ($interest->tags)
              <div class="pt-2 flex flex-wrap gap-1.5 text-[11px] text-slate-600">
                @foreach ($interest->tags as $tag)
                  <span class="px-2 py-0.5 bg-slate-100 rounded">{{ $tag }}</span>
                @endforeach
              </div>
            @endif
          </div>
        @endforeach
      </div>

    </div>
  </section>

  <section id="pendidikan" class="py-20 bg-white border-b border-slate-200">
    <div id="lokasi" class="scroll-mt-24"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

      <div data-reveal class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12">
        <div class="max-w-2xl">
          <span class="text-xs font-bold uppercase tracking-wider text-royal-700">{{ $content['education_eyebrow'] }}</span>
          <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">{{ $content['education_title'] }}</h2>
          @if ($content['education_body'] !== '')
            <p class="text-slate-600 text-sm mt-2 leading-relaxed">{{ $content['education_body'] }}</p>
          @endif
        </div>
      </div>

      <div data-reveal="scale" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <div class="{{ $hasMap ? 'lg:col-span-5' : 'lg:col-span-12' }} space-y-4">
          @foreach ($educations as $education)
            @php($mapKey = $hasMap ? $education->location?->mapKey() : null)
            <div @if ($mapKey) data-map-key="{{ $mapKey }}" onclick="selectLocation('{{ $mapKey }}')" @endif
              class="edu-card clean-card rounded-2xl p-5 border-2 border-transparent bg-white {{ $mapKey ? 'cursor-pointer hover:border-slate-300' : '' }} transition-all shadow-sm">
              <div class="flex items-center justify-between gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $educationTones[$education->tone]['badge'] ?? $educationTones['done']['badge'] }}">
                  {{ $education->period_label }}
                </span>
                @if ($education->city)
                  <span class="text-[11px] font-medium text-slate-500">{{ $education->city }}</span>
                @endif
              </div>
              <div class="mt-2.5">
                <h3 class="text-base font-bold text-slate-900">{{ $education->name }}</h3>
                @if ($education->program)
                  <p class="text-xs font-semibold mt-0.5 {{ $educationTones[$education->tone]['program'] ?? $educationTones['done']['program'] }}">{{ $education->program }}</p>
                @endif
              </div>
              @if ($education->description)
                <p class="text-xs text-slate-600 mt-2 leading-relaxed">{{ $education->description }}</p>
              @endif
              @if ($education->footnote || $mapKey)
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-3 text-xs">
                  <span class="text-[11px] font-semibold text-slate-500">{{ $education->footnote ? '• '.$education->footnote : '' }}</span>
                  @if ($mapKey)
                    <span class="edu-card-status text-xs font-medium text-slate-500 flex items-center gap-1 shrink-0">
                      <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                      <span>Lihat di Peta</span>
                    </span>
                  @endif
                </div>
              @endif
            </div>
          @endforeach

          @if ($hasMap && $venues->isNotEmpty())
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-wrap items-center justify-between gap-2">
              <span class="text-xs font-bold text-slate-700">{{ $content['venues_label'] }}:</span>
              <div class="flex flex-wrap gap-2">
                @foreach ($venues as $venue)
                  <button type="button" onclick="selectLocation('{{ $venue->mapKey() }}')" class="tap px-3 py-1 bg-white hover:bg-slate-100 rounded-xl text-xs font-semibold text-slate-800 border border-slate-200 flex items-center gap-1 transition-colors">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-royal-700"></i> {{ $venue->short_name ?: $venue->name }}
                  </button>
                @endforeach
              </div>
            </div>
          @endif
        </div>

        @if ($hasMap)
          <div class="lg:col-span-7 flex flex-col space-y-4">

            <div class="clean-card rounded-3xl overflow-hidden border border-slate-300 shadow-sm relative">
              <div id="school-map" class="w-full h-[260px] sm:h-[400px] bg-slate-100"></div>

              <div class="absolute top-3 left-3 z-[400] bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-xl border border-slate-200 text-[11px] font-semibold text-slate-800 shadow-md flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span id="map-floating-label">{{ $defaultLocation->name }}</span>
              </div>

              <button type="button" onclick="resetMapView()" title="Kembali ke lokasi awal" class="absolute bottom-3 right-3 z-[400] bg-white/95 hover:bg-slate-50 text-slate-700 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold shadow-md flex items-center gap-1.5 transition-colors">
                <i data-lucide="focus" class="w-3.5 h-3.5 text-royal-700"></i>
                <span>Fokus Lokasi</span>
              </button>
            </div>

            <div id="location-card" class="clean-card rounded-3xl p-5 sm:p-6 border border-slate-300 space-y-4 shadow-sm">

              <div class="flex items-center justify-between gap-2">
                <span id="loc-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-royal-50 text-royal-700 border border-royal-200 empty:hidden">{{ $defaultLocation->badge ?: 'Lokasi' }}</span>
                <span id="loc-status" class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 empty:hidden">{{ $defaultLocation->status }}</span>
              </div>

              <div>
                <h3 id="loc-name" class="text-lg sm:text-xl font-bold text-slate-900 mt-1">{{ $defaultLocation->name }}</h3>
                <p id="loc-subtitle" class="text-xs text-royal-700 font-semibold mt-0.5">{{ $defaultLocation->subtitle }}</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5">
                <div class="sm:col-span-5 aspect-[16/10] sm:aspect-auto sm:h-28 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 relative">
                  <img id="loc-photo" src="{{ $payload['locations'][$defaultLocation->mapKey()]['photo'] }}" alt="{{ $defaultLocation->name }}" class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent flex items-end p-2">
                    <p id="loc-photo-caption" class="text-white text-[10px] font-medium leading-tight">{{ $defaultLocation->caption }}</p>
                  </div>
                </div>

                <div class="sm:col-span-7 p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-xs flex flex-col justify-between">
                  <div class="flex items-start gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-royal-700 mt-0.5 shrink-0"></i>
                    <p id="loc-address" class="text-slate-600 leading-relaxed text-[11px]">{{ $defaultLocation->address }}</p>
                  </div>
                </div>
              </div>

              <a id="loc-nav-link" href="{{ $payload['locations'][$defaultLocation->mapKey()]['navUrl'] }}" target="_blank" rel="noopener noreferrer" class="tap inline-flex items-center gap-1.5 text-xs font-semibold text-royal-700 hover:text-royal-900 transition-colors">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                <span>Buka di Google Maps</span>
              </a>

            </div>

          </div>
        @endif

      </div>

      @if ($roadmapSteps->isNotEmpty())
        <div class="mt-16 pt-12 border-t border-slate-200">
          <div data-reveal class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10">
            <div class="max-w-2xl">
              <span class="text-xs font-bold uppercase tracking-wider text-royal-700">{{ $content['roadmap_eyebrow'] }}</span>
              <h3 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">{{ $content['roadmap_title'] }}</h3>
              @if ($content['roadmap_body'] !== '')
                <p class="text-slate-600 text-sm mt-2 leading-relaxed">{{ $content['roadmap_body'] }}</p>
              @endif
            </div>

            @if ($roadmapStats->isNotEmpty())
              <div class="grid divide-x divide-slate-200 bg-white border border-slate-200 rounded-2xl shadow-sm text-center w-full sm:w-auto shrink-0 self-start lg:self-auto" style="grid-template-columns: repeat({{ $roadmapStats->count() }}, minmax(0, 1fr))">
                @foreach ($roadmapStats as $stat)
                  <div class="px-4 sm:px-6 py-3">
                    <p class="text-xl font-extrabold {{ Palette::text($stat->tone) }} tabular-nums leading-none">{{ $stat->value }}{{ $stat->suffix }}</p>
                    <p class="text-[11px] font-medium text-slate-500 whitespace-nowrap mt-1.5">{{ $stat->label }}</p>
                  </div>
                @endforeach
              </div>
            @endif
          </div>

          <ol data-stagger="up" class="roadmap" style="--roadmap-steps: {{ $roadmapSteps->count() }}">
            @foreach ($roadmapSteps as $step)
              <li class="roadmap-step {{ ['peak' => 'is-peak', 'next' => 'is-next'][$step->state] ?? '' }}">
                <button type="button" onclick="jumpToCategory('{{ $step->jump_target ?: 'all' }}')" class="group block w-full text-left rounded-xl">
                  <span class="roadmap-node tabular-nums">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                  <span class="block text-xs font-bold text-slate-500 tabular-nums">{{ $step->period }}</span>
                  <span class="block text-sm font-bold text-slate-900 group-hover:text-royal-700 transition-colors leading-snug mt-1">{{ $step->title }}</span>
                  @if ($step->description)
                    <span class="block text-xs text-slate-600 leading-relaxed mt-1.5">{{ $step->description }}</span>
                  @endif
                  @if ($step->tag)
                    <span class="roadmap-tag">{{ $step->tag }}</span>
                  @endif
                </button>
              </li>
            @endforeach
          </ol>
        </div>
      @endif

    </div>
  </section>

  <section id="prestasi" class="py-20 bg-slate-50 border-b border-slate-200">
    <div id="galeri" class="scroll-mt-24"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

      <div data-reveal class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-8">
        <div class="max-w-2xl">
          <span class="text-xs font-bold uppercase tracking-wider text-royal-700">{{ $content['showcase_eyebrow'] }}</span>
          <h2 class="text-2xl sm:text-4xl font-bold text-slate-900 tracking-tight mt-1">{{ $content['showcase_title'] }}</h2>
          @if ($content['showcase_body'] !== '')
            <p class="text-slate-600 text-sm sm:text-base mt-2 leading-relaxed">{{ $content['showcase_body'] }}</p>
          @endif
        </div>

        <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-2xl border border-slate-200 shadow-sm text-xs w-full sm:w-auto shrink-0 self-start lg:self-auto">
          <button type="button" onclick="switchShowcaseTab('prestasi')" id="switch-tab-prestasi" class="showcase-tab-btn flex-1 sm:flex-none justify-center whitespace-nowrap active px-4 py-2 rounded-xl font-bold transition-all bg-slate-900 text-white shadow-sm flex items-center gap-2">
            <i data-lucide="award" class="w-3.5 h-3.5"></i>
            <span class="sm:hidden">Prestasi ({{ $achievements->count() }})</span><span class="hidden sm:inline">Piagam & Prestasi ({{ $achievements->count() }})</span>
          </button>
          <button type="button" onclick="switchShowcaseTab('galeri')" id="switch-tab-galeri" class="showcase-tab-btn flex-1 sm:flex-none justify-center whitespace-nowrap px-4 py-2 rounded-xl font-semibold text-slate-600 hover:text-slate-900 transition-all flex items-center gap-2">
            <i data-lucide="camera" class="w-3.5 h-3.5"></i>
            <span class="sm:hidden">Foto ({{ $gallery->count() }})</span><span class="hidden sm:inline">Foto & Dokumentasi ({{ $gallery->count() }})</span>
          </button>
        </div>
      </div>

      <div id="panel-prestasi" class="showcase-panel transition-all">

        <div id="tally" data-reveal class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4"></div>

        <div data-reveal class="bg-white rounded-2xl p-3 sm:p-4 mb-5 border border-slate-200 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-3">
          <div class="relative isolate flex items-center gap-1.5 overflow-x-auto w-full lg:w-auto scrollbar-none" id="category-tabs">
            <span id="tab-pill" class="slide-pill" aria-hidden="true"></span>
            <button onclick="filterCategory('all', this)" aria-pressed="true" class="category-btn active px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all whitespace-nowrap">
              Semua <span class="cat-count tabular-nums opacity-70" data-cat="all"></span>
            </button>
            @foreach ($achievementCategories as $category)
              <button onclick="filterCategory('{{ $category->slug }}', this)" class="category-btn px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all whitespace-nowrap">
                {{ $category->name }} <span class="cat-count tabular-nums opacity-70" data-cat="{{ $category->slug }}"></span>
              </button>
            @endforeach
          </div>

          <div class="flex items-center gap-2 w-full lg:w-auto">
            <div class="relative flex-1 lg:w-60">
              <input type="text" id="achievement-search" oninput="handleSearch(this.value)" placeholder="Cari lomba atau tahun..." aria-label="Cari prestasi" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 pl-9 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-royal-600 focus:bg-white transition-all">
              <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
            </div>
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl shrink-0" role="group" aria-label="Tampilan">
              <button type="button" onclick="setAchievementView('grid')" data-view="grid" aria-pressed="true" class="ach-view-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 transition-all">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i><span>Kartu</span>
              </button>
              <button type="button" onclick="setAchievementView('timeline')" data-view="timeline" aria-pressed="false" class="ach-view-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 transition-all">
                <i data-lucide="git-commit-vertical" class="w-3.5 h-3.5"></i><span>Linimasa</span>
              </button>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between gap-3 mb-4 px-1 text-xs text-slate-500">
          <p><span id="visible-count" class="inline-block font-bold text-slate-900 tabular-nums">{{ $achievements->count() }}</span> dari {{ $achievements->count() }} prestasi pilihan</p>
          <p id="carousel-pos" class="md:hidden flex items-center gap-1.5 font-medium" aria-hidden="true">
            <span>Geser</span><i data-lucide="arrow-right" class="w-3.5 h-3.5"></i><span id="carousel-pos-text" class="tabular-nums text-slate-900 font-bold">1/{{ $achievements->count() }}</span>
          </p>
        </div>

        <div id="achievements-grid" data-stagger="up" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
        <ol id="achievements-timeline" class="hidden"></ol>

        <div id="no-results" class="hidden text-center py-16 bg-white rounded-2xl border border-slate-200 mt-6">
          <h4 class="font-bold text-base text-slate-900">Data Tidak Ditemukan</h4>
          <p class="text-xs text-slate-500 mt-1">Coba kata kunci lain atau hapus filter yang aktif.</p>
          <button onclick="resetFilters()" class="mt-3 px-4 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-semibold">
            Reset Filter
          </button>
        </div>

      </div>

      <div id="panel-galeri" class="showcase-panel hidden transition-all">

        <div data-reveal class="bg-white rounded-2xl p-4 mb-8 border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
          <div>
            <h3 class="text-sm font-bold text-slate-900">{{ $content['gallery_title'] }}</h3>
            @if ($content['gallery_body'] !== '')
              <p class="text-xs text-slate-500">{{ $content['gallery_body'] }}</p>
            @endif
          </div>

          <div class="flex flex-wrap gap-1.5 bg-slate-100 p-1.5 rounded-2xl border border-slate-200 text-xs shrink-0">
            <button type="button" onclick="filterGalleryCategory('all', this)" class="gal-filter-btn px-3 py-1.5 rounded-xl font-semibold transition-all active bg-slate-900 text-white shadow-sm">
              Semua ({{ $gallery->count() }})
            </button>
            @foreach ($galleryCategories as $category)
              <button type="button" onclick="filterGalleryCategory('{{ $category->slug }}', this)" class="gal-filter-btn px-3 py-1.5 rounded-xl font-semibold text-slate-600 hover:text-slate-900 transition-all flex items-center">
                @if ($category->icon)
                  <i data-lucide="{{ $category->icon }}" class="w-3.5 h-3.5 inline-block mr-1"></i>
                @endif
                {{ $category->name }} ({{ $category->items_count }})
              </button>
            @endforeach
          </div>
        </div>

        <div id="gallery-grid" data-stagger="wipe" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach ($gallery as $item)
            <div class="gallery-card clean-card rounded-2xl overflow-hidden group cursor-pointer" data-category="{{ $item->category?->slug }}" onclick="openPhotoModalByIndex({{ $loop->index }})">
              <div class="aspect-[4/3] bg-slate-100 overflow-hidden relative border-b border-slate-200">
                <img src="{{ $payload['gallery'][$loop->index]['img'] }}" alt="{{ $item->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @if ($payload['gallery'][$loop->index]['badge'] !== '')
                  <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md text-[11px] font-bold bg-white/95 text-slate-900 shadow-sm border border-slate-200">
                    {{ $payload['gallery'][$loop->index]['badge'] }}
                  </span>
                @endif
              </div>
              <div class="p-4 space-y-1">
                <div class="flex items-center justify-between gap-3 text-[11px] text-slate-500">
                  <span class="font-semibold text-royal-700 truncate">{{ $item->place }}</span>
                  <span class="shrink-0">{{ $item->taken_on?->translatedFormat('M Y') }}</span>
                </div>
                <p class="text-xs font-bold text-slate-900 group-hover:text-royal-700 transition-colors">{{ $item->title }}</p>
                @if ($item->description)
                  <p class="text-[11px] text-slate-500 leading-relaxed line-clamp-2">{{ $item->description }}</p>
                @endif
              </div>
            </div>
          @endforeach
        </div>

        @if ($gallery->isEmpty())
          <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
            <h4 class="font-bold text-base text-slate-900">Belum Ada Foto</h4>
            <p class="text-xs text-slate-500 mt-1">Dokumentasi kegiatan akan tampil di sini.</p>
          </div>
        @endif
      </div>

    </div>
  </section>

  @if ($testimonials->isNotEmpty())
    <section class="py-20 bg-white border-b border-slate-200">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div data-reveal class="max-w-2xl mb-12">
          <span class="text-xs font-bold uppercase tracking-wider text-royal-700">{{ $content['testimonials_eyebrow'] }}</span>
          <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-1">{{ $content['testimonials_title'] }}</h2>
        </div>

        <div data-stagger="up" class="grid grid-cols-1 md:grid-cols-3 gap-6">
          @foreach ($testimonials as $testimonial)
            <div class="clean-card rounded-2xl p-6 flex flex-col justify-between">
              <p class="text-xs sm:text-sm text-slate-700 leading-relaxed italic">"{{ $testimonial->quote }}"</p>
              <div class="pt-4 mt-6 border-t border-slate-100 flex items-center gap-3">
                @if ($testimonial->image)
                  <img src="{{ $testimonial->image->url() }}" alt="{{ $testimonial->name }}" loading="lazy" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                @else
                  <span class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-sm font-bold text-slate-500">{{ Str::upper(Str::substr($testimonial->name, 0, 1)) }}</span>
                @endif
                <div>
                  <p class="font-bold text-xs text-slate-900">{{ $testimonial->name }}</p>
                  @if ($testimonial->role)
                    <p class="text-[11px] text-slate-500">{{ $testimonial->role }}</p>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>

      </div>
    </section>
  @endif

  <section id="kontak" class="py-20 bg-slate-50 border-b border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

      <div data-reveal class="clean-card rounded-3xl p-8 sm:p-12 border border-slate-300 shadow-sm bg-white text-center space-y-8">

        <div class="max-w-xl mx-auto space-y-3">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-royal-50 text-royal-700 border border-royal-200">
            <i data-lucide="users" class="w-3.5 h-3.5"></i>
            <span>{{ $content['contact_badge'] }}</span>
          </div>
          <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">{{ $content['contact_title'] }}</h2>
          @if ($content['contact_body'] !== '')
            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">{{ $content['contact_body'] }}</p>
          @endif
        </div>

        @if ($hasContact)
          <div class="flex flex-col sm:flex-row sm:flex-wrap items-center justify-center gap-3.5 pt-1">
            @if ($whatsappUrl)
              <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="btn-shine w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-slate-900 hover:bg-royal-700 text-white font-semibold text-xs shadow-sm transition-all group">
                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400"></i>
                <span>Chat WhatsApp ({{ $whatsappLabel }})</span>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-white transition-colors"></i>
              </a>
            @endif
            @if ($content['email'] !== '')
              <button type="button" onclick="copyEmailAddress()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-800 font-semibold text-xs border border-slate-200 transition-all">
                <i data-lucide="mail" class="w-4 h-4 text-royal-700"></i>
                <span>{{ $content['email'] }}</span>
                <i data-lucide="copy" class="w-3.5 h-3.5 text-slate-400"></i>
              </button>
            @endif
            @if ($contactFormEnabled)
              <button type="button" onclick="openContactModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 font-semibold text-xs border border-slate-300 transition-all">
                <i data-lucide="pen-line" class="w-4 h-4 text-royal-700"></i>
                <span>Tulis Pesan</span>
              </button>
            @endif
          </div>
        @endif

      </div>

    </div>
  </section>

  <footer class="py-10 bg-slate-100 border-t border-slate-200 text-xs text-slate-500">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div>
        <p class="font-bold text-slate-900">{{ $content['footer_title'] ?: $name.' • Portofolio Siswa' }}</p>
        @if ($content['footer_note'] !== '')
          <p class="text-[11px] text-slate-500 mt-0.5">{{ $content['footer_note'] }}</p>
        @endif
      </div>

      <div class="flex items-center gap-6">
        <a href="#beranda" class="hover:text-slate-900 transition-colors">Beranda</a>
        <a href="#pendidikan" class="hover:text-slate-900 transition-colors">Pendidikan & Kampus</a>
        <a href="#prestasi" class="hover:text-slate-900 transition-colors">Prestasi & Dokumentasi</a>
        <a href="#kontak" class="hover:text-slate-900 transition-colors">Kontak</a>
      </div>

      <div>
        <p>&copy; {{ now()->year }} {{ $name }}. Hak cipta dilindungi.</p>
      </div>
    </div>
  </footer>

  <button id="to-top" type="button" aria-label="Kembali ke atas" class="to-top fixed bottom-5 right-5 z-30 w-11 h-11 rounded-xl bg-slate-900 hover:bg-royal-700 text-white shadow-lg flex items-center justify-center">
    <i data-lucide="chevron-up" class="w-5 h-5"></i>
  </button>

  <nav id="tabbar" class="tabbar" aria-label="Navigasi utama">
    <a href="#beranda" data-tab="beranda" class="tab-btn is-active" aria-current="page">
      <span class="tab-icon"><i data-lucide="home" class="w-5 h-5"></i></span>
      <span>Beranda</span>
    </a>
    <a href="#pendidikan" data-tab="pendidikan" class="tab-btn">
      <span class="tab-icon"><i data-lucide="graduation-cap" class="w-5 h-5"></i></span>
      <span>Pendidikan</span>
    </a>
    <a href="#prestasi" data-tab="prestasi" class="tab-btn">
      <span class="tab-icon"><i data-lucide="trophy" class="w-5 h-5"></i></span>
      <span>Prestasi</span>
    </a>
    <a href="#galeri" data-tab="galeri" class="tab-btn">
      <span class="tab-icon"><i data-lucide="camera" class="w-5 h-5"></i></span>
      <span>Galeri</span>
    </a>
    <button type="button" data-tab="lainnya" aria-haspopup="dialog" aria-controls="more-sheet" class="tab-btn">
      <span class="tab-icon"><i data-lucide="menu" class="w-5 h-5"></i></span>
      <span>Lainnya</span>
    </button>
  </nav>

  <div id="more-sheet" role="dialog" aria-modal="true" aria-labelledby="more-sheet-title" class="modal sheet fixed inset-0 z-50 hidden flex items-end justify-center bg-slate-900/50">
    <div tabindex="-1" class="sheet-panel w-full max-w-lg bg-white rounded-t-3xl shadow-2xl px-3 pt-2">
      <div class="sheet-grip" aria-hidden="true"></div>
      <h2 id="more-sheet-title" class="px-2 pb-1 text-xs font-bold uppercase tracking-wider text-slate-500">Lainnya</h2>
      <a href="#tentang" data-go="tentang" class="sheet-row">
        <span class="sheet-ico"><i data-lucide="user-round" class="w-5 h-5"></i></span>
        <span class="flex-1 min-w-0">
          <span class="block text-sm font-semibold">{{ $content['about_eyebrow'] }}</span>
          <span class="block text-xs text-slate-500">Minat dan bidang yang ditekuni {{ $firstName }}</span>
        </span>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 shrink-0"></i>
      </a>
      <a href="#kontak" data-go="kontak" class="sheet-row">
        <span class="sheet-ico"><i data-lucide="mail" class="w-5 h-5"></i></span>
        <span class="flex-1 min-w-0">
          <span class="block text-sm font-semibold">Kontak</span>
          <span class="block text-xs text-slate-500">{{ $contactFormEnabled ? 'Tulis pesan atau hubungi narahubung' : $content['contact_badge'] }}</span>
        </span>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 shrink-0"></i>
      </a>
      @if ($hasResume)
        <button type="button" data-act="resume" class="sheet-row">
          <span class="sheet-ico"><i data-lucide="file-text" class="w-5 h-5"></i></span>
          <span class="flex-1 min-w-0">
            <span class="block text-sm font-semibold">Resume PDF</span>
            <span class="block text-xs text-slate-500">Unduh ringkasan portofolio</span>
          </span>
          <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 shrink-0"></i>
        </button>
      @endif
      @if ($whatsappUrl)
        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="sheet-row">
          <span class="sheet-ico" style="background:#d1fae5;color:#047857"><i data-lucide="message-circle" class="w-5 h-5"></i></span>
          <span class="flex-1 min-w-0">
            <span class="block text-sm font-semibold">Chat WhatsApp</span>
            <span class="block text-xs text-slate-500">Terbuka di aplikasi WhatsApp</span>
          </span>
          <i data-lucide="external-link" class="w-4 h-4 text-slate-400 shrink-0"></i>
        </a>
      @endif
    </div>
  </div>

  <div id="toast" class="toast" role="status" aria-live="polite"></div>

  <div id="cert-modal" role="dialog" aria-modal="true" aria-labelledby="cert-modal-title" class="modal fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="modal-panel relative w-full max-w-2xl max-h-[92vh] overflow-y-auto bg-white rounded-2xl border border-slate-300 p-6 sm:p-8 shadow-2xl">
      <button onclick="closeCertificateModal()" aria-label="Tutup" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 hover:rotate-90 text-slate-600 flex items-center justify-center transition-all duration-300">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
      <div id="modal-content"></div>
    </div>
  </div>

  <div id="photo-modal" role="dialog" aria-modal="true" aria-labelledby="photo-modal-title" class="modal fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="modal-panel modal-panel--flat relative w-full max-w-2xl bg-white rounded-2xl border border-slate-300 p-5 sm:p-6 shadow-2xl space-y-4">
      <button onclick="closePhotoModal()" aria-label="Tutup" class="absolute top-3 right-3 z-10 w-9 h-9 rounded-full bg-white hover:bg-slate-100 hover:rotate-90 text-slate-700 border border-slate-200 shadow-sm flex items-center justify-center transition-all duration-300">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
      <div id="photo-frame" class="relative w-full aspect-[4/3] max-h-[50vh] bg-slate-100 rounded-xl overflow-hidden border border-slate-200">
        <img id="photo-modal-img" src="" alt="Dokumentasi" class="w-full h-full object-cover">
        <button type="button" onclick="stepPhoto(-1)" aria-label="Foto sebelumnya" class="photo-nav absolute left-3 top-1/2 -mt-5 w-10 h-10 rounded-full bg-white text-slate-800 shadow-md flex items-center justify-center">
          <i data-lucide="chevron-left" class="w-4 h-4"></i>
        </button>
        <button type="button" onclick="stepPhoto(1)" aria-label="Foto berikutnya" class="photo-nav absolute right-3 top-1/2 -mt-5 w-10 h-10 rounded-full bg-white text-slate-800 shadow-md flex items-center justify-center">
          <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </button>
        <span id="photo-counter" class="tabular-nums absolute bottom-3 right-3 px-2 py-0.5 rounded-md bg-slate-900/80 text-white text-[11px] font-semibold"></span>
      </div>
      <div id="photo-modal-text">
        <div class="flex items-center justify-between gap-2">
          <span id="photo-modal-badge" class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200 empty:hidden"></span>
          <span id="photo-modal-meta" class="text-[11px] text-slate-500 font-semibold"></span>
        </div>
        <h4 id="photo-modal-title" class="font-bold text-lg text-slate-900 mt-1"></h4>
        <p id="photo-modal-desc" class="text-xs text-slate-600 mt-1.5 leading-relaxed"></p>
      </div>

      <div>
        <p class="text-[11px] font-bold text-slate-500 mb-1.5">Jelajahi Foto Lainnya:</p>
        <div id="photo-thumb-strip" class="flex gap-2 overflow-x-auto pb-1 scrollbar-none"></div>
      </div>

      <div class="pt-2 flex items-center justify-between border-t border-slate-100">
        <button type="button" onclick="shareActivePhoto()" class="text-xs font-semibold text-royal-700 hover:text-royal-900 flex items-center gap-1.5 transition-colors">
          <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
          <span>Salin Keterangan Foto</span>
        </button>
        <button onclick="closePhotoModal()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold">
          Tutup
        </button>
      </div>
    </div>
  </div>

  @if ($contactFormEnabled)
    <div id="contact-modal" role="dialog" aria-modal="true" aria-labelledby="contact-modal-title" class="modal fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="modal-panel relative w-full max-w-lg max-h-[92vh] overflow-y-auto bg-white rounded-2xl border border-slate-300 p-6 sm:p-7 shadow-2xl">
        <button type="button" onclick="closeContactModal()" aria-label="Tutup" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 hover:rotate-90 text-slate-600 flex items-center justify-center transition-all duration-300">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>

        <h3 id="contact-modal-title" class="font-bold text-lg text-slate-900 pr-10">Tulis Pesan</h3>
        <p class="text-xs text-slate-600 mt-1 leading-relaxed">Pesan diterima langsung oleh orang tua / wali {{ $firstName }} dan dibalas lewat kontak yang Anda isi.</p>

        <form id="contact-form" action="{{ route('contact', absolute: false) }}" method="post" novalidate class="mt-5 space-y-3.5">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label for="contact-name" class="block text-xs font-semibold text-slate-700">Nama *</label>
              <input id="contact-name" name="name" type="text" autocomplete="name" maxlength="120" required class="contact-input mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-royal-600 focus:bg-white transition-all">
              <p data-error="name" class="hidden text-[11px] text-rose-700 mt-1"></p>
            </div>
            <div>
              <label for="contact-organization" class="block text-xs font-semibold text-slate-700">Instansi / sekolah</label>
              <input id="contact-organization" name="organization" type="text" autocomplete="organization" maxlength="150" class="contact-input mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-royal-600 focus:bg-white transition-all">
              <p data-error="organization" class="hidden text-[11px] text-rose-700 mt-1"></p>
            </div>
            <div>
              <label for="contact-phone" class="block text-xs font-semibold text-slate-700">Nomor WhatsApp</label>
              <input id="contact-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="30" placeholder="0812xxxxxxxx" class="contact-input mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-royal-600 focus:bg-white transition-all">
              <p data-error="phone" class="hidden text-[11px] text-rose-700 mt-1"></p>
            </div>
            <div>
              <label for="contact-email" class="block text-xs font-semibold text-slate-700">Email</label>
              <input id="contact-email" name="email" type="email" autocomplete="email" maxlength="150" class="contact-input mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-royal-600 focus:bg-white transition-all">
              <p data-error="email" class="hidden text-[11px] text-rose-700 mt-1"></p>
            </div>
          </div>
          <p class="text-[11px] text-slate-500 -mt-1">Isi salah satu: nomor WhatsApp atau email.</p>

          <div>
            <label for="contact-topic" class="block text-xs font-semibold text-slate-700">Keperluan *</label>
            <select id="contact-topic" name="topic" required class="contact-input mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-royal-600 focus:bg-white transition-all">
              @foreach ($contactTopics as $value => $topicLabel)
                <option value="{{ $value }}">{{ $topicLabel }}</option>
              @endforeach
            </select>
            <p data-error="topic" class="hidden text-[11px] text-rose-700 mt-1"></p>
          </div>

          <div>
            <label for="contact-message" class="block text-xs font-semibold text-slate-700">Pesan *</label>
            <textarea id="contact-message" name="message" rows="4" maxlength="2000" required class="contact-input mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-royal-600 focus:bg-white transition-all" placeholder="Ceritakan singkat acara atau keperluan Anda."></textarea>
            <p data-error="message" class="hidden text-[11px] text-rose-700 mt-1"></p>
          </div>

          <div>
            <label for="contact-attachments" class="block text-xs font-semibold text-slate-700">Lampiran</label>
            <input id="contact-attachments" name="attachments[]" type="file" multiple accept="image/jpeg,image/png,image/webp,application/pdf"
              data-max-files="{{ \App\Models\ContactMessage::MAX_ATTACHMENTS }}" data-max-kb="{{ \App\Models\ContactMessage::MAX_ATTACHMENT_KB }}"
              class="mt-1 block w-full text-xs text-slate-600 file:mr-3 file:px-3.5 file:py-2 file:rounded-xl file:border file:border-solid file:border-slate-300 file:bg-white file:text-slate-700 file:text-xs file:font-semibold file:cursor-pointer hover:file:bg-slate-50 cursor-pointer">
            <p class="text-[11px] text-slate-500 mt-1">Opsional, mis. surat undangan atau poster acara. Foto atau PDF, maks. {{ \App\Models\ContactMessage::MAX_ATTACHMENTS }} berkas, 5 MB per berkas.</p>
            <ul id="contact-attachment-list" class="hidden mt-1.5 space-y-1 text-[11px] text-slate-600"></ul>
            <p data-error="attachments" class="hidden text-[11px] text-rose-700 mt-1"></p>
          </div>

          {{-- Jebakan bot: manusia tidak melihat kolom ini. --}}
          <div class="absolute -left-[9999px]" aria-hidden="true">
            <label for="contact-ref">Kode rujukan</label>
            <input id="contact-ref" name="contact_ref" type="text" tabindex="-1" autocomplete="off">
          </div>

          <p id="contact-form-error" role="alert" class="hidden text-xs text-rose-700 bg-rose-50 border border-rose-200 rounded-xl px-3 py-2"></p>

          <div class="pt-1 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <button type="button" onclick="closeContactModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">Batal</button>
            <button type="submit" id="contact-submit" class="px-6 py-2.5 bg-slate-900 hover:bg-royal-700 disabled:opacity-60 text-white rounded-xl text-xs font-semibold shadow-sm transition-colors">Kirim Pesan</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  <div id="download-modal" role="dialog" aria-modal="true" aria-labelledby="download-modal-title" class="modal fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="modal-panel relative w-full max-w-md bg-white rounded-2xl border border-slate-300 p-6 text-center space-y-4 shadow-2xl">
      <div class="w-12 h-12 rounded-xl bg-slate-100 text-royal-700 mx-auto flex items-center justify-center">
        <i data-lucide="file-text" class="w-6 h-6"></i>
      </div>
      <div>
        <h3 id="download-modal-title" class="font-bold text-lg text-slate-900">{{ $content['resume_title'] }}</h3>
        @if ($content['resume_body'] !== '')
          <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $content['resume_body'] }}</p>
        @endif
      </div>
      <div class="pt-2 flex flex-col gap-2">
        @if ($hasResume)
          <a href="{{ route('resume') }}" data-track="resume" target="_blank" rel="noopener" onclick="closeDownloadModal()" class="w-full py-2.5 bg-slate-900 hover:bg-royal-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-colors">
            Unduh PDF ({{ Number::fileSize($resumeSize, 1) }})
          </a>
        @endif
        <button onclick="closeDownloadModal()" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">
          Batal
        </button>
      </div>
    </div>
  </div>

  <script>window.SITE = {{ Js::from($payload) }};</script>
  <script src="{{ $asset('site.js') }}"></script>
  @if ($trackVisits)
    <script src="{{ $asset('track.js') }}" data-endpoint="{{ route('track', absolute: false) }}"></script>
  @endif
</body>
</html>
