<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpotifyService
{
    protected $baseUrl = 'https://api.spotify.com/v1';
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    protected function get($endpoint, $params = [])
    {
        $response = Http::withToken($this->token)->get($this->baseUrl . $endpoint, $params);

        if (!$response->successful()) {
            Log::error("Spotify API Error: {$endpoint}", [
                'status' => $response->status(),
                'body' => $response->body(),
                'params' => $params
            ]);
            throw new \Exception("Spotify API Error: " . $response->status());
        }

        return $response->json();
    }

    public function getUserPlaylists()
    {
        return $this->get('/me/playlists');
    }

    public function getPlaylist($id)
    {
        return $this->get("/playlists/{$id}");
    }

    public function getAlbum($id)
    {
        return $this->get("/albums/{$id}");
    }

    public function getPlaylistTracks($id, $offset = 0, $limit = 50)
    {
        return $this->get("/playlists/{$id}/tracks", [
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }

    public function getAlbumTracks($id, $offset = 0, $limit = 50)
    {
        return $this->get("/albums/{$id}/tracks", [
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }

    public function getAllPlaylistTracks($id)
    {
        $allTracks = [];
        $offset = 0;
        $limit = 50;

        do {
            $data = $this->getPlaylistTracks($id, $offset, $limit);
            $items = $data['items'] ?? [];

            foreach ($items as $item) {
                if (isset($item['track'])) {
                    $allTracks[] = $this->formatTrack($item['track']);
                }
            }

            $offset += $limit;
            $total = $data['total'];

            // basic rate limiting
            usleep(100000);
        } while ($offset < $total);

        return $allTracks;
    }

    public function getAllAlbumTracks($id)
    {
        $allTracks = [];
        $offset = 0;
        $limit = 50;

        // We need album info for the cover art and release date, 
        // as album/tracks endpoint returns simplified track objects often without images
        $album = $this->getAlbum($id);
        $albumCover = $album['images'][0]['url'] ?? null;
        $albumName = $album['name'];
        $releaseDate = $album['release_date'] ?? null;

        do {
            $data = $this->getAlbumTracks($id, $offset, $limit);
            $items = $data['items'] ?? [];

            foreach ($items as $item) {
                // Pass album context because album tracks don't have it
                $allTracks[] = $this->formatTrack($item, [
                    'album_name' => $albumName,
                    'album_image' => $albumCover,
                    'release_date' => $releaseDate
                ]);
            }

            $offset += $limit;
            $total = $data['total'];

            usleep(100000);
        } while ($offset < $total);

        return $allTracks;
    }

    protected function formatTrack($track, $context = [])
    {
        // Handle playlist item wrapper (added_at, added_by, track object)
        /* 
           Playlist structure: $item['track'] -> actual track
           Album structure: $item -> actual track (but missing album object usually)
        */

        // If context is provided, it's likely an album track which is direct
        // If not, we check if it has 'track' key (playlist item)
        /* 
           Actually formatTrack is called with $item['track'] in getAllPlaylistTracks.
           So $track is the track object.
        */

        $albumName = $track['album']['name'] ?? $context['album_name'] ?? 'Unknown Album';
        $albumImage = $track['album']['images'][0]['url'] ?? $context['album_image'] ?? null;
        $releaseDate = $track['album']['release_date'] ?? $context['release_date'] ?? null;

        return [
            'id' => $track['id'],
            'name' => $track['name'],
            'artists' => array_map(fn($a) => $a['name'], $track['artists']),
            'album' => $albumName,
            'release_date' => $releaseDate,
            'track_number' => $track['track_number'] ?? 0,
            'disc_number' => $track['disc_number'] ?? 1,
            'image' => $albumImage,
            'duration_ms' => $track['duration_ms'],
            'uri' => $track['uri'],
        ];
    }
}
