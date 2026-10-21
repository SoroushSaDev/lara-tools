/**
 * Music library player — survives route changes.
 *
 * Turbo swaps the page <body> on navigation but JavaScript state lives on,
 * so playback continues seamlessly. The player bar itself lives in the app
 * layout marked data-turbo-permanent: it renders as a compact pill on every
 * page and expands to the full deck while the Music page is open (the body
 * gets an .on-music-page class server-side).
 *
 * Because mini and full modes duplicate some controls, they are wired via
 * data hooks: [data-player-cover|title|artist|play-icon|action="..."].
 * Single-instance elements keep ids: #player-progress/-current-time/
 * -total-time/-volume/-mute.
 *
 * Library rows (.track): data-index|src|ok|title|artist|cover|duration.
 * Body dataset: data-music-shuffle-default="0|1" (from settings).
 *
 * Progress/volume are <x-slider> components (modules/slider.js): the ids
 * land on the native inputs inside them, so they keep the value/input/change
 * contract — but programmatic writes must dispatch a bubbled `input` event
 * for the component to repaint (see syncSlider). The seek slider gets its tip
 * text from root.tipOverride so it shows the target time, not the playhead.
 */

import { onReady } from '../support/ready';

const VOLUME_KEY = 'lara-tools.volume';

const state = {
    tracks: [],
    current: -1,
    shuffle: false,
    repeat: 'off', // off | all | one
    seeking: false,
};

let audio = null;
let ui = {};
let controlsBound = false;

/** Format seconds as m:ss (or h:mm:ss for long files). */
function formatTime(seconds) {
    if (!Number.isFinite(seconds)) return '--:--';

    const total = Math.max(0, Math.floor(seconds));
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = total % 60;

    return h > 0
        ? `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
        : `${m}:${String(s).padStart(2, '0')}`;
}

/** Write a slider value and let modules/slider.js repaint --p and the tip. */
function syncSlider(input, value) {
    input.value = String(value);
    input.dispatchEvent(new Event('input', { bubbles: true }));
}

/** Show a transient toast matching the global flash-message styling. */
function showToast(message) {
    const toast = document.createElement('div');

    toast.dataset.toast = '';
    toast.className = 'glass pointer-events-auto fixed top-4 right-4 z-50 flex items-center gap-3 '
        + 'border-l-4 !border-l-rose-500 p-4 text-sm animate-toast-in';

    toast.innerHTML = '<i class="bi bi-exclamation-triangle-fill shrink-0 text-base text-rose-500"></i>'
        + `<p class="flex-1 leading-snug">${message}</p>`;

    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 320);
    }, 4500);
}

function markActiveRow() {
    document.querySelectorAll('.track').forEach((row) => {
        row.classList.toggle('is-active', Number(row.dataset.index) === state.current);
    });
}

function showPlayer() {
    ui.player.classList.remove('hidden');
    ui.player.classList.add('flex');
}

function updatePlayIcon() {
    if (!audio) return;

    ui.playIcons.forEach((icon) => {
        icon.classList.toggle('bi-play-fill', audio.paused);
        icon.classList.toggle('bi-pause-fill', !audio.paused);
    });
}

function updateRepeatIcon() {
    ui.repeat.classList.toggle('text-violet-500', state.repeat !== 'off');
    ui.repeat.classList.toggle('dark:text-fuchsia-400', state.repeat !== 'off');

    const icon = ui.repeat.querySelector('i');

    icon.classList.toggle('bi-repeat', state.repeat !== 'one');
    icon.classList.toggle('bi-repeat-1', state.repeat === 'one');
}

function syncShuffleUi() {
    ui.shuffle.classList.toggle('text-violet-500', state.shuffle);
    ui.shuffle.classList.toggle('dark:text-fuchsia-400', state.shuffle);
}

/** Reflect the current track's metadata across both player modes. */
function renderTrackInfo(track) {
    ui.titles.forEach((el) => { el.textContent = track.dataset.title; });
    ui.artists.forEach((el) => { el.textContent = track.dataset.artist || ''; });

    ui.covers.forEach((img) => {
        if (track.dataset.cover) {
            img.src = track.dataset.cover;
            img.classList.remove('hidden');
        } else {
            img.removeAttribute('src');
            img.classList.add('hidden');
        }
    });
}

/** Load a track into the audio element and start playback. */
function play(index) {
    if (state.tracks.length === 0) return;

    // Wrap around the playlist in both directions.
    state.current = ((index % state.tracks.length) + state.tracks.length) % state.tracks.length;

    const track = state.tracks[state.current];

    // Refuse to play rows whose underlying file has vanished.
    if (track.dataset.ok === '0') {
        showToast('The audio file for this track is missing. Re-import it from disk.');

        return;
    }

    audio.src = track.dataset.src;
    audio.load();

    // play() may reject under browser autoplay policies; the pause icon
    // already reflects reality in that case.
    audio.play().catch(() => updatePlayIcon());

    renderTrackInfo(track);

    syncSlider(ui.progress, 0);
    ui.currentTime.textContent = '0:00';
    ui.totalTime.textContent = formatTime(Number(track.dataset.duration));

    markActiveRow();
    showPlayer();
}

function next() {
    if (state.shuffle && state.tracks.length > 1) {
        let random;

        do {
            random = Math.floor(Math.random() * state.tracks.length);
        } while (random === state.current);

        play(random);

        return;
    }

    play(state.current + 1);
}

function previous() {
    // Standard player behaviour: restart when part-way through a track.
    if (audio.currentTime > 3) {
        audio.currentTime = 0;

        return;
    }

    play(state.current - 1);
}

/** Bind transport/progress listeners — done once, they outlive navigation. */
function bindControls() {
    const actions = {
        prev: () => previous(),
        next: () => next(),
        play: () => (audio.paused ? audio.play() : audio.pause()),
        shuffle: () => {
            state.shuffle = !state.shuffle;
            syncShuffleUi();
        },
        repeat: () => {
            state.repeat = { off: 'all', all: 'one', one: 'off' }[state.repeat];
            updateRepeatIcon();
        },
    };

    ui.actionButtons.forEach((button) => {
        button.addEventListener('click', () => actions[button.dataset.playerAction]?.());
    });

    audio.addEventListener('play', updatePlayIcon);
    audio.addEventListener('pause', updatePlayIcon);

    audio.addEventListener('error', () => {
        if (!audio.src) return;

        showToast('The audio file could not be read. It may have been moved or deleted.');
        updatePlayIcon();
    });

    audio.addEventListener('loadedmetadata', () => {
        ui.totalTime.textContent = formatTime(audio.duration);
    });

    audio.addEventListener('timeupdate', () => {
        if (!state.seeking && Number.isFinite(audio.duration) && audio.duration > 0) {
            syncSlider(ui.progress, (audio.currentTime / audio.duration) * 100);
        }

        ui.currentTime.textContent = formatTime(audio.currentTime);

        // Thin progress line shared by both player modes.
        document.querySelectorAll('[data-player-line]').forEach((line) => {
            line.style.width = Number.isFinite(audio.duration) && audio.duration > 0
                ? `${(audio.currentTime / audio.duration) * 100}%`
                : '0%';
        });
    });

    audio.addEventListener('ended', () => {
        if (state.repeat === 'one') {
            audio.currentTime = 0;
            audio.play();

            return;
        }

        const isLastTrack = state.current === state.tracks.length - 1;

        if (!isLastTrack || state.repeat === 'all') {
            next();
        }
    });

    // Seek support (dragging the progress bar).
    ui.progress.addEventListener('input', () => {
        if (Number.isFinite(audio.duration)) {
            ui.currentTime.textContent = formatTime(
                (ui.progress.value / 100) * audio.duration,
            );
        }
    });

    ui.progress.addEventListener('pointerdown', () => { state.seeking = true; });
    ui.progress.addEventListener('change', () => {
        if (Number.isFinite(audio.duration)) {
            audio.currentTime = (ui.progress.value / 100) * audio.duration;
        }
        state.seeking = false;
    });

    // The seek slider's tip must show the drag target, not the playhead.
    const progressRoot = ui.progress.closest('[data-slider]');

    if (progressRoot) {
        progressRoot.tipOverride = (percent) =>
            Number.isFinite(audio.duration) && audio.duration > 0
                ? formatTime((percent / 100) * audio.duration)
                : '--:--';
    }

    // Volume control persisted between sessions. Muting swaps the level to
    // zero and back so the previous volume survives the mute.
    const savedVolume = localStorage.getItem(VOLUME_KEY);
    let lastVolume = null;

    audio.volume = savedVolume === null
        ? 1
        : Math.min(1, Math.max(0, Number(savedVolume)));

    syncSlider(ui.volume, audio.volume * 100);

    ui.volume.addEventListener('input', () => {
        audio.volume = Math.min(1, Math.max(0, ui.volume.value / 100));
        localStorage.setItem(VOLUME_KEY, String(audio.volume));

        ui.mute.querySelector('i').className = audio.volume === 0
            ? 'bi bi-volume-mute-fill'
            : 'bi bi-volume-up-fill';
    });

    ui.mute.addEventListener('click', () => {
        if (audio.volume > 0) {
            lastVolume = audio.volume;
            syncSlider(ui.volume, 0);
        } else {
            syncSlider(ui.volume, (lastVolume ?? 1) * 100);
        }
    });

    controlsBound = true;
}

/** Re-bind library rows after each visit; refresh active highlighting. */
function bindRows() {
    state.tracks = [...document.querySelectorAll('.track')];

    markActiveRow();

    state.tracks.forEach((row) => {
        // Turbo morphs keep unchanged rows, so never bind a row twice.
        if (row.dataset.bound) return;

        row.dataset.bound = '1';

        row.addEventListener('click', (event) => {
            // Nested controls (delete form, details link) act on their own.
            if (event.target.closest('form, button, a')) return;

            const index = Number(row.dataset.index);

            if (index === state.current && !audio.paused) {
                audio.pause();
            } else if (index === state.current) {
                audio.play();
            } else {
                play(index);
            }
        });
    });
}

onReady(() => {
    const playerEl = document.getElementById('player');

    // The bar lives in the layout, so it exists everywhere; without a
    // library (no tracks rendered anywhere) there is simply nothing to bind.
    if (!playerEl || document.querySelector('.track') === null) return;

    state.shuffle = document.body.dataset.musicShuffleDefault === '1';

    if (!controlsBound) {
        ui = {
            player: playerEl,
            covers: [...playerEl.querySelectorAll('[data-player-cover]')],
            titles: [...playerEl.querySelectorAll('[data-player-title]')],
            artists: [...playerEl.querySelectorAll('[data-player-artist]')],
            playIcons: [...playerEl.querySelectorAll('[data-player-play-icon]')],
            actionButtons: [...playerEl.querySelectorAll('[data-player-action]')],
            shuffle: playerEl.querySelector('[data-player-action="shuffle"]'),
            repeat: playerEl.querySelector('[data-player-action="repeat"]'),
            progress: document.getElementById('player-progress'),
            currentTime: document.getElementById('player-current-time'),
            totalTime: document.getElementById('player-total-time'),
            volume: document.getElementById('player-volume'),
            mute: document.getElementById('player-mute'),
        };

        audio = new Audio();
        audio.preload = 'metadata';

        bindControls();
        syncShuffleUi();
        updateRepeatIcon();
    } else {
        ui.player = playerEl;
    }

    bindRows();

    // Returning to the library mid-song: reflect the running state.
    if (state.current >= 0) {
        markActiveRow();
        showPlayer();
        updatePlayIcon();
    }
});

