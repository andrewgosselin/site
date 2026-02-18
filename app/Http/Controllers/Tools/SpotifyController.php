<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Illuminate\Support\Facades\Crypt;
use App\Models\SpotifyPlaylist;

class SpotifyController extends Controller
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.spotify.client_id');
        $this->clientSecret = config('services.spotify.client_secret');
        $this->redirectUri = config('services.spotify.redirect_uri');
    }

    public function index()
    {
        return Inertia::render('tools/SpotifyTool', [
            'isConnected' => Session::has('spotify_access_token'),
        ]);
    }

    public function redirect()
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => 'playlist-read-private playlist-read-collaborative user-library-read',
        ]);

        return redirect('https://accounts.spotify.com/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $code = $request->code;

        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // Encrypt and store tokens in session
            Session::put('spotify_access_token', Crypt::encryptString($data['access_token']));
            if (isset($data['refresh_token'])) {
                Session::put('spotify_refresh_token', Crypt::encryptString($data['refresh_token']));
            }

            return redirect()->route('tools.spotify.index')->with('success', 'Connected to Spotify successfully!');
        }

        return redirect()->route('tools.spotify.index')->with('error', 'Failed to connect to Spotify.');
    }

    private function getClientAccessToken()
    {
        // Check if we have a valid client token in cache/session
        if (Session::has('spotify_client_token')) {
            $tokenData = Session::get('spotify_client_token');
            if ($tokenData['expires_at'] > time()) {
                return $tokenData['token'];
            }
        }

        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $token = $data['access_token'];
            $expiresIn = $data['expires_in'];

            Session::put('spotify_client_token', [
                'token' => $token,
                'expires_at' => time() + $expiresIn - 60 // buffer
            ]);

            return $token;
        }

        throw new \Exception('Failed to get client access token');
    }

    private function getService()
    {
        if (Session::has('spotify_access_token')) {
            $token = Crypt::decryptString(Session::get('spotify_access_token'));
        } else {
            // Fallback to client credentials
            $token = $this->getClientAccessToken();
        }

        return new \App\Services\SpotifyService($token);
    }

    public function playlists()
    {
        try {
            $service = $this->getService();
            return $service->getUserPlaylists();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    public function tracks($id)
    {
        try {
            $service = $this->getService();
            // TODO: Handling album tracks here if $id is album? 
            // Currently tracks() is used for previewing tracks list in UI.
            // But we can check type if needed, or just let service fail if wrong endpoint.
            // For now, let's assume UI handles fetchPlaylist which returns type and tracks?
            // Actually fetchPlaylist is just metadata. tracks($id) fetches tracks.
            // We should check type query param too.
            // But let's keep it simple for now, the UI logic fetches playlist then tracks.
            // We will need to update this to support albums if the UI calls it for albums.
            // Let's optimize: Check query param 'type'
            $type = request()->query('type', 'playlist');
            if ($type === 'album') {
                return ['items' => $service->getAllAlbumTracks($id)]; // Use getAllAlbumTracks to get formatted list
            }
            $tracks = $service->getPlaylistTracks($id);
            return $tracks;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fetchPlaylist(Request $request, $id)
    {
        $type = $request->query('type', 'playlist');

        try {
            $service = $this->getService();
            if ($type === 'album') {
                return $service->getAlbum($id);
            }
            return $service->getPlaylist($id);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function download($id)
    {
        // Allow download if we have either user token OR can get client token
        $token = null;
        if (Session::has('spotify_access_token')) {
            $token = Session::get('spotify_access_token');
        } else {
            // Encode client token so job can decrypt it same way
            $clientToken = $this->getClientAccessToken();
            $token = Crypt::encryptString($clientToken);
        }

        // Check if exists
        $playlist = SpotifyPlaylist::where('spotify_id', $id)->first();

        if (!$playlist) {
            // Initiate download record
            try {
                $service = $this->getService();

                // Determine type based on request
                $type = request()->query('type', 'playlist');

                if ($type === 'album') {
                    $data = $service->getAlbum($id);
                } else {
                    $data = $service->getPlaylist($id);
                }

                $playlist = SpotifyPlaylist::create([
                    'spotify_id' => $data['id'],
                    'type' => $type,
                    'name' => $data['name'],
                    'image_url' => $data['images'][0]['url'] ?? null,
                    'total_tracks' => $data['tracks']['total'],
                    'status' => 'pending'
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to init download: ' . $e->getMessage()], 500);
            }
        }

        // If completed, we still allow re-downloading (retrying failed tracks or syncing)
        // logic below handles the dispatch


        // If not processing, start job
        if ($playlist->status !== 'processing') {
            \App\Jobs\DownloadSpotifyPlaylist::dispatch($playlist->id, $token);
            $playlist->update(['status' => 'processing']);
        }

        return response()->json([
            'status' => $playlist->status,
            'playlist' => $playlist
        ]);
    }

    public function status($id)
    {
        $playlist = SpotifyPlaylist::where('spotify_id', $id)->first();

        if (!$playlist) {
            return response()->json(['error' => 'Playlist not found'], 404);
        }

        return response()->json([
            'status' => $playlist->status,
            'processed_tracks' => $playlist->processed_tracks,
            'total_tracks' => $playlist->total_tracks,
            // Return playlist with tracks to show progress
            'playlist' => $playlist
        ]);
    }

    public function export($id)
    {
        $playlist = SpotifyPlaylist::where('spotify_id', $id)->firstOrFail();

        if ($playlist->status !== 'completed') {
            return response()->json(['error' => 'Playlist not ready for export'], 400);
        }

        $audioDir = storage_path('app/public/audio/' . $playlist->spotify_id);

        if (!is_dir($audioDir)) {
            return response()->json(['error' => 'Audio files not found'], 404);
        }

        $zipFileName = 'playlist_' . $playlist->spotify_id . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        if (file_exists($zipFilePath)) {
            return response()->download($zipFilePath);
        }

        // Fallback: Create zip if missing
        $zip = new \ZipArchive;
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($audioDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            $fileCount = 0;
            foreach ($files as $name => $file) {
                // Skip the .spotdl file but include mp3 and m3u
                if (!$file->isDir() && ($file->getExtension() === 'mp3' || $file->getExtension() === 'm3u')) {
                    $filePath = $file->getRealPath();
                    $relativePath = $file->getFilename();
                    $zip->addFile($filePath, $relativePath);
                    $fileCount++;
                }
            }
            $zip->close();

            if ($fileCount === 0) {
                return response()->json(['error' => 'No audio files found to zip'], 404);
            }
            return response()->download($zipFilePath);
        } else {
            return response()->json(['error' => 'Failed to create zip file'], 500);
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}
