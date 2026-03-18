/**
 * spotify-widget.js
 * Fetches your most recently played Spotify track from spotify-proxy.php
 * and populates the widget on your page.
 *
 * Required HTML elements:
 *   #sp-track       — track name
 *   #sp-artist      — artist name
 *   #sp-ago         — relative time e.g. "17m ago"
 *   #sp-art-thumb   — album art <img> thumbnail
 *   #sp-art-bg      — album art <img> blurred background
 *   .card-spotify   — the card container (used to attach click → Spotify link)
 */

(async function () {
  const PROXY_URL = '/spotify-proxy.php'; // update if your proxy lives in a subfolder

  function timeAgo(isoString) {
    const diffMs = Date.now() - new Date(isoString).getTime();
    const mins   = Math.floor(diffMs / 60000);
    const hours  = Math.floor(mins / 60);
    const days   = Math.floor(hours / 24);
    if (mins  <  1) return 'just now';
    if (mins  < 60) return `${mins}m ago`;
    if (hours < 24) return `${hours}h ago`;
    return `${days}d ago`;
  }

  try {
    const res = await fetch(PROXY_URL);
    if (!res.ok) throw new Error(`Proxy returned ${res.status}`);
    const data = await res.json();
    if (data.error) throw new Error(data.error);

    const trackEl  = document.getElementById('sp-track');
    const artistEl = document.getElementById('sp-artist');
    const agoEl    = document.getElementById('sp-ago');
    const thumb    = document.getElementById('sp-art-thumb');
    const bg       = document.getElementById('sp-art-bg');

    if (trackEl)  trackEl.textContent  = data.track;
    if (artistEl) artistEl.textContent = data.artist;
    if (agoEl)    agoEl.textContent    = timeAgo(data.played_at);
    if (thumb && data.art) thumb.src   = data.art;
    if (bg    && data.art) bg.src      = data.art;

    // Make the card clickable — opens the track on Spotify
    const card = document.querySelector('.card-spotify');
    if (card && data.url) {
      card.style.cursor = 'pointer';
      card.addEventListener('click', () => window.open(data.url, '_blank'));
    }

  } catch (err) {
    // Silently falls back to whatever mockup text is in the HTML
    console.warn('Spotify widget:', err.message);
  }
})();
