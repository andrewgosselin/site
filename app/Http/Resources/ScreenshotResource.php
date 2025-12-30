<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScreenshotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'original_url' => $this['url'],
            'screenshot_url' => $this['screenshot_url'],
            'meta' => [
                'dimensions' => $this['dimensions'],
                'hash' => $this['hash'],
                'cached_at' => $this['cached_at'],
            ],
        ];
    }
}
