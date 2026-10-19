/**
 * "Use my location" button on the weather dashboard.
 *
 * Asks for browser geolocation and navigates to the live-location view.
 */

import { onReady } from '../support/ready';

onReady(() => {
    const button = document.querySelector('[data-locate-url]');

    if (!button || button.dataset.bound) return;

    button.dataset.bound = '1';

    button.addEventListener('click', () => {
        if (!('geolocation' in navigator)) {
            button.title = 'Geolocation is not supported here';

            return;
        }

        const icon = button.querySelector('i');

        icon?.classList.add('animate-spin-slow');
        icon?.classList.replace('bi-geo-alt-fill', 'bi-arrow-repeat');

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const url = new URL(button.dataset.locateUrl, window.location.origin);

                url.searchParams.set('lat', position.coords.latitude.toFixed(6));
                url.searchParams.set('lon', position.coords.longitude.toFixed(6));

                window.location.href = url.toString();
            },
            (error) => {
                icon?.classList.remove('animate-spin-slow');
                icon?.classList.replace('bi-arrow-repeat', 'bi-geo-alt-fill');

                button.title = error.message === 'User denied Geolocation'
                    ? 'Location permission denied'
                    : 'Could not determine your location';
            },
            { timeout: 10000 },
        );
    });
});
