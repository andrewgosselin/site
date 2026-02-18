<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpotifyPlaylist extends Model
{
    use HasFactory;

    protected $fillable = [
        'spotify_id',
        'type',
        'name',
        'image_url',
        'total_tracks',
        'processed_tracks',
        'status',
        'tracks',
        'job_id'
    ];

    protected $casts = [
        'tracks' => 'array',
    ];

    public function getDownloadCommand(string $title, string $artist, string $downloadFolder, array $metadata = [])
    {
        // Ensure download folder exists
        if (!file_exists($downloadFolder)) {
            mkdir($downloadFolder, 0755, true);
        }

        // Sanitize filename
        $safeArtist = trim(str_replace(['/', '\\', '?', '%', '*', ':', '|', '"', '<', '>'], '', $artist));
        $safeTitle = trim(str_replace(['/', '\\', '?', '%', '*', ':', '|', '"', '<', '>'], '', $title));
        $filename = "{$safeArtist} - {$safeTitle}.mp3";
        $outputPath = $downloadFolder . '/' . $filename;

        $cookiePath = storage_path('app/generic-yt-cookie.txt');

        // Command: yt-dlp -x --audio-format mp3 --output "path/to/file.mp3" "ytsearch1:artist title"
        // We'll use the venv python to run yt-dlp module if possible, or just the binary.
        // The check `ls ~/.local/share/spotify-downloader/venv/bin/yt-dlp` passed.
        $command = [
            'yt-dlp',
            '--no-overwrites',
            '-x',
            '--audio-format',
            'mp3',
            '--output',
            $outputPath,
            "--default-search",
            "ytsearch",
            // Embed Metadata
            '--embed-metadata',
            '--cookies',
            $cookiePath
        ];

        // Add FFmpeg metadata args if provided
        if (!empty($metadata)) {
            $ffmpegArgs = [];

            if (isset($metadata['album'])) {
                $album = str_replace('"', '\"', $metadata['album']);
                $ffmpegArgs[] = "-metadata album=\"{$album}\"";
            }
            if (isset($metadata['release_date'])) {
                $date = str_replace('"', '\"', $metadata['release_date']);
                $ffmpegArgs[] = "-metadata date=\"{$date}\"";
                // also year
                $year = substr($date, 0, 4);
                $ffmpegArgs[] = "-metadata year=\"{$year}\"";
            }
            if (isset($metadata['track_number'])) {
                $ffmpegArgs[] = "-metadata track=\"{$metadata['track_number']}\"";
            }
            if (isset($metadata['disc_number'])) {
                $ffmpegArgs[] = "-metadata disc=\"{$metadata['disc_number']}\"";
            }

            // Allow overriding artist/title from metadata if provided, otherwise yt-dlp might use video title
            $metaArtist = str_replace('"', '\"', $artist);
            $metaTitle = str_replace('"', '\"', $title);
            $ffmpegArgs[] = "-metadata artist=\"{$metaArtist}\"";
            $ffmpegArgs[] = "-metadata title=\"{$metaTitle}\"";


            if (!empty($ffmpegArgs)) {
                $argsString = "ffmpeg:" . implode(' ', $ffmpegArgs);
                $command[] = '--postprocessor-args';
                $command[] = $argsString;
            }
        }

        $command[] = "{$artist} - {$title}";

        return [
            'command' => $command,
            'output_path' => $outputPath,
            'filename' => $filename
        ];
    }

    public function downloadTrack(string $title, string $artist, string $downloadFolder)
    {
        $info = $this->getDownloadCommand($title, $artist, $downloadFolder);

        \Illuminate\Support\Facades\Log::info("Executing command: " . implode(' ', $info['command']));

        $result = \Illuminate\Support\Facades\Process::timeout(600)->run($info['command']);

        if ($result->successful()) {
            \Illuminate\Support\Facades\Log::info("Successfully downloaded: {$info['filename']}");
            return $info['output_path'];
        } else {
            \Illuminate\Support\Facades\Log::error("Failed to download {$info['filename']}: " . $result->errorOutput());
            return false;
        }
    }

    public function tagFile(string $filePath, array $metadata)
    {
        if (empty($metadata) || !file_exists($filePath)) {
            return;
        }

        $ffmpegArgs = [];

        if (isset($metadata['album'])) {
            $album = str_replace('"', '\"', $metadata['album']);
            $ffmpegArgs[] = "-metadata album=\"{$album}\"";
        }
        if (isset($metadata['release_date'])) {
            $date = str_replace('"', '\"', $metadata['release_date']);
            $ffmpegArgs[] = "-metadata date=\"{$date}\"";
            $year = substr($date, 0, 4);
            $ffmpegArgs[] = "-metadata year=\"{$year}\"";
        }
        if (isset($metadata['track_number'])) {
            $ffmpegArgs[] = "-metadata track=\"{$metadata['track_number']}\"";
        }
        if (isset($metadata['disc_number'])) {
            $ffmpegArgs[] = "-metadata disc=\"{$metadata['disc_number']}\"";
        }

        // Use provided artist/title if valid, to correct potentially wrong ones from youtube match
        // Extract filename parts or pass them in? For now rely on file having something, but we can overwrite.
        if (isset($metadata['artist'])) {
            $metaArtist = str_replace('"', '\"', $metadata['artist']);
            $ffmpegArgs[] = "-metadata artist=\"{$metaArtist}\"";
        }
        if (isset($metadata['title'])) {
            $metaTitle = str_replace('"', '\"', $metadata['title']);
            $ffmpegArgs[] = "-metadata title=\"{$metaTitle}\"";
        }

        if (empty($ffmpegArgs) && !isset($metadata['image'])) {
            return;
        }

        $tempPath = $filePath . '.temp.mp3';
        $coverPath = null;
        $inputs = "-i \"{$filePath}\"";
        $maps = "-map 0";
        $id3Args = "-id3v2_version 3"; // Ensure ID3v2.3 for maximum compatibility (Windows/iTunes)

        // Handle Cover Art
        if (isset($metadata['image']) && $metadata['image']) {
            try {
                $coverContent = file_get_contents($metadata['image']);
                if ($coverContent) {
                    $coverPath = sys_get_temp_dir() . '/cover_' . md5($filePath) . '.jpg';
                    file_put_contents($coverPath, $coverContent);

                    // Add cover as second input
                    $inputs .= " -i \"{$coverPath}\"";
                    // Map audio from 0, video (image) from 1
                    $maps = "-map 0:a -map 1:v";
                    // Set metadata for cover
                    $id3Args .= " -metadata:s:v title=\"Album cover\" -metadata:s:v comment=\"Cover (front)\"";
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to download cover art: " . $e->getMessage());
            }
        }

        $argsString = implode(' ', $ffmpegArgs);

        // ffmpeg -y -i input.mp3 [-i cover.jpg] [-map 0:a -map 1:v] -metadata ... -c copy -id3v2_version 3 temp.mp3
        $command = "ffmpeg -y {$inputs} {$maps} {$argsString} -c copy {$id3Args} \"{$tempPath}\" && mv \"{$tempPath}\" \"{$filePath}\"";

        // Clean up cover art
        if ($coverPath) {
            $command .= " && rm \"{$coverPath}\"";
        }

        \Illuminate\Support\Facades\Log::info("Tagging file via ffmpeg: {$filePath}");

        \Illuminate\Support\Facades\Process::timeout(60)->run($command);
    }
}
