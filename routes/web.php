<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/tools', function () {
    return Inertia::render('tools/Index');
})->name('tools.index');

Route::get('/tools/json', function () {
    return Inertia::render('tools/JsonTool');
})->name('tools.json');

Route::get('/', function () {
    $readme = Cache::remember('github_readme', 3600, function () {
        try {
            $markdown = file_get_contents('https://raw.githubusercontent.com/andrewgosselin/andrewgosselin/refs/heads/master/README.md');
            $parsedown = new \Parsedown();
            return $parsedown->text($markdown);
        } catch (\Exception $e) {
            return '<p>Unable to load README at this time.</p>';
        }
    });

    return Inertia::render('portfolio/Home', [
        'readme' => $readme
    ]);
})->name('home');

Route::get('/projects', function () {
    return Inertia::render('portfolio/Projects');
})->name('projects');

Route::get('/resume', function () {
    return Inertia::render('portfolio/Resume');
})->name('resume');

Route::get('/ideas', function () {
    return Inertia::render('portfolio/Ideas');
})->name('ideas');

Route::get('/arcade', function () {
    return Inertia::render('portfolio/Arcade');
})->name('arcade');

Route::get('/arcade/{slug}', function ($slug) {
    $games = config('games.games');
    $game = collect($games)->firstWhere('slug', $slug);

    if (!$game) {
        abort(404);
    }

    return Inertia::render('portfolio/GameView', [
        'game' => $game
    ]);
})->name('arcade.view');

// Route::get('/docs', function () {
//     return Inertia::render('portfolio/ApiDocs');
// })->name('api.docs');

