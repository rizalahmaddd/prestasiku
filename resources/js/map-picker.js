// Leaflet hanya dibutuhkan di formulir Lokasi, jadi dimuat saat pemilih peta pertama kali muncul.
const LEAFLET_VERSION = '1.9.4';
const LEAFLET_JS = `https://unpkg.com/leaflet@${LEAFLET_VERSION}/dist/leaflet.js`;
const LEAFLET_CSS = `https://unpkg.com/leaflet@${LEAFLET_VERSION}/dist/leaflet.css`;
const DEFAULT_CENTER = [-6.2, 106.816666];
const DEFAULT_ZOOM = 11;

let leafletLoading = null;

function loadLeaflet() {
    if (window.L) {
        return Promise.resolve();
    }

    leafletLoading ??= new Promise((resolve, reject) => {
        const style = document.createElement('link');
        style.rel = 'stylesheet';
        style.href = LEAFLET_CSS;
        document.head.append(style);

        const script = document.createElement('script');
        script.src = LEAFLET_JS;
        script.onload = resolve;
        script.onerror = () => {
            leafletLoading = null;
            reject(new Error('Leaflet gagal dimuat'));
        };
        document.head.append(script);
    });

    return leafletLoading;
}

function isCoordinate(value) {
    return value !== null && value !== '' && Number.isFinite(Number(value));
}

function round(value) {
    return Math.round(value * 1e6) / 1e6;
}

function mapPicker(config) {
    return {
        lat: config.lat,
        lng: config.lng,
        zoom: config.zoom,
        color: config.color,
        query: '',
        results: [],
        searching: false,
        message: '',
        failed: false,
        map: null,
        marker: null,

        async init() {
            try {
                await loadLeaflet();
            } catch {
                this.failed = true;
                return;
            }

            const L = window.L;
            this.map = L.map(this.$refs.map, { scrollWheelZoom: false }).setView(this.center(), this.hasPoint() ? Number(this.zoom) || 15 : DEFAULT_ZOOM);

            const tiles = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri',
                maxZoom: 18,
            });
            tiles.once('tileerror', () => {
                this.map.removeLayer(tiles);
                L.tileLayer('https://tile.openstreetmap.de/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 18 }).addTo(this.map);
            });
            tiles.addTo(this.map);

            this.map.on('click', (event) => this.setPoint(event.latlng.lat, event.latlng.lng));
            this.map.on('zoomend', () => {
                if (this.hasPoint()) {
                    this.zoom = this.map.getZoom();
                }
            });

            // Formulirnya ada di dalam modal: peta dibuat saat masih tersembunyi (ukuran 0),
            // jadi ukurannya harus dihitung ulang begitu modal tampil.
            new ResizeObserver(() => this.map.invalidateSize()).observe(this.$refs.map);

            this.syncMarker();
            this.$watch('lat', () => this.syncMarker());
            this.$watch('lng', () => this.syncMarker());
            this.$watch('color', () => this.syncMarker(true));
            this.$watch('zoom', (zoom) => {
                if (this.hasPoint() && Number(zoom) >= 3 && Number(zoom) !== this.map.getZoom()) {
                    this.map.setZoom(Number(zoom));
                }
            });
        },

        hasPoint() {
            return isCoordinate(this.lat) && isCoordinate(this.lng);
        },

        center() {
            return this.hasPoint() ? [Number(this.lat), Number(this.lng)] : DEFAULT_CENTER;
        },

        pinIcon() {
            const color = /^#[0-9a-f]{6}$/i.test(this.color || '') ? this.color : '#1d4ed8';

            return window.L.divIcon({
                className: '',
                html: `<span style="display:block;width:22px;height:22px;border-radius:50%;background:${color};border:3px solid #fff;box-shadow:0 2px 8px rgba(15,23,42,.5)"></span>`,
                iconSize: [22, 22],
                iconAnchor: [11, 11],
            });
        },

        syncMarker(restyle = false) {
            if (!this.map) {
                return;
            }

            if (!this.hasPoint()) {
                this.marker?.remove();
                this.marker = null;
                this.map.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
                return;
            }

            const point = this.center();

            if (!this.marker) {
                this.marker = window.L.marker(point, { draggable: true, icon: this.pinIcon(), keyboard: false }).addTo(this.map);
                this.marker.on('dragend', () => {
                    const position = this.marker.getLatLng();
                    this.setPoint(position.lat, position.lng);
                });
                this.map.setView(point, Number(this.zoom) || 15);
                return;
            }

            if (restyle) {
                this.marker.setIcon(this.pinIcon());
            }

            const current = this.marker.getLatLng();
            if (round(current.lat) !== round(point[0]) || round(current.lng) !== round(point[1])) {
                this.marker.setLatLng(point);
                this.map.panTo(point);
            }
        },

        setPoint(lat, lng) {
            const first = !this.hasPoint();
            this.lat = round(lat);
            this.lng = round(lng);

            if (first) {
                this.zoom = Math.max(this.map.getZoom(), 15);
            }
        },

        async search() {
            const query = this.query.trim();
            if (query.length < 3 || this.searching) {
                return;
            }

            this.searching = true;
            this.message = '';
            this.results = [];

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&accept-language=id&q=${encodeURIComponent(query)}`);
                if (!response.ok) {
                    throw new Error(String(response.status));
                }
                this.results = await response.json();
                if (this.results.length === 0) {
                    this.message = 'Tempat tidak ditemukan. Coba nama yang lebih umum, lalu geser penandanya secara manual.';
                }
            } catch {
                this.message = 'Pencarian tempat sedang tidak bisa dipakai. Klik langsung di peta untuk menaruh penanda.';
            } finally {
                this.searching = false;
            }
        },

        pick(result) {
            this.setPoint(Number(result.lat), Number(result.lon));
            this.zoom = 16;
            this.map?.setView(this.center(), 16);
            this.results = [];
            this.query = '';

            if (config.addressField && !this.$wire.get(config.addressField)) {
                this.$wire.set(config.addressField, result.display_name, false);
            }
        },
    };
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('mapPicker', mapPicker);
});
