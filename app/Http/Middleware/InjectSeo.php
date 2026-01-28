<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class InjectSeo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = Route::currentRouteName();
        $config = config('seo.routes.' . $routeName, []);
        $defaults = config('seo.defaults', []);

        $seo = [
            'title' => $config['title'] ?? null, // Let app name append automatically if just title
            'description' => $config['description'] ?? $defaults['description'],
            'keywords' => $config['keywords'] ?? $defaults['keywords'],
            'image' => $config['image'] ?? $defaults['image'],
            'type' => $config['type'] ?? $defaults['type'],
            'twitter_card' => $config['twitter_card'] ?? $defaults['twitter_card'],
            'url' => app()->environment('production') 
                ? str_replace('http://', 'https://', $request->url())
                : $request->url(),
        ];

        // Format title: "Page Title - AppName" or just "AppName"
        $appName = config('app.name', 'Andrew Gosselin');
        if (!empty($seo['title'])) {
            $seo['full_title'] = "{$seo['title']} - {$appName}";
        } else {
            $seo['full_title'] = $defaults['title'];
        }

        view()->share('seo', $seo);
        \Inertia\Inertia::share('seo', $seo);

        return $next($request);
    }
}
