/**
 * Library like toggles.
 *
 * Contract with the Blade view:
 *  - Rows: button[data-like-url].like-btn, holding .is-liked while liked.
 *  - Header: [data-liked-count] with the liked total.
 *
 * The listener lives on the document, bound once, because a search re-renders
 * the rows inside their turbo frame and a per-element bind would stack up.
 * CSS chooses which heart glyph shows (resources/css/app.css), so a repaint
 * only ever flips one class.
 *
 * Requests are optimistic, like the todo checklist: the heart answers at once
 * and only a failed PATCH puts it back. Nothing reloads, which matters because
 * a page visit would rebuild the library rows the player tracks by position —
 * and because the header count lives outside the frame, it is corrected here.
 */

function paint(button, liked) {
    button.classList.toggle('is-liked', liked);
    button.setAttribute('aria-pressed', String(liked));
    button.title = liked ? 'Remove from liked' : 'Like this track';
}

function syncCount(before, after) {
    const count = document.querySelector('[data-liked-count]');

    if (!count || before === after) return;

    count.textContent = String(Math.max(0, Number(count.textContent) + (after ? 1 : -1)));
}

document.addEventListener('click', async (event) => {
    const button = event.target.closest('button[data-like-url]');

    if (!button) return;

    const wasLiked = button.classList.contains('is-liked');
    const liked = !wasLiked;

    paint(button, liked);

    try {
        const { data } = await window.axios.patch(button.dataset.likeUrl);

        paint(button, Boolean(data.liked));
        syncCount(wasLiked, Boolean(data.liked));
    } catch {
        paint(button, wasLiked);
    }
});
