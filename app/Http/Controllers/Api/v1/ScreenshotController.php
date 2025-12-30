<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\ScreenshotResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Str;

class ScreenshotController extends BaseApiController
{
    /**
     * Generate a screenshot from a URL.
     * 
     * Captures a high-quality screenshot of any public website.
     * Results are cached for 24 hours to ensure performance.
     * 
     * @group Tools
     * @unauthenticated
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url'],
            'width' => ['nullable', 'integer', 'min:320', 'max:3840'],
            'height' => ['nullable', 'integer', 'min:240', 'max:2160'],
            'delay' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'force' => ['nullable', 'boolean'],
        ]);

        $url = $validated['url'];
        $width = $validated['width'] ?? 1920;
        $height = $validated['height'] ?? 1080;
        $delay = $validated['delay'] ?? 0;
        $force = $request->boolean('force');

        // Create a unique hash for caching
        $hash = md5(implode('|', [$url, $width, $height, $delay]));
        $filename = "screenshots/{$hash}.jpg";
        $ttl = 24 * 60 * 60; // 24 hours in seconds

        $exists = Storage::disk('public')->exists($filename);
        $isStale = $exists && (time() - Storage::disk('public')->lastModified($filename) > $ttl);

        if (!$exists || $isStale || $force) {
            try {
                $browserShot = Browsershot::url($url)
                    ->windowSize($width, $height)
                    ->setDelay($delay)
                    ->setScreenshotType('jpeg', 90);

                // On Windows, browsershot might need some specific paths or flags
                // but usually it works if puppeteer is in path.
                
                $imageData = $browserShot->screenshot();
                Storage::disk('public')->put($filename, $imageData);
            } catch (\Exception $e) {
                return $this->error("Failed to capture screenshot: " . $e->getMessage(), 500);
            }
        }

        return $this->success(new ScreenshotResource([
            'url' => $url,
            'screenshot_url' => asset(Storage::url($filename)),
            'hash' => $hash,
            'dimensions' => "{$width}x{$height}",
            'cached_at' => date('c', Storage::disk('public')->lastModified($filename)),
        ]));
    }
}
