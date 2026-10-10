// Seluruh data halaman dikirim server lewat window.SITE (lihat resources/views/site/home.blade.php).
const SITE = window.SITE;
const certDatabase = SITE.certs;
const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

const motionOK = document.documentElement.classList.contains('js-anim');
const finePointer = matchMedia('(hover: hover) and (pointer: fine)').matches;
const EASE = 'cubic-bezier(0.16, 1, 0.3, 1)';
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

function movePill(pill, target) {
  if (!pill || !target) return;
  pill.style.width = target.offsetWidth + 'px';
  pill.style.height = target.offsetHeight + 'px';
  pill.style.transform = `translate(${target.offsetLeft}px, ${target.offsetTop}px)`;
  pill.style.opacity = 1;
  requestAnimationFrame(() => pill.classList.add('is-ready'));
}

/* ----------------------------------------------------
   2. FILTERING & SEARCHING
----------------------------------------------------- */
let currentCategory = 'all';
let currentStat = null;
let currentSearch = '';
let achievementView = 'grid';

// `photo` indexes galleryDatabase, `loc` keys mapLocations, `key` keys certDatabase.
const achievements = SITE.achievements;

const ACH_LEVEL = SITE.levels;
const TALLY = [
  { stat: 'emas', label: 'Juara 1 & Emas', icon: 'trophy', tone: 'text-amber-600 bg-amber-50' },
  { stat: 'perak', label: 'Medali Perak', icon: 'medal', tone: 'text-slate-600 bg-slate-100' },
  { stat: 'internasional', label: 'Tingkat Internasional', icon: 'globe', tone: 'text-royal-700 bg-royal-50' },
  { stat: 'nasional', label: 'Tingkat Nasional', icon: 'flag', tone: 'text-emerald-700 bg-emerald-50' }
];
const hasStat = (a, stat) => a.medal === stat || a.level === stat;
const achThumb = a => esc(a.img || SITE.placeholder);

function achievementCard(a, peek) {
  const f = a.featured;
  return `
    <article class="achievement-card clean-card rounded-2xl overflow-hidden flex flex-col cursor-pointer ${f ? 'md:col-span-2 lg:col-span-3 md:flex-row' : ''} ${peek ? 'is-in' : ''}" data-key="${a.key}">
      <div class="ach-thumb relative shrink-0 bg-slate-100 overflow-hidden aspect-[16/9] ${f ? 'md:aspect-auto md:w-5/12 md:min-h-[18rem]' : ''}">
        <img src="${achThumb(a)}" alt="" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
        <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-white/95 text-slate-900 shadow-sm">
          <i data-lucide="${esc(a.icon)}" class="ach-ico w-3.5 h-3.5 ${a.toneClass}"></i>${esc(a.badge)}
        </span>
        <span class="absolute top-3 right-3 px-2 py-1 rounded-lg text-xs font-bold bg-slate-900/80 text-white tabular-nums">${a.when.slice(0, 4)}</span>
      </div>
      <div class="flex flex-col flex-1 min-w-0 p-5 ${f ? 'md:p-8 md:justify-center' : ''}">
        <div class="flex-1 ${f ? 'md:flex-none' : ''}">
          ${f ? '<p class="text-[11px] font-bold uppercase tracking-wider text-amber-700 mb-2">Prestasi Tertinggi</p>' : ''}
          <h3 class="font-bold text-slate-900 leading-snug ${f ? 'text-lg md:text-2xl tracking-tight' : 'text-base'}">${esc(a.title)}</h3>
          <p class="text-xs text-slate-500 mt-1">${esc(a.org)}</p>
          <p class="text-slate-600 leading-relaxed mt-3 ${f ? 'text-xs md:text-sm' : 'text-xs'}">${esc(a.desc)}</p>
          <div class="flex flex-wrap gap-1.5 mt-3 text-[11px] text-slate-600">
            ${a.tags.map(t => `<span class="px-2 py-0.5 bg-slate-100 rounded">${esc(t)}</span>`).join('')}
          </div>
        </div>
        <div class="pt-4 mt-5 border-t border-slate-100 flex items-center justify-between gap-3 text-xs">
          <span class="font-mono text-slate-500 text-[11px] truncate">${a.no ? '#' + esc(a.no) : ''}</span>
          <span class="flex items-center gap-3 shrink-0">
            ${a.loc ? `<button type="button" data-loc="${a.loc}" class="tap font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1 transition-colors"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i><span>Lokasi</span></button>` : ''}
            <button type="button" class="tap font-semibold text-royal-700 hover:text-royal-900 flex items-center gap-1 transition-colors"><span>Lihat Detail</span><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></button>
          </span>
        </div>
      </div>
    </article>`;
}

function achievementRow(a) {
  return `
    <li>
      <button type="button" class="ach-tl-row clean-card rounded-2xl p-3 w-full flex items-center gap-3.5 text-left" data-key="${a.key}">
        <span class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden bg-slate-100 shrink-0"><img src="${achThumb(a)}" alt="" loading="lazy" class="w-full h-full object-cover"></span>
        <span class="flex-1 min-w-0">
          <span class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px]">
            <span class="inline-flex items-center gap-1 font-bold text-slate-900"><i data-lucide="${esc(a.icon)}" class="w-3.5 h-3.5 ${a.toneClass}"></i>${esc(a.badge)}</span>
            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold">${esc(ACH_LEVEL[a.level])}</span>
            <span class="text-slate-500">${esc(certDatabase[a.key].date)}</span>
          </span>
          <span class="block font-bold text-sm text-slate-900 leading-snug mt-1">${esc(a.title)}</span>
          <span class="block text-xs text-slate-500 mt-0.5 truncate">${esc(a.org)}</span>
        </span>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 shrink-0"></i>
      </button>
    </li>`;
}

function renderAchievements() {
  const grid = $('#achievements-grid');
  const timeline = $('#achievements-timeline');
  // Stagger-reveal waits for each card to scroll in, which would hide the peeking next card of the phone carousel.
  const peek = matchMedia('(max-width: 767.98px)').matches;
  grid.innerHTML = achievements.map(a => achievementCard(a, peek)).join('');

  const byDate = [...achievements].sort((a, b) => a.when.localeCompare(b.when));
  const years = [...new Set(byDate.map(a => a.when.slice(0, 4)))];
  timeline.innerHTML = years.map(year => `
    <li class="ach-tl-year">
      <p class="inline-flex px-3 py-1 rounded-full bg-slate-900 text-white text-xs font-bold tabular-nums">${year}</p>
      <ol class="ach-tl-list space-y-3 mt-3">${byDate.filter(a => a.when.startsWith(year)).map(achievementRow).join('')}</ol>
    </li>`).join('');

  $('#tally').innerHTML = TALLY.map(t => {
    const n = achievements.filter(a => hasStat(a, t.stat)).length;
    return `
      <button type="button" class="tally-btn bg-white rounded-2xl border border-slate-200 p-3 sm:p-3.5 flex items-center gap-3 text-left transition-all" data-stat="${t.stat}" aria-pressed="false">
        <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ${t.tone}"><i data-lucide="${esc(t.icon)}" class="w-5 h-5"></i></span>
        <span class="min-w-0">
          <span class="block text-xl font-extrabold text-slate-900 tabular-nums leading-none" data-count="${n}">${n}</span>
          <span class="block text-[11px] font-medium text-slate-500 leading-tight mt-1">${t.label}</span>
        </span>
      </button>`;
  }).join('');

  $$('.cat-count').forEach(el => {
    const cat = el.dataset.cat;
    el.textContent = cat === 'all' ? achievements.length : achievements.filter(a => a.cat.includes(cat)).length;
  });

  $('#tally').addEventListener('click', e => {
    const btn = e.target.closest('.tally-btn');
    if (btn) setStat(btn.dataset.stat === currentStat ? null : btn.dataset.stat);
  });
  const open = e => {
    const loc = e.target.closest('[data-loc]');
    if (loc) { goToLocation(loc.dataset.loc); return; }
    const item = e.target.closest('[data-key]');
    if (item) openCertificateModal(item.dataset.key);
  };
  grid.addEventListener('click', open);
  timeline.addEventListener('click', open);

  let queued = false;
  grid.addEventListener('scroll', () => {
    if (queued) return;
    queued = true;
    requestAnimationFrame(() => { queued = false; updateCarouselPos(); });
  }, { passive: true });
}

function updateCarouselPos() {
  const grid = $('#achievements-grid');
  const cards = $$('.achievement-card:not(.hidden)', grid);
  const mid = grid.getBoundingClientRect().left + grid.clientWidth / 2;
  let nearest = 0;
  cards.forEach((card, i) => {
    const r = card.getBoundingClientRect();
    const best = cards[nearest].getBoundingClientRect();
    if (Math.abs(r.left + r.width / 2 - mid) < Math.abs(best.left + best.width / 2 - mid)) nearest = i;
  });
  $('#carousel-pos-text').textContent = cards.length ? `${nearest + 1}/${cards.length}` : '0/0';
}

function filterCategory(cat, btn) {
  currentCategory = cat;
  $$('.category-btn').forEach(b => {
    b.classList.toggle('active', b === btn);
    b.setAttribute('aria-pressed', b === btn);
  });
  movePill($('#tab-pill'), btn);
  runFilters();
}

function setStat(stat) {
  currentStat = stat;
  $$('.tally-btn').forEach(b => b.setAttribute('aria-pressed', b.dataset.stat === stat));
  runFilters();
}

function setAchievementView(view) {
  achievementView = view;
  $$('.ach-view-btn').forEach(b => b.setAttribute('aria-pressed', b.dataset.view === view));
  $('#achievements-grid').classList.toggle('hidden', view !== 'grid');
  $('#achievements-timeline').classList.toggle('hidden', view !== 'timeline');
  $('#carousel-pos').classList.toggle('hidden', view !== 'grid');
  if (motionOK) {
    const shown = view === 'grid' ? $('#achievements-grid') : $('#achievements-timeline');
    shown.animate([{ opacity: 0, transform: 'translateY(10px)' }, { opacity: 1, transform: 'none' }], { duration: 320, easing: EASE });
  }
}

function jumpToCategory(cat) {
  const pill = document.querySelector(`.category-btn[onclick*="'${cat}'"]`);
  if (pill) {
    setStat(null);
    filterCategory(cat, pill);
  } else {
    filterCategory('all', $('.category-btn'));
    setStat(TALLY.some(t => t.stat === cat) ? cat : null);
  }
  if (showcaseView !== 'prestasi') switchShowcaseTab('prestasi');
  const el = document.getElementById('prestasi');
  if (el) el.scrollIntoView({ behavior: motionOK ? 'smooth' : 'auto' });
}

function goToLocation(key) {
  selectLocation(key);
  $('#school-map').scrollIntoView({ behavior: motionOK ? 'smooth' : 'auto', block: 'center' });
}

function handleSearch(val) {
  currentSearch = val.toLowerCase().trim();
  runFilters();
}

function runFilters() {
  const cards = $$('#achievements-grid .achievement-card');
  const before = new Map();
  cards.forEach(card => {
    if (!card.classList.contains('hidden')) before.set(card, card.getBoundingClientRect());
  });

  const visible = new Set(achievements.filter(a => {
    const text = [a.title, a.org, a.desc, a.badge, a.no, a.kw, a.when.slice(0, 4), ...a.tags].join(' ').toLowerCase();
    return (currentCategory === 'all' || a.cat.includes(currentCategory))
      && (!currentStat || hasStat(a, currentStat))
      && (!currentSearch || text.includes(currentSearch));
  }).map(a => a.key));
  const count = visible.size;

  cards.forEach(card => card.classList.toggle('hidden', !visible.has(card.dataset.key)));
  $$('#achievements-timeline .ach-tl-row').forEach(row => row.parentElement.classList.toggle('hidden', !visible.has(row.dataset.key)));
  $$('#achievements-timeline .ach-tl-year').forEach(group => {
    group.classList.toggle('hidden', !$('.ach-tl-list > li:not(.hidden)', group));
  });
  $('#achievements-grid').scrollLeft = 0;
  updateCarouselPos();

  if (motionOK && achievementView === 'grid') {
    let entering = 0;
    cards.forEach(card => {
      if (card.classList.contains('hidden')) return;
      const from = before.get(card);
      if (!from) {
        card.classList.add('is-in');
        card.animate(
          [{ opacity: 0, transform: 'scale(0.94)' }, { opacity: 1, transform: 'none' }],
          { duration: 380, delay: entering++ * 45, easing: EASE, fill: 'backwards' }
        );
        return;
      }
      const to = card.getBoundingClientRect();
      const dx = from.left - to.left;
      const dy = from.top - to.top;
      if (dx || dy) {
        card.animate(
          [{ transform: `translate(${dx}px, ${dy}px)` }, { transform: 'none' }],
          { duration: 480, easing: EASE }
        );
      }
    });
  }

  const counter = $('#visible-count');
  if (counter.textContent !== String(count)) {
    counter.textContent = count;
    if (motionOK) {
      counter.animate(
        [{ transform: 'translateY(-60%)', opacity: 0 }, { transform: 'none', opacity: 1 }],
        { duration: 320, easing: EASE }
      );
    }
  }
  $('#no-results').classList.toggle('hidden', count !== 0);
}

function resetFilters() {
  $('#achievement-search').value = '';
  currentSearch = '';
  currentStat = null;
  $$('.tally-btn').forEach(b => b.setAttribute('aria-pressed', 'false'));
  filterCategory('all', $('.category-btn'));
}

/* ----------------------------------------------------
   3. MODALS
----------------------------------------------------- */
let activeModal = null;
let lastFocus = null;

function openModal(modal) {
  if (activeModal === modal) return;
  lastFocus = document.activeElement;
  activeModal = modal;
  modal.classList.remove('hidden');
  void modal.offsetWidth;
  modal.classList.add('is-open');
  document.documentElement.classList.add('modal-lock');
  const first = $('button', modal);
  if (first) first.focus({ preventScroll: true });
  // Otherwise the browser restores the old scroll position on the pop and undoes a jump queued in afterClose.
  history.scrollRestoration = 'manual';
  history.pushState({ modal: true }, '');
}

function closeModal(modal) {
  if (!modal.classList.contains('is-open')) return;
  modal.classList.remove('is-open');
  document.documentElement.classList.remove('modal-lock');
  activeModal = null;
  setTimeout(() => {
    if (!modal.classList.contains('is-open')) modal.classList.add('hidden');
  }, 260);
  if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
  // Opening pushed a history entry so the system back button closes the modal.
  // Closed any other way, that entry has to be popped or back would do nothing once.
  if (history.state && history.state.modal) history.back();
  else flushAfterClose();
}

// Anything that scrolls or opens another modal right after a close must wait for the pop above.
let afterClose = null;
function flushAfterClose() {
  const fn = afterClose;
  afterClose = null;
  if (fn) requestAnimationFrame(fn);
}

function openCertificateModal(key) {
  const item = certDatabase[key];
  if (!item) return;
  const modal = $('#cert-modal');
  const ach = achievements.find(a => a.key === key);
  const photo = ach && ach.photo !== null ? galleryDatabase[ach.photo] : null;

  $('#modal-content').innerHTML = `
    ${photo ? `
    <button type="button" onclick="viewAchievementPhoto(${ach.photo})" class="group relative block w-full aspect-[16/7] rounded-xl overflow-hidden bg-slate-100 mb-4 text-left">
      <img src="${esc(photo.img)}" alt="${esc(photo.title)}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
      <span class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-transparent to-transparent"></span>
      <span class="absolute left-3 right-3 bottom-3 flex items-end justify-between gap-3 text-white">
        <span class="text-xs font-semibold leading-snug">${esc(photo.title)}</span>
        <span class="shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white/95 text-slate-900 text-[11px] font-bold"><i data-lucide="image" class="w-3.5 h-3.5"></i>Lihat Foto</span>
      </span>
    </button>` : (ach && ach.img ? `
    <div class="relative w-full aspect-[16/7] rounded-xl overflow-hidden bg-slate-100 mb-4">
      <img src="${esc(ach.img)}" alt="" class="absolute inset-0 w-full h-full object-cover">
    </div>` : '')}
    <div class="cert-frame bg-white p-6 sm:p-8 text-center space-y-4">
      <div class="cert-seal w-12 h-12 mx-auto rounded-full bg-slate-900 text-white flex items-center justify-center">
        <i data-lucide="award" class="w-6 h-6 text-amber-400"></i>
      </div>
      <div style="--n:0">
        <span class="text-[10px] tracking-wider uppercase font-semibold text-slate-500">Salinan Dokumen Piagam Resmi</span>
        <h3 id="cert-modal-title" class="font-bold text-lg sm:text-xl text-slate-900 mt-1">${esc(item.title)}</h3>
        <p class="text-xs text-royal-700 font-semibold mt-0.5">${esc(item.org)}</p>
      </div>
      <div style="--n:1" class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700 leading-relaxed max-w-lg mx-auto">
        ${esc(item.desc)}
      </div>
      <div style="--n:2" class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 text-left border-t border-slate-200 text-xs">
        <div>
          <span class="block text-[10px] text-slate-500">Nomor Registrasi:</span>
          <span class="font-mono font-bold text-slate-900 break-all">${esc(item.number || '-')}</span>
        </div>
        <div>
          <span class="block text-[10px] text-slate-500">Tanggal Pengesahan:</span>
          <span class="font-medium text-slate-900">${esc(item.date)}</span>
        </div>
        <div class="col-span-2 sm:col-span-1">
          <span class="block text-[10px] text-slate-500">Predikat:</span>
          <span class="font-bold text-emerald-800">${esc(item.predikat || '-')}</span>
        </div>
      </div>
      <div style="--n:3" class="flex flex-wrap gap-1.5 justify-center pt-1">
        ${item.tags.map(t => `<span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] text-slate-700 border border-slate-200">${esc(t)}</span>`).join('')}
      </div>
    </div>
    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
      <span class="flex items-center gap-1.5">
        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
        <span>Arsip fisik tersimpan rapi dan dapat ditunjukkan bila diperlukan.</span>
      </span>
      <span class="w-full sm:w-auto flex flex-col sm:flex-row gap-2 shrink-0">
        ${ach && ach.loc ? `<button onclick="showAchievementOnMap('${ach.loc}')" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold border border-slate-300 flex items-center justify-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>Lihat Lokasi</button>` : ''}
        <button onclick="closeCertificateModal()" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold">
          Tutup
        </button>
      </span>
    </div>
  `;

  openModal(modal);
  if (window.lucide) { lucide.createIcons(); }

  // Confetti is reserved for first-place results; runner-up certificates only get the stamp.
  if (ach && ach.medal === 'emas') {
    setTimeout(() => {
      const seal = $('.cert-seal', modal);
      if (!seal || activeModal !== modal) return;
      const r = seal.getBoundingClientRect();
      burstConfetti(r.left + r.width / 2, r.top + r.height / 2);
    }, 520);
  }
}

function viewAchievementPhoto(index) {
  afterClose = () => openPhotoModalByIndex(index);
  closeCertificateModal();
}

function showAchievementOnMap(key) {
  afterClose = () => goToLocation(key);
  closeCertificateModal();
}

function closeCertificateModal() {
  closeModal($('#cert-modal'));
}

/* ----------------------------------------------------
   4. PETA LOKASI INTERAKTIF & VENUE KOMPETISI
      (100% GRATIS & TANPA API KEY APAPUN)
----------------------------------------------------- */
const mapLocations = SITE.locations;

let schoolMap = null;
let currentMapKey = SITE.defaultLocation;
let mapMarkers = {};

function renderOsmEmbed(key) {
  const loc = mapLocations[key];
  const mapContainer = document.getElementById('school-map');
  if (!mapContainer || !loc) return;
  mapContainer.innerHTML = `
    <iframe src="${esc(loc.osmEmbedUrl)}" width="100%" height="100%" style="border:0;" loading="lazy" title="Peta Lokasi ${esc(loc.name)}"></iframe>
  `;
}

function initSchoolMap() {
  const mapContainer = document.getElementById('school-map');
  if (!mapContainer || !mapLocations[SITE.defaultLocation]) return;

  // Dual-Engine: If Leaflet library is not available, automatically render OpenStreetMap embed iframe
  if (typeof L === 'undefined') {
    renderOsmEmbed(SITE.defaultLocation);
    return;
  }

  try {
    // Initialize Leaflet map (100% Free, NO API Key Needed)
    schoolMap = L.map('school-map', {
      center: [mapLocations[SITE.defaultLocation].lat, mapLocations[SITE.defaultLocation].lng],
      zoom: 15,
      scrollWheelZoom: false,
      zoomControl: true
    });

    // JANGAN pakai tile.openstreetmap.org atau CARTO: tanpa header Referer (halaman dibuka
    // lewat file://) yang pertama membalas 403, yang kedua gambar "API KEY REQUIRED".
    const tileLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; <a href="https://www.esri.com/" target="_blank" rel="noopener noreferrer">Esri</a> &mdash; Esri, HERE, Garmin, OpenStreetMap contributors',
      maxZoom: 18
    });

    tileLayer.once('tileerror', function() {
      schoolMap.removeLayer(tileLayer);
      L.tileLayer('https://tile.openstreetmap.de/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors',
        maxZoom: 18
      }).addTo(schoolMap);
    });

    tileLayer.addTo(schoolMap);

    // Add pins for all locations
    Object.keys(mapLocations).forEach(key => {
      const loc = mapLocations[key];
      const icon = L.divIcon({
        className: 'custom-leaflet-marker',
        html: `
          <div class="pin-marker" style="--pin-color: ${esc(loc.color)};">
            <div class="pin-dot"></div>
            <div class="pin-pulse"></div>
          </div>
        `,
        iconSize: [28, 28],
        iconAnchor: [14, 14]
      });

      const marker = L.marker([loc.lat, loc.lng], { icon: icon }).addTo(schoolMap);
      marker.bindPopup(`
        <div class="p-1 space-y-1.5 font-sans" style="min-width: 200px;">
          <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold text-white" style="background-color: ${esc(loc.color)};">${esc(loc.badge)}</span>
          <h5 class="text-xs font-bold text-slate-900 leading-snug">${esc(loc.name)}</h5>
          <p class="text-[11px] text-slate-600 line-clamp-2">${esc(loc.address)}</p>
          <div class="pt-1 flex items-center justify-between border-t border-slate-100">
            <button onclick="selectLocation('${key}')" class="tap text-[11px] font-bold text-royal-700 hover:underline">Lihat Rincian →</button>
            <a href="${esc(loc.navUrl)}" target="_blank" rel="noopener noreferrer" class="tap text-[11px] text-slate-500 hover:text-slate-900">Google Maps ↗</a>
          </div>
        </div>
      `);
      marker.on('click', () => selectLocation(key));
      mapMarkers[key] = marker;
    });

    // Set initial view
    selectLocation(SITE.defaultLocation, false);

    // Resize fix on layout shift
    setTimeout(() => {
      if (schoolMap) schoolMap.invalidateSize();
    }, 300);
  } catch (err) {
    console.warn('Leaflet error, using OpenStreetMap embed fallback:', err);
    renderOsmEmbed(SITE.defaultLocation);
  }
}

function selectLocation(key, fly = true) {
  const loc = mapLocations[key];
  if (!loc) return;
  currentMapKey = key;

  // Update Left Education Cards Active State
  $$('.edu-card').forEach(card => {
    const isTarget = card.dataset.mapKey === key;
    card.classList.toggle('border-royal-600', isTarget);
    card.classList.toggle('bg-royal-50/20', isTarget);
    card.classList.toggle('border-transparent', !isTarget);
    card.classList.toggle('bg-white', !isTarget);

    const statusEl = card.querySelector('.edu-card-status');
    if (statusEl) {
      statusEl.innerHTML = isTarget
        ? '<i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i><span>Ditampilkan di Peta</span>'
        : '<i data-lucide="map-pin" class="w-3.5 h-3.5"></i><span>Lihat di Peta</span>';
      statusEl.classList.toggle('text-royal-700', isTarget);
      statusEl.classList.toggle('font-bold', isTarget);
      statusEl.classList.toggle('text-slate-500', !isTarget);
      statusEl.classList.toggle('font-medium', !isTarget);
    }
  });
  if (window.lucide && typeof lucide.createIcons === 'function') {
    lucide.createIcons();
  }

  // Update Floating Label
  const floatLabel = $('#map-floating-label');
  if (floatLabel) floatLabel.textContent = loc.name;

  // Update Right Detail Card
  const locName = $('#loc-name');
  const locSubtitle = $('#loc-subtitle');
  const locBadge = $('#loc-badge');
  const locStatus = $('#loc-status');
  const locPhoto = $('#loc-photo');
  const locCaption = $('#loc-photo-caption');
  const locAddress = $('#loc-address');
  const locNav = $('#loc-nav-link');

  if (locName) locName.textContent = loc.name;
  if (locSubtitle) locSubtitle.textContent = loc.subtitle;
  if (locBadge) locBadge.textContent = loc.badge;
  if (locStatus) locStatus.textContent = loc.status;
  if (locPhoto) {
    locPhoto.src = loc.photo;
    locPhoto.alt = loc.name;
  }
  if (locCaption) locCaption.textContent = loc.caption;
  if (locAddress) locAddress.textContent = loc.address;
  if (locNav) locNav.href = loc.navUrl;

  // Fly map smoothly to location
  if (schoolMap) {
    if (fly) {
      schoolMap.flyTo([loc.lat, loc.lng], loc.zoom, { duration: 1.1 });
    } else {
      schoolMap.setView([loc.lat, loc.lng], loc.zoom);
    }
  } else {
    renderOsmEmbed(key);
  }
}

function resetMapView() {
  selectLocation(SITE.defaultLocation);
}

function jumpToMap(key) {
  const sec = document.getElementById('lokasi');
  if (sec) {
    sec.scrollIntoView({ behavior: 'smooth' });
    setTimeout(() => {
      selectLocation(key);
    }, 400);
  }
}

function copyEmailAddress() {
  navigator.clipboard.writeText(SITE.email).then(() => {
    showToast(`Email ${SITE.email} berhasil disalin ke clipboard!`);
  }).catch(() => {
    showToast(`Email: ${SITE.email}`);
  });
}

/* ----------------------------------------------------
   5. GALERI FOTO KEGIATAN & ALBUM 2.0
----------------------------------------------------- */
const galleryDatabase = SITE.gallery;

let currentGalleryCategory = 'all';
let currentPhotoIdx = 0;

function switchShowcaseTab(tab) {
  const pPrestasi = $('#panel-prestasi');
  const pGaleri = $('#panel-galeri');
  const btnP = $('#switch-tab-prestasi');
  const btnG = $('#switch-tab-galeri');

  [btnP, btnG].forEach(btn => {
    if (!btn) return;
    const isTarget = btn.id === `switch-tab-${tab}`;
    btn.classList.toggle('active', isTarget);
    btn.classList.toggle('bg-slate-900', isTarget);
    btn.classList.toggle('text-white', isTarget);
    btn.classList.toggle('shadow-sm', isTarget);
    btn.classList.toggle('font-bold', isTarget);
    btn.classList.toggle('font-semibold', !isTarget);
    btn.classList.toggle('text-slate-600', !isTarget);
    btn.classList.toggle('hover:text-slate-900', !isTarget);
    btn.setAttribute('aria-pressed', isTarget);
  });

  if (tab === 'prestasi') {
    if (pPrestasi) pPrestasi.classList.remove('hidden');
    if (pGaleri) pGaleri.classList.add('hidden');
  } else if (tab === 'galeri') {
    if (pPrestasi) pPrestasi.classList.add('hidden');
    if (pGaleri) pGaleri.classList.remove('hidden');
  }

  if (window.lucide && typeof lucide.createIcons === 'function') {
    lucide.createIcons();
  }
  showcaseView = tab;
  movePill($('#tab-pill'), $('.category-btn.active'));
  syncTabbar();
}

// Hash navigation listener for #galeri & #prestasi
window.addEventListener('hashchange', () => {
  if (window.location.hash === '#galeri') {
    switchShowcaseTab('galeri');
  } else if (window.location.hash === '#prestasi') {
    switchShowcaseTab('prestasi');
  }
});
if (window.location.hash === '#galeri') {
  setTimeout(() => switchShowcaseTab('galeri'), 150);
}

function filterGalleryCategory(cat, btn) {
  currentGalleryCategory = cat;
  $$('.gal-filter-btn').forEach(b => {
    b.classList.toggle('active', b === btn);
    b.classList.toggle('bg-slate-900', b === btn);
    b.classList.toggle('text-white', b === btn);
    b.classList.toggle('text-slate-600', b !== btn);
  });

  const cards = $$('.gallery-card');
  cards.forEach(card => {
    const itemCat = card.dataset.category;
    const matches = cat === 'all' || itemCat === cat;
    card.style.display = matches ? '' : 'none';
    if (matches && motionOK) {
      card.animate([{ opacity: 0, transform: 'scale(0.96)' }, { opacity: 1, transform: 'none' }], {
        duration: 320,
        easing: EASE
      });
    }
  });
}

function openPhotoModalByIndex(index) {
  if (index < 0 || index >= galleryDatabase.length) return;
  currentPhotoIdx = index;
  const item = galleryDatabase[index];
  const modal = $('#photo-modal');
  const img = $('#photo-modal-img');
  const wasOpen = modal.classList.contains('is-open');

  img.dataset.want = item.img;
  img.src = item.img;
  img.alt = item.title;

  $('#photo-modal-title').textContent = item.title;
  $('#photo-modal-desc').textContent = item.desc;
  $('#photo-modal-badge').textContent = item.badge;
  $('#photo-modal-meta').textContent = item.meta;
  $('#photo-counter').textContent = `${index + 1} / ${galleryDatabase.length}`;

  // Populate Thumbnail Strip
  const strip = $('#photo-thumb-strip');
  if (strip) {
    strip.innerHTML = galleryDatabase.map((it, i) => `
      <button type="button" onclick="openPhotoModalByIndex(${i})" class="thumb-btn w-12 h-12 rounded-lg overflow-hidden shrink-0 border border-slate-200 ${i === index ? 'is-active ring-2 ring-royal-600' : ''}" title="${esc(it.title)}">
        <img src="${esc(it.img)}" alt="${esc(it.title)}" class="w-full h-full object-cover">
      </button>
    `).join('');
  }

  if (wasOpen) {
    if (motionOK) {
      img.animate(
        [{ opacity: 0, transform: `translateX(${photoDir * 28}px)` }, { opacity: 1, transform: 'none' }],
        { duration: 340, easing: EASE }
      );
    }
  } else {
    openModal(modal);
  }
}

function stepPhoto(dir) {
  photoDir = dir;
  const nextIdx = (currentPhotoIdx + dir + galleryDatabase.length) % galleryDatabase.length;
  openPhotoModalByIndex(nextIdx);
}

function closePhotoModal() {
  closeModal($('#photo-modal'));
}

function shareActivePhoto() {
  const item = galleryDatabase[currentPhotoIdx];
  if (!item) return;
  const text = `${esc(item.title)} (${item.meta}) - Portofolio ${SITE.name}`;
  navigator.clipboard.writeText(text).then(() => {
    showToast('Keterangan dokumentasi kegiatan berhasil disalin ke clipboard!');
  }).catch(() => {
    showToast('Tautan foto disalin.');
  });
}

/* ----------------------------------------------------
   6. CV DOWNLOAD & WHATSAPP
----------------------------------------------------- */
function downloadPortfolioModal() {
  openModal($('#download-modal'));
}

function closeDownloadModal() {
  closeModal($('#download-modal'));
}

function openContactModal() {
  const modal = $('#contact-modal');
  if (!modal) return;
  openModal(modal);
  $('#contact-name').focus({ preventScroll: true });
}

function closeContactModal() {
  closeModal($('#contact-modal'));
}

function initContactForm() {
  const form = $('#contact-form');
  if (!form) return;
  const submit = $('#contact-submit');
  const summary = $('#contact-form-error');

  const files = $('#contact-attachments');
  const fileList = $('#contact-attachment-list');
  const formatSize = bytes => bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';

  // Diperiksa di browser dulu supaya pengunjung tidak menunggu unggahan besar hanya untuk ditolak server.
  const attachmentProblem = () => {
    const chosen = [...files.files];
    if (chosen.length > Number(files.dataset.maxFiles)) return `Lampiran paling banyak ${files.dataset.maxFiles} berkas.`;
    const tooBig = chosen.find(file => file.size > Number(files.dataset.maxKb) * 1024);
    return tooBig ? `"${tooBig.name}" lebih dari 5 MB. Pilih berkas yang lebih kecil.` : '';
  };
  const renderFiles = () => {
    fileList.innerHTML = [...files.files].map(file => `<li class="flex items-center justify-between gap-3"><span class="truncate">${esc(file.name)}</span><span class="shrink-0 tabular-nums text-slate-500">${formatSize(file.size)}</span></li>`).join('');
    fileList.classList.toggle('hidden', files.files.length === 0);
  };
  files.addEventListener('change', () => {
    renderFiles();
    const problem = attachmentProblem();
    showErrors(problem ? { attachments: [problem] } : {});
  });
  form.addEventListener('reset', () => setTimeout(renderFiles));

  const showErrors = errors => {
    // Error per berkas datang sebagai "attachments.0", "attachments.1", dst.
    const fileError = Object.keys(errors).find(key => key.startsWith('attachments.'));
    if (fileError && !errors.attachments) errors = { ...errors, attachments: errors[fileError] };

    $$('[data-error]', form).forEach(el => {
      const messages = errors[el.dataset.error];
      el.textContent = messages ? messages[0] : '';
      el.classList.toggle('hidden', !messages);
      const input = form.elements[el.dataset.error];
      if (input) input.setAttribute('aria-invalid', messages ? 'true' : 'false');
    });
  };
  const showSummary = message => {
    summary.textContent = message || '';
    summary.classList.toggle('hidden', !message);
  };

  // Nomor WhatsApp dan email saling menggantikan, jadi mengisi salah satunya menghapus error keduanya.
  form.addEventListener('input', e => {
    if (e.target === files) return;
    const names = ['phone', 'email'].includes(e.target.name) ? ['phone', 'email'] : [e.target.name];
    names.forEach(name => {
      const error = $(`[data-error="${name}"]`, form);
      if (!error || error.classList.contains('hidden')) return;
      error.classList.add('hidden');
      form.elements[name].setAttribute('aria-invalid', 'false');
    });
  });

  form.addEventListener('submit', async e => {
    e.preventDefault();
    if (submit.disabled) return;
    showErrors({});
    showSummary('');
    const problem = attachmentProblem();
    if (problem) {
      showErrors({ attachments: [problem] });
      files.focus();
      return;
    }
    submit.disabled = true;
    submit.textContent = 'Mengirim...';

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: new FormData(form)
      });
      const data = await response.json().catch(() => ({}));

      if (response.ok) {
        form.reset();
        closeContactModal();
        showToast(data.message || 'Pesan terkirim.');
        window.dispatchEvent(new CustomEvent('site:message-sent'));
      } else if (response.status === 422) {
        showErrors(data.errors || {});
        const firstInvalid = $('[aria-invalid="true"]', form);
        if (firstInvalid) firstInvalid.focus();
      } else if (response.status === 413) {
        showErrors({ attachments: ['Lampiran terlalu besar untuk dikirim. Kurangi jumlah atau ukurannya.'] });
      } else if (response.status === 429) {
        showSummary('Terlalu banyak percobaan. Coba lagi sebentar lagi.');
      } else if (response.status === 419) {
        showSummary('Halaman sudah terlalu lama terbuka. Muat ulang halaman lalu kirim lagi.');
      } else {
        showSummary('Pesan belum terkirim. Coba lagi, atau hubungi lewat WhatsApp.');
      }
    } catch (err) {
      showSummary('Pesan belum terkirim karena koneksi terputus. Coba lagi.');
    } finally {
      submit.disabled = false;
      submit.textContent = 'Kirim Pesan';
    }
  });
}

let toastTimer;
function showToast(message) {
  const toast = $('#toast');
  toast.textContent = message;
  toast.classList.add('is-on');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('is-on'), 5200);
}

function burstConfetti(x, y) {
  if (!motionOK) return;
  const dpr = Math.min(window.devicePixelRatio || 1, 2);
  const canvas = document.createElement('canvas');
  canvas.className = 'confetti';
  canvas.width = innerWidth * dpr;
  canvas.height = innerHeight * dpr;
  document.body.appendChild(canvas);
  const ctx = canvas.getContext('2d');
  ctx.scale(dpr, dpr);

  const colors = ['#d97706', '#fbbf24', '#1e40af', '#0f172a'];
  const parts = Array.from({ length: 70 }, (_, i) => {
    const angle = Math.random() * Math.PI * 2;
    const speed = 4 + Math.random() * 9;
    return {
      x, y,
      vx: Math.cos(angle) * speed,
      vy: Math.sin(angle) * speed - 5,
      w: 4 + Math.random() * 5,
      h: 8 + Math.random() * 6,
      rot: Math.random() * 6,
      spin: (Math.random() - 0.5) * 0.4,
      color: colors[i % colors.length]
    };
  });

  const start = performance.now();
  (function frame(now) {
    const t = (now - start) / 1800;
    if (t >= 1) { canvas.remove(); return; }
    ctx.clearRect(0, 0, innerWidth, innerHeight);
    ctx.globalAlpha = t < 0.6 ? 1 : 1 - (t - 0.6) / 0.4;
    parts.forEach(p => {
      p.vx *= 0.985;
      p.vy = p.vy * 0.985 + 0.32;
      p.x += p.vx;
      p.y += p.vy;
      p.rot += p.spin;
      ctx.save();
      ctx.translate(p.x, p.y);
      ctx.rotate(p.rot);
      ctx.fillStyle = p.color;
      ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
      ctx.restore();
    });
    requestAnimationFrame(frame);
  })(start);
}

/* ----------------------------------------------------
   6. MOTION & MICRO-INTERACTIONS
----------------------------------------------------- */
const REVEALS = {
  up: [{ opacity: 0, transform: 'translateY(22px)' }, { opacity: 1, transform: 'none' }],
  left: [{ opacity: 0, transform: 'translateX(-28px)' }, { opacity: 1, transform: 'none' }],
  scale: [{ opacity: 0, transform: 'scale(0.95)' }, { opacity: 1, transform: 'none' }],
  wipe: [
    { clipPath: 'inset(0 0 100% 0)', transform: 'translateY(20px)' },
    { clipPath: 'inset(0 0 0 0)', transform: 'none' }
  ]
};

function initReveals() {
  const items = $$('[data-reveal], [data-stagger] > *');
  if (!motionOK) return;
  if (!('IntersectionObserver' in window)) {
    items.forEach(el => el.classList.add('is-in'));
    return;
  }
  const io = new IntersectionObserver(entries => {
    let order = 0;
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      io.unobserve(el);
      if (el.classList.contains('is-in')) return;
      const kind = el.dataset.reveal || el.parentElement.dataset.stagger;
      el.classList.add('is-in');
      el.animate(REVEALS[kind] || REVEALS.up, { duration: 750, delay: order++ * 80, easing: EASE, fill: 'backwards' });
    });
  // No ratio threshold: a block taller than a few screens never reaches one on a short phone and would stay invisible.
  }, { rootMargin: '0px 0px -6% 0px' });
  items.forEach(el => io.observe(el));
}

function initCounters() {
  if (!motionOK || !('IntersectionObserver' in window)) return;
  const io = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      io.unobserve(el);
      const end = Number(el.dataset.count);
      const suffix = el.dataset.suffix || '';
      const start = performance.now();
      (function tick(now) {
        const p = Math.min((now - start) / 1400, 1);
        el.textContent = Math.round(end * (1 - Math.pow(1 - p, 4))) + suffix;
        if (p < 1) requestAnimationFrame(tick);
      })(start);
    });
  }, { threshold: 0.6 });
  $$('[data-count]').forEach(el => io.observe(el));
}

function initScrollEffects() {
  const header = $('#site-header');
  const progress = $('#scroll-progress');
  const toTop = $('#to-top');
  const lines = $$('.tl-line');

  function update() {
    const y = window.scrollY;
    const max = document.documentElement.scrollHeight - innerHeight;
    header.classList.toggle('is-scrolled', y > 24);
    progress.style.transform = `scaleX(${max > 0 ? Math.min(y / max, 1) : 0})`;
    toTop.classList.toggle('is-on', y > 700);

    const mark = innerHeight * 0.62;
    lines.forEach(line => {
      const r = line.getBoundingClientRect();
      line.style.setProperty('--p', Math.max(0, Math.min(1, (mark - r.top) / r.height)));
    });
  }

  let queued = false;
  addEventListener('scroll', () => {
    if (queued) return;
    queued = true;
    requestAnimationFrame(() => { update(); queued = false; });
  }, { passive: true });
  addEventListener('resize', update);
  toTop.addEventListener('click', () => scrollTo({ top: 0, behavior: motionOK ? 'smooth' : 'auto' }));
  update();
}

function initScrollSpy() {
  const links = $$('.nav-link');
  const pill = $('#nav-pill');
  let current = null;

  function setActive(id) {
    current = links.find(a => a.getAttribute('href') === '#' + id) || null;
    links.forEach(a => {
      a.classList.toggle('is-active', a === current);
      if (a === current) a.setAttribute('aria-current', 'true');
      else a.removeAttribute('aria-current');
    });
    if (current) movePill(pill, current);
    else pill.style.opacity = 0;
    currentSection = id;
    syncTabbar();
  }

  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => { if (entry.isIntersecting) setActive(entry.target.id); });
    }, { rootMargin: '-45% 0px -54% 0px' });
    $$('section[id]').forEach(section => io.observe(section));
  }

  const realign = () => {
    if (current) movePill(pill, current);
    movePill($('#tab-pill'), $('.category-btn.active'));
  };
  addEventListener('resize', realign);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(realign);
}

let currentSection = 'beranda';
let showcaseView = 'prestasi';

function syncTabbar() {
  const onGallery = currentSection === 'prestasi' && showcaseView === 'galeri';
  const tab = onGallery ? 'galeri'
    : (currentSection === 'tentang' || currentSection === 'kontak') ? 'lainnya'
    : currentSection;
  $$('#tabbar .tab-btn').forEach(btn => {
    const on = btn.dataset.tab === tab;
    btn.classList.toggle('is-active', on);
    if (on) btn.setAttribute('aria-current', 'page');
    else btn.removeAttribute('aria-current');
  });

  const titles = { beranda: SITE.name, tentang: 'Profil & Karakter', pendidikan: 'Pendidikan', prestasi: 'Prestasi', kontak: 'Kontak' };
  const title = $('#appbar-title');
  const text = onGallery ? 'Galeri' : titles[currentSection];
  if (!title || !text || title.textContent === text) return;
  title.textContent = text;
  if (motionOK) {
    title.animate([{ opacity: 0, transform: 'translateY(6px)' }, { opacity: 1, transform: 'none' }], { duration: 240, easing: EASE });
  }
}

// Tabs switch screens instantly, like a native tab bar, instead of smooth-scrolling the whole page.
function goTo(id, anchor) {
  const target = document.getElementById(id);
  if (!target) return;
  if (id === 'beranda') scrollTo({ top: 0, behavior: 'instant' });
  else (anchor || target).scrollIntoView({ behavior: 'instant', block: 'start' });
  if (motionOK) target.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 220, easing: 'ease-out' });
  currentSection = id;
  syncTabbar();
}

function initMobileNav() {
  const sheet = $('#more-sheet');
  const panel = $('.sheet-panel', sheet);

  $('#tabbar').addEventListener('click', e => {
    const btn = e.target.closest('[data-tab]');
    if (!btn) return;
    e.preventDefault();
    const tab = btn.dataset.tab;
    if (tab === 'lainnya') {
      openModal(sheet);
      panel.focus({ preventScroll: true });
      return;
    }
    if (tab === 'galeri' || tab === 'prestasi') switchShowcaseTab(tab);
    if (tab === 'galeri') goTo('prestasi', $('#panel-galeri'));
    else goTo(tab);
  });

  sheet.addEventListener('click', e => {
    const row = e.target.closest('.sheet-row');
    if (!row) return;
    if (row.dataset.go) {
      e.preventDefault();
      afterClose = () => goTo(row.dataset.go);
    } else if (row.dataset.act === 'resume') {
      afterClose = downloadPortfolioModal;
    }
    closeModal(sheet);
  });

  let startY = null;
  let dragged = 0;
  panel.addEventListener('touchstart', e => {
    startY = e.touches[0].clientY;
    dragged = 0;
    panel.style.transition = 'none';
  }, { passive: true });
  panel.addEventListener('touchmove', e => {
    if (startY === null) return;
    dragged = Math.max(0, e.touches[0].clientY - startY);
    panel.style.transform = `translateY(${dragged}px)`;
  }, { passive: true });
  const release = () => {
    if (startY === null) return;
    startY = null;
    panel.style.transition = '';
    panel.style.transform = '';
    if (dragged > 70) closeModal(sheet);
  };
  panel.addEventListener('touchend', release);
  panel.addEventListener('touchcancel', release);

  const root = document.documentElement;
  document.addEventListener('focusin', e => root.classList.toggle('kb-open', e.target.matches('input, textarea')));
  document.addEventListener('focusout', () => root.classList.remove('kb-open'));
}

function initPointerEffects() {
  if (!motionOK || !finePointer) return;

  const hero = $('#beranda');
  const dots = $('.hero-dots');
  hero.addEventListener('pointermove', e => {
    const r = hero.getBoundingClientRect();
    dots.style.setProperty('--mx', (e.clientX - r.left) + 'px');
    dots.style.setProperty('--my', (e.clientY - r.top) + 'px');
    dots.classList.add('is-on');
  });
  hero.addEventListener('pointerleave', () => dots.classList.remove('is-on'));

  const card = $('#hero-card');
  card.addEventListener('pointermove', e => {
    const r = card.getBoundingClientRect();
    const px = (e.clientX - r.left) / r.width - 0.5;
    const py = (e.clientY - r.top) / r.height - 0.5;
    card.classList.add('is-tilting');
    card.style.transform = `rotateY(${px * 8}deg) rotateX(${-py * 8}deg)`;
    card.style.setProperty('--gx', (px + 0.5) * 100 + '%');
    card.style.setProperty('--gy', (py + 0.5) * 100 + '%');
  });
  card.addEventListener('pointerleave', () => {
    card.classList.remove('is-tilting');
    card.style.transform = '';
  });

  $$('.achievement-card, #tentang .clean-card').forEach(el => {
    el.classList.add('spot');
    el.addEventListener('pointermove', e => {
      const r = el.getBoundingClientRect();
      el.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      el.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  });

  $$('[data-magnetic]').forEach(el => {
    el.addEventListener('pointermove', e => {
      const r = el.getBoundingClientRect();
      const dx = (e.clientX - r.left - r.width / 2) * 0.18;
      const dy = (e.clientY - r.top - r.height / 2) * 0.3;
      el.style.transform = `translate(${dx}px, ${dy}px)`;
    });
    el.addEventListener('pointerleave', () => { el.style.transform = ''; });
  });
}

function initImages() {
  $$('.bg-slate-100 > img[src^="http"]').forEach(img => {
    img.classList.add('lazy-fade');
    if (img.complete) return;
    const wrap = img.parentElement;
    const done = () => {
      img.classList.remove('is-loading');
      wrap.classList.remove('shimmer');
    };
    img.classList.add('is-loading');
    wrap.classList.add('shimmer');
    img.addEventListener('load', done, { once: true });
    img.addEventListener('error', done, { once: true });
  });
}

function initGallery() {
  galleryItems = $$('#panel-galeri .gallery-card');
  galleryItems.forEach(el => {
    el.classList.add('gallery-item');
    el.setAttribute('role', 'button');
    el.setAttribute('tabindex', '0');
    el.addEventListener('keydown', e => {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      e.preventDefault();
      el.click();
    });
  });
}

function initModals() {
  $$('.modal').forEach(modal => {
    modal.addEventListener('mousedown', e => { if (e.target === modal) closeModal(modal); });
  });

  window.addEventListener('popstate', () => {
    history.scrollRestoration = 'auto';
    if (activeModal) closeModal(activeModal);
    else flushAfterClose();
  });

  window.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      if (activeModal) closeModal(activeModal);
      return;
    }
    if (!activeModal) return;

    if (activeModal.id === 'photo-modal' && (e.key === 'ArrowRight' || e.key === 'ArrowLeft')) {
      stepPhoto(e.key === 'ArrowRight' ? 1 : -1);
      return;
    }
    if (e.key === 'Tab') {
      const focusables = $$('button, [href], input, textarea, select', activeModal).filter(el => el.tabIndex !== -1);
      const first = focusables[0];
      const last = focusables[focusables.length - 1];
      if (!activeModal.contains(document.activeElement)) { e.preventDefault(); first.focus(); }
      else if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });
}

renderAchievements();
movePill($('#tab-pill'), $('.category-btn.active'));
initGallery();
initModals();
initContactForm();
initImages();
initScrollEffects();
initScrollSpy();
initMobileNav();
initPointerEffects();
initCounters();
initReveals();
initSchoolMap();
if (window.lucide) {
  lucide.createIcons();
}
