# spotify-recently-played-widget

A lightweight Spotify "Recently Played" widget for static sites on shared hosting (DreamHost, Bluehost, etc.). No Node, no build step — just PHP and vanilla JS.

![Widget preview showing track name, artist, album art thumbnail, and animated EQ bars](preview.png)

## What it does

Displays your last played Spotify track — title, artist, album art, and a relative timestamp ("17m ago") — live on your site. The card is clickable and opens the track on Spotify.

---

## Files

| File | Purpose |
|---|---|
| `spotify-proxy.php` | Lives on your server. Handles OAuth token refresh and Spotify API calls. |
| `spotify-widget.js` | Included in your HTML. Fetches from the proxy and updates the DOM. |
| `callback.html` | One-time setup tool. Upload once, get your refresh token, then delete it. |

---

## Setup

### 1. Create a Spotify App

1. Go to [developer.spotify.com/dashboard](https://developer.spotify.com/dashboard) and create a new app
2. Under **APIs used**, check **Web API**
3. Note your **Client ID** and **Client Secret**

### 2. Get your Refresh Token

1. Upload `callback.html` to your server (e.g. `https://yourdomain.com/callback.html`)
2. In your Spotify app settings, add `https://yourdomain.com/callback.html` as a **Redirect URI** and save
3. Also go to **User Management** and add your own Spotify account email
4. Visit `https://yourdomain.com/callback.html` in your browser
5. Enter your Client ID and Client Secret, then click **Authorize with Spotify**
6. Copy the **Refresh Token** that appears
7. Delete `callback.html` from your server — you won't need it again

### 3. Configure the Proxy

Open `spotify-proxy.php` and fill in your credentials:

```php
define('SPOTIFY_CLIENT_ID',     'your_client_id_here');
define('SPOTIFY_CLIENT_SECRET', 'your_client_secret_here');
define('SPOTIFY_REFRESH_TOKEN', 'your_refresh_token_here');
```

Optionally lock down the CORS origin to your domain:

```php
define('ALLOWED_ORIGIN', 'https://yourdomain.com');
```

Upload `spotify-proxy.php` to your server root.

### 4. Add the Widget HTML

Add this card wherever you want the widget to appear:

```html
<div class="card card-spotify">
  <img class="sp-art-bg" id="sp-art-bg" src="" alt="">
  <div class="widget-label">Recently Played</div>
  <div class="spotify-main">
    <img class="sp-art-thumb" id="sp-art-thumb" src="" alt="Album art">
    <div class="spotify-body">
      <div class="track-name" id="sp-track">Track Name</div>
      <div class="track-artist" id="sp-artist">Artist</div>
      <div class="track-ago" id="sp-ago">—</div>
    </div>
  </div>
  <div class="eq-bars"><span></span><span></span><span></span><span></span><span></span><span></span></div>
</div>
```

### 5. Include the Script

Add this just before `</body>`:

```html
<script src="/spotify-widget.js"></script>
```

---

## Notes

- The proxy fetches a fresh access token on every request using your refresh token — no manual re-authentication needed
- If the proxy fails for any reason, the widget silently falls back to whatever placeholder text you put in the HTML
- Refresh tokens don't expire unless you revoke them or go 30+ days without a Spotify login

---

## License

MIT — use it, remix it, ship it.
Built by ohgollybritta with the help of Claude by Antropic.
