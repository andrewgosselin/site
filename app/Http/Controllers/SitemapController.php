<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];
        $baseUrl = config('app.url');

        // 1. Static Routes from SEO Config
        $seoRoutes = config('seo.routes', []);
        foreach ($seoRoutes as $routeName => $data) {
            // Filter: Only allow 'home' and 'tools.*'
            if ($routeName !== 'home' && !str_starts_with($routeName, 'tools.')) {
                continue;
            }

            if (Route::has($routeName)) {
                $urls[] = [
                    'loc' => route($routeName),
                    'lastmod' => date('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority' => $routeName === 'home' ? '1.0' : '0.8',
                ];
            }
        }

        // Generate XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . $url['loc'] . '</loc>';
            $xml .= '<lastmod>' . $url['lastmod'] . '</lastmod>';
            $xml .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
            $xml .= '<priority>' . $url['priority'] . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'text/xml');
    }
}
