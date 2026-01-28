<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use DOMDocument;
use DOMXPath;

class MetadataController extends Controller
{
    public function fetch(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = $request->input('url');

        try {
            // Fetch the URL content
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; MetadataBot/1.0)'
                ])
                ->get($url);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to fetch URL: ' . $response->status()
                ], 400);
            }

            $html = $response->body();

            // Parse HTML
            $metadata = $this->parseMetadata($html, $url);

            return response()->json($metadata);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching URL: ' . $e->getMessage()
            ], 500);
        }
    }

    private function parseMetadata($html, $url)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $metadata = [
            'title' => $this->getTitle($dom, $xpath),
            'description' => $this->getMetaContent($xpath, 'description'),
            'favicon' => $this->getFavicon($dom, $xpath, $url),
            'openGraph' => $this->getOpenGraph($xpath),
            'twitter' => $this->getTwitterCard($xpath),
            'structuredData' => $this->getStructuredData($dom),
            'allMeta' => $this->getAllMetaTags($xpath)
        ];

        return $metadata;
    }

    private function getTitle($dom, $xpath)
    {
        // Try og:title first, then <title>
        $ogTitle = $this->getMetaContent($xpath, 'og:title', 'property');
        if ($ogTitle)
            return $ogTitle;

        $titleNodes = $dom->getElementsByTagName('title');
        if ($titleNodes->length > 0) {
            return $titleNodes->item(0)->textContent;
        }

        return null;
    }

    private function getMetaContent($xpath, $name, $attribute = 'name')
    {
        $query = "//meta[@{$attribute}='{$name}']";
        $nodes = $xpath->query($query);

        if ($nodes->length > 0) {
            return $nodes->item(0)->getAttribute('content');
        }

        return null;
    }

    private function getFavicon($dom, $xpath, $baseUrl)
    {
        // Try various favicon link types
        $queries = [
            "//link[@rel='icon']",
            "//link[@rel='shortcut icon']",
            "//link[@rel='apple-touch-icon']"
        ];

        foreach ($queries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $href = $nodes->item(0)->getAttribute('href');
                return $this->resolveUrl($href, $baseUrl);
            }
        }

        // Default to /favicon.ico
        $parsedUrl = parse_url($baseUrl);
        return $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/favicon.ico';
    }

    private function getOpenGraph($xpath)
    {
        $og = [];
        $properties = ['title', 'description', 'image', 'url', 'type', 'site_name'];

        foreach ($properties as $prop) {
            $value = $this->getMetaContent($xpath, "og:{$prop}", 'property');
            if ($value) {
                $og[$prop] = $value;
            }
        }

        return $og;
    }

    private function getTwitterCard($xpath)
    {
        $twitter = [];
        $properties = ['card', 'title', 'description', 'image', 'site', 'creator'];

        foreach ($properties as $prop) {
            $value = $this->getMetaContent($xpath, "twitter:{$prop}");
            if ($value) {
                $twitter[$prop] = $value;
            }
        }

        return $twitter;
    }

    private function getStructuredData($dom)
    {
        $scripts = $dom->getElementsByTagName('script');
        $structuredData = [];

        foreach ($scripts as $script) {
            $type = $script->getAttribute('type');
            if ($type === 'application/ld+json') {
                $content = $script->textContent;
                $decoded = json_decode($content, true);
                if ($decoded) {
                    $structuredData[] = $decoded;
                }
            }
        }

        return $structuredData;
    }

    private function getAllMetaTags($xpath)
    {
        $allMeta = [];
        $nodes = $xpath->query('//meta');

        foreach ($nodes as $node) {
            $name = $node->getAttribute('name') ?: $node->getAttribute('property');
            $content = $node->getAttribute('content');

            if ($name && $content) {
                $allMeta[] = [
                    'name' => $name,
                    'content' => $content
                ];
            }
        }

        return $allMeta;
    }

    private function resolveUrl($url, $baseUrl)
    {
        // If already absolute, return as-is
        if (parse_url($url, PHP_URL_SCHEME) !== null) {
            return $url;
        }

        $base = parse_url($baseUrl);

        // Handle protocol-relative URLs
        if (substr($url, 0, 2) === '//') {
            return $base['scheme'] . ':' . $url;
        }

        // Handle absolute paths
        if (substr($url, 0, 1) === '/') {
            return $base['scheme'] . '://' . $base['host'] . $url;
        }

        // Handle relative paths (simplified)
        return $base['scheme'] . '://' . $base['host'] . '/' . $url;
    }
}
