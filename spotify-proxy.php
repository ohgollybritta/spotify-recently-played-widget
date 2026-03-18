<?php
// ============================================================
//  spotify-proxy.php
//  Upload this to your server root.
//  Fill in the three constants below, then save and upload.
// ============================================================

define('SPOTIFY_CLIENT_ID',     'YOUR_CLIENT_ID_HERE');
define('SPOTIFY_CLIENT_SECRET', 'YOUR_CLIENT_SECRET_HERE');
define('SPOTIFY_REFRESH_TOKEN', 'YOUR_REFRESH_TOKEN_HERE');

// Lock this down to your domain in production, e.g. 'https://yourdomain.com'
define('ALLOWED_ORIGIN', '*');

// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);

// ── 1. Get a fresh access token using the refresh token ──────
$token_response = curl_request('https://accounts.spotify.com/api/token', 'POST', [
  'grant_type'    => 'refresh_token',
  'refresh_token' => SPOTIFY_REFRESH_TOKEN,
], [
  'Authorization: Basic ' . base64_encode(SPOTIFY_CLIENT_ID . ':' . SPOTIFY_CLIENT_SECRET),
  'Content-Type: application/x-www-form-urlencoded',
]);

if (!isset($token_response['access_token'])) {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to refresh access token', 'detail' => $token_response]);
  exit;
}

$access_token = $token_response['access_token'];

// ── 2. Fetch the most recently played track ──────────────────
$recent = curl_request(
  'https://api.spotify.com/v1/me/player/recently-played?limit=1',
  'GET',
  [],
  ['Authorization: Bearer ' . $access_token]
);

if (empty($recent['items'])) {
  http_response_code(500);
  echo json_encode(['error' => 'No recently played tracks found', 'detail' => $recent]);
  exit;
}

// ── 3. Shape the response ────────────────────────────────────
$item      = $recent['items'][0];
$track     = $item['track'];
$played_at = $item['played_at']; // ISO 8601 UTC

echo json_encode([
  'track'     => $track['name'],
  'artist'    => implode(', ', array_map(fn($a) => $a['name'], $track['artists'])),
  'album'     => $track['album']['name'],
  'art'       => $track['album']['images'][1]['url'] ?? $track['album']['images'][0]['url'] ?? null,
  'url'       => $track['external_urls']['spotify'],
  'played_at' => $played_at,
]);


// ── Helper ───────────────────────────────────────────────────
function curl_request(string $url, string $method, array $body, array $headers): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_CUSTOMREQUEST  => $method,
  ]);
  if ($method === 'POST' && $body) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
  }
  $raw = curl_exec($ch);
  curl_close($ch);
  return json_decode($raw, true) ?? [];
}
