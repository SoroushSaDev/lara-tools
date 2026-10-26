/**
 * Leaflet location picker for calendar events
 * (resources/views/calendar/partial/form.blade.php).
 *
 * Contract with the Blade view:
 *  - Root:   [data-map] with data-lat / data-lng / data-address (empty
 *            strings when no point is stored) and the .map-canvas surface
 *            (styled in resources/css/app.css; Leaflet CSS is imported
 *            globally there).
 *  - Fields: input[name="latitude"] and input[name="longitude"] in the same
 *            form are written on every pick/drag, together with the
 *            [data-map-coords] readout that sits in the same block.
 *  - Empty coords: the map centres on a neutral default with no marker;
 *    the first click drops a draggable pin.
 *  - Tiles are best-effort (OSM needs internet); the picker stays usable
 *    without them, so nothing is gated on tile loading. A basemap notice
 *    names the failure instead of leaving a grey rectangle.
 */

import L from 'leaflet';

import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

import { onReady } from '../support/ready';

/** Neutral centre when the event has no stored point yet. */
const DEFAULT_CENTER = [35.7, 51.38];
const DEFAULT_ZOOM = 11;
const POINT_ZOOM = 15;

/**
 * Leaflet's default icon derives its folder from the stylesheet, which finds
 * nothing once Vite has hashed the filenames, so the pin points at the
 * imported assets directly. Sizes copy L.Icon.Default's own options.
 */
const PIN = L.icon({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    tooltipAnchor: [16, -28],
    shadowSize: [41, 41],
});

/** Parse stored coordinates; null when absent or out of range. */
function storedPoint(el) {
    const lat = Number.parseFloat(el.dataset.lat);
    const lng = Number.parseFloat(el.dataset.lng);

    if (!Number.isFinite(lat) || !Number.isFinite(lng)
        || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
        return null;
    }

    return [lat, lng];
}

function format(value) {
    return value.toFixed(6);
}

function initPicker(el) {
    // Turbo re-runs initialisers on every visit/morph; never double-init.
    if (el._eventMap) return;

    el._eventMap = true;

    const scope = el.closest('form') ?? el.parentElement;

    const latInput = scope.querySelector('input[name="latitude"]');
    const lngInput = scope.querySelector('input[name="longitude"]');
    const readout = el.parentElement.querySelector('[data-map-coords]');

    const map = L.map(el, { scrollWheelZoom: false });

    // Turbo can restore a cached page, so reuse the notice rather than
    // stacking one per visit.
    let notice = el.querySelector('.map-basemap-notice');

    if (!notice) {
        notice = document.createElement('p');
        notice.className = 'map-basemap-notice';
        notice.textContent = 'Basemap unavailable — the pin still works; click to place it.';
        el.appendChild(notice);
    }

    notice.hidden = true;

    const basemap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    });

    // Offline is the normal state for the desktop app, so say so once instead
    // of letting every tile failure pile up. A later tile clears the notice.
    basemap.on('tileerror', () => { notice.hidden = false; });
    basemap.on('tileload', () => { notice.hidden = true; });

    basemap.addTo(map);

    let marker = null;

    const write = (point) => {
        const [lat, lng] = point;

        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;

        if (readout) {
            readout.innerHTML = `<i class="bi bi-geo-alt me-1"></i>${lat}, ${lng}`;
        }
    };

    const place = (latlng) => {
        const point = [Number.parseFloat(latlng.lat.toFixed(6)), Number.parseFloat(latlng.lng.toFixed(6))];

        if (marker) {
            marker.setLatLng(point);
        } else {
            marker = L.marker(point, { draggable: true, icon: PIN, title: 'Event location' }).addTo(map);

            marker.on('dragend', () => {
                const dragged = marker.getLatLng();

                write([Number.parseFloat(dragged.lat.toFixed(6)), Number.parseFloat(dragged.lng.toFixed(6))]);
            });
        }

        write(point);
    };

    map.on('click', (event) => place(event.latlng));

    const stored = storedPoint(el);

    if (stored) {
        map.setView(stored, POINT_ZOOM);
        place(stored);
    } else {
        map.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
    }

    // Keep the instance so navigation can free it (see below).
    el._leafletMap = map;
}

onReady(() => {
    document.querySelectorAll('[data-map]').forEach(initPicker);
});

/** Drop maps before the page enters Turbo's cache so none leak. */
document.addEventListener('turbo:before-cache', () => {
    document.querySelectorAll('[data-map]').forEach((el) => {
        el._leafletMap?.remove();
        el._leafletMap = null;
        el._eventMap = false;
    });
});
