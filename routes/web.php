<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('portfolio/Home');
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

Route::get('/docs', function () {
    return Inertia::render('portfolio/ApiDocs');
})->name('api.docs');

