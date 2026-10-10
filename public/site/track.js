// Statistik pengunjung: mengirim sinyal kunjungan dan interaksi ke server. Tanpa cookie dan
// tanpa pihak ketiga; server yang memutuskan apakah sinyalnya dicatat (bot dan pengelola yang
// sedang login dilewati).
(function () {
  const endpoint = document.currentScript.dataset.endpoint;
  if (!endpoint) return;

  function send(payload) {
    const body = JSON.stringify(payload);
    try {
      if (navigator.sendBeacon && navigator.sendBeacon(endpoint, new Blob([body], { type: 'application/json' }))) return;
      fetch(endpoint, { method: 'POST', body, keepalive: true, headers: { 'Content-Type': 'application/json' } }).catch(() => {});
    } catch (e) { /* statistik tidak boleh mengganggu halaman */ }
  }

  const event = (name, label) => send({ type: 'event', name, label: label || null });

  send({ type: 'visit', referrer: document.referrer, query: location.search });

  // Fungsi halaman dibungkus, bukan diubah: site.js tetap tidak tahu soal statistik.
  function wrap(name, report) {
    const original = window[name];
    if (typeof original !== 'function') return;
    window[name] = function (...args) {
      try { report(...args); } catch (e) { /* abaikan */ }
      return original.apply(this, args);
    };
  }

  const site = window.SITE || {};
  let lastPhoto = null;

  wrap('openCertificateModal', key => {
    const cert = (site.certs || {})[key];
    if (cert) event('achievement', cert.title);
  });
  wrap('openPhotoModalByIndex', index => {
    const photo = (site.gallery || [])[index];
    if (photo && lastPhoto !== index) event('photo', photo.title);
    lastPhoto = index;
  });
  wrap('closePhotoModal', () => { lastPhoto = null; });
  wrap('selectLocation', (key, fly) => {
    const location = (site.locations || {})[key];
    // fly === false hanya dipakai saat peta pertama kali dimuat, bukan pilihan pengunjung.
    if (location && fly !== false) event('location', location.name);
  });
  wrap('copyEmailAddress', () => event('email'));
  window.addEventListener('site:message-sent', () => event('message'));

  document.addEventListener('click', e => {
    const link = e.target.closest('a[href]');
    if (!link) return;
    if (link.href.includes('wa.me/')) event('whatsapp');
    else if (link.dataset.track === 'resume') event('resume');
  });

  if ('IntersectionObserver' in window) {
    const labels = { tentang: 'Profil', pendidikan: 'Pendidikan & peta', prestasi: 'Prestasi & galeri', kontak: 'Kontak' };
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        observer.unobserve(entry.target);
        event('section', labels[entry.target.id]);
      });
    }, { threshold: 0.15 });
    Object.keys(labels).forEach(id => {
      const section = document.getElementById(id);
      if (section) observer.observe(section);
    });
  }
})();
