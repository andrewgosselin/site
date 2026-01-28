<?php

namespace App\Http\Controllers\Portfolio;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class PortfolioController extends Controller
{
    public function home()
    {
        $readme = Cache::remember('github_readme', 3600, function () {
            try {
                $markdown = file_get_contents('https://raw.githubusercontent.com/andrewgosselin/andrewgosselin/refs/heads/master/README.md');
                $parsedown = new \Parsedown();
                $html = $parsedown->text($markdown);
                
                // Fix accessibility: Add aria-labels to known image-only links
                $html = str_replace(
                    'href="https://cyrexag.com"', 
                    'href="https://cyrexag.com" aria-label="Cyrex Website" rel="noopener noreferrer"', 
                    $html
                );

                // Fix accessibility: Add alt text to profile image
                $html = preg_replace(
                    '/<img([^>]+)src="[^"]*avatars\.githubusercontent\.com[^"]*"([^>]*)>/i',
                    '<img$1alt="Andrew Gosselin" src="https://images.weserv.nl/?url=avatars.githubusercontent.com/u/32310481?v=4"$2>',
                    $html
                );

                return $html;
            } catch (\Exception $e) {
                return '<p>Unable to load README at this time.</p>';
            }
        });

        return Inertia::render('portfolio/Home', [
            'readme' => $readme
        ]);
    }

    public function projects()
    {
        return Inertia::render('portfolio/Projects');
    }

    public function resume()
    {
        return Inertia::render('portfolio/Resume');
    }

    public function ideas()
    {
        return Inertia::render('portfolio/Ideas');
    }

    public function arcade()
    {
        return Inertia::render('portfolio/Arcade');
    }

    public function arcadeView($slug)
    {
        $games = config('games.games');
        $game = collect($games)->firstWhere('slug', $slug);

        if (!$game) {
            abort(404);
        }

        return Inertia::render('portfolio/GameView', [
            'game' => $game
        ]);
    }

    public function apiDocs()
    {
        return Inertia::render('portfolio/ApiDocs');
    }
}
