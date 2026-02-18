<?php

namespace App\Jobs;

use App\Models\SpotifyPlaylist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class DownloadSpotifyPlaylist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $playlistId;
    protected $encryptedToken;

    public $timeout = 3600; // 1 hour

    public function __construct($playlistId, $encryptedToken)
    {
        $this->playlistId = $playlistId;
        $this->encryptedToken = $encryptedToken;
    }

    public function handle(): void
    {
        $playlist = SpotifyPlaylist::find($this->playlistId);

        if (!$playlist) {
            Log::error("Playlist not found for job: {$this->playlistId}");
            return;
        }

        $playlist->update([
            'status' => 'processing',
            'job_id' => $this->job->getJobId(),
            'processed_tracks' => 0
        ]);

        try {
            // 1. Fetch Metadata first (needed for UI list)
            $token = Crypt::decryptString($this->encryptedToken);
            $service = new \App\Services\SpotifyService($token);

            if ($playlist->type === 'album') {
                $allTracks = $service->getAllAlbumTracks($playlist->spotify_id);
            } else {
                $allTracks = $service->getAllPlaylistTracks($playlist->spotify_id);
            }

            // Transform tracks to include status
            $allTracks = array_map(function ($track) {
                $track['status'] = 'pending';
                return $track;
            }, $allTracks);

            $playlist->update([
                'tracks' => $allTracks,
                'total_tracks' => count($allTracks),
                'processed_tracks' => 0 // reset for audio download progress
            ]);

            // 2. Download Audio using spotify-dl
            $downloadPath = storage_path('app/public/audio/' . $playlist->spotify_id);

            if (!file_exists($downloadPath)) {
                mkdir($downloadPath, 0755, true);
            }

            Log::info("Starting download for playlist: {$playlist->spotify_id}");

            $m3uContent = ["#EXTM3U"];
            $processed = 0;
            $maxConcurrency = 5;
            $activeProcesses = []; // [index => ProcessInstance]

            // Maintain a persistent index map because pendingTracks will be shifted
            // Actually, let's just use an index pointer
            $trackIndices = array_keys($allTracks);
            $currentIndex = 0;
            $totalTracks = count($allTracks);

            while ($currentIndex < $totalTracks || count($activeProcesses) > 0) {
                // Fill up the pool
                while (count($activeProcesses) < $maxConcurrency && $currentIndex < $totalTracks) {
                    $index = $trackIndices[$currentIndex];
                    $track = $allTracks[$index];
                    $trackId = $track['id'] ?? null;

                    if (!$trackId) {
                        $allTracks[$index]['status'] = 'failed';
                        $playlist->update(['tracks' => $allTracks]); // Update immediately for failure
                        $currentIndex++;
                        continue;
                    }

                    $artistName = is_array($track['artists']) && count($track['artists']) > 0
                        ? (is_string($track['artists'][0]) ? $track['artists'][0] : ($track['artists'][0]['name'] ?? 'Unknown'))
                        : 'Unknown';
                    $trackName = $track['name'] ?? 'Unknown';

                    $metadata = [
                        'album' => $track['album'] ?? null,
                        'release_date' => $track['release_date'] ?? null,
                        'track_number' => $track['track_number'] ?? null,
                        'disc_number' => $track['disc_number'] ?? null,
                        'image' => $track['image'] ?? null,
                    ];

                    $info = $playlist->getDownloadCommand($trackName, $artistName, $downloadPath, $metadata);
                    $filename = $info['filename'] ?? "{$artistName} - {$trackName}.mp3";

                    // Check if file already exists
                    if (file_exists($info['output_path'])) {
                        Log::info("Skipping existing file but updating tags: {$filename}");

                        // Update tags for existing file
                        $playlist->tagFile($info['output_path'], [
                            'album' => $metadata['album'],
                            'release_date' => $metadata['release_date'],
                            'track_number' => $metadata['track_number'],
                            'disc_number' => $metadata['disc_number'],
                            'artist' => $artistName,
                            'title' => $trackName,
                            'image' => $track['image'] ?? null
                        ]);

                        $m3uContent[] = "#EXTINF:-1,{$artistName} - {$trackName}";
                        $m3uContent[] = $filename;
                        $allTracks[$index]['status'] = 'completed';

                        $processed++;
                        $playlist->update([
                            'processed_tracks' => $processed,
                            'tracks' => $allTracks
                        ]);

                        $currentIndex++;
                        continue;
                    }

                    // Start process
                    $process = Process::timeout(600)->start($info['command']);

                    $activeProcesses[$index] = [
                        'process' => $process,
                        'info' => $info,
                        'artist' => $artistName,
                        'title' => $trackName,
                        'metadata' => $metadata
                    ];

                    $allTracks[$index]['status'] = 'downloading';
                    $playlist->update(['tracks' => $allTracks]); // Update immediately for downloading status

                    $currentIndex++;
                }

                // Check for completions
                // We sleep a bit to avoid busy waiting
                usleep(100000); // 100ms

                foreach ($activeProcesses as $index => $data) {
                    $process = $data['process'];

                    if (!$process->running()) {
                        // Process finished
                        $result = $process->wait(); // Get result
                        $info = $data['info'];
                        $filename = $info['filename'];
                        $artistName = $data['artist'];
                        $trackName = $data['title'];

                        if ($result->successful()) {
                            Log::info("Successfully downloaded: {$filename}");

                            // Apply Spotify specific tags and cover art using ffmpeg
                            // This ensures we get the real Spotify cover, not just YouTube thumbnail
                            $metadata = $data['metadata'];
                            $metadata['artist'] = $artistName;
                            $metadata['title'] = $trackName;

                            $playlist->tagFile($info['output_path'], $metadata);

                            $m3uContent[] = "#EXTINF:-1,{$artistName} - {$trackName}";
                            $m3uContent[] = $filename;
                            $allTracks[$index]['status'] = 'completed';
                        } else {
                            Log::error("Failed to download {$filename}: " . $result->errorOutput());
                            $allTracks[$index]['status'] = 'failed';
                        }

                        $processed++;
                        $playlist->update([
                            'processed_tracks' => $processed,
                            'tracks' => $allTracks
                        ]);

                        // Remove from active
                        unset($activeProcesses[$index]);
                    }
                }
            }

            // Generate M3U file
            $m3uPath = $downloadPath . '/playlist.m3u';
            file_put_contents($m3uPath, implode(PHP_EOL, $m3uContent));

            // Create ZIP archive
            $zipFileName = 'playlist_' . $playlist->spotify_id . '.zip';
            $zipFilePath = storage_path('app/public/' . $zipFileName);

            $zip = new \ZipArchive;
            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($downloadPath),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    if (!$file->isDir() && ($file->getExtension() === 'mp3' || $file->getExtension() === 'm3u')) {
                        $filePath = $file->getRealPath();
                        $relativePath = $file->getFilename();
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                $zip->close();
                Log::info("Created ZIP for playlist: {$playlist->spotify_id}");
            } else {
                Log::error("Failed to create ZIP for playlist: {$playlist->spotify_id}");
            }

            $playlist->update([
                'status' => 'completed',
                'job_id' => null,
                'processed_tracks' => count($allTracks)
            ]);
        } catch (\Exception $e) {
            Log::error("Download job failed: " . $e->getMessage());
            $playlist->update(['status' => 'failed']);
        }
    }

    public function failed(\Throwable $exception)
    {
        $playlist = SpotifyPlaylist::find($this->playlistId);
        if ($playlist) {
            $playlist->update(['status' => 'failed']);
        }
    }
}
