<?php

use App\Http\Controllers\Portfolio\PortfolioController;
use App\Http\Controllers\Tools\MetadataController;
use App\Http\Controllers\Tools\ToolsController;
use App\Http\Controllers\Tools\INDController;
use App\Http\Controllers\Tools\SpotifyController;
use Illuminate\Support\Facades\Route;

// Tools Routes
Route::prefix('tools')->name('tools.')->group(function () {
    Route::get('/', [ToolsController::class, 'index'])->name('index');
    Route::get('/json', [ToolsController::class, 'json'])->name('json');
    Route::get('/encoder', [ToolsController::class, 'encoder'])->name('encoder');
    Route::get('/generator', [ToolsController::class, 'generator'])->name('generator');
    Route::get('/seo-checker', [ToolsController::class, 'seoChecker'])->name('seo-checker');
    Route::get('/image', [ToolsController::class, 'image'])->name('image');
    Route::get('/pdf', [ToolsController::class, 'pdf'])->name('pdf');
    Route::get('/ind', [INDController::class, 'index'])->name('ind');

    // Spotify Tool
    Route::get('/spotify', [SpotifyController::class, 'index'])->name('spotify.index');
    Route::get('/spotify/auth/redirect', [SpotifyController::class, 'redirect'])->name('spotify.redirect');
    Route::get('/spotify/auth/callback', [SpotifyController::class, 'callback'])->name('spotify.callback');
});

// Tools API Routes
Route::post('/api/tools/fetch-metadata', [MetadataController::class, 'fetch']);
Route::post('/api/tools/pdf/translate-text', [ToolsController::class, 'translateText']);
Route::get('/api/tools/ind-register', [INDController::class, 'fetchRegister']);
Route::get('/api/tools/ind-search', [INDController::class, 'search']);
Route::get('/api/tools/spotify/playlists', [SpotifyController::class, 'playlists'])->name('api.spotify.playlists');
Route::get('/api/tools/spotify/playlists/{id}/tracks', [SpotifyController::class, 'tracks'])->name('api.spotify.tracks');
Route::get('/api/tools/spotify/playlist/{id}', [SpotifyController::class, 'fetchPlaylist'])->name('api.spotify.playlist.fetch');
Route::post('/api/tools/spotify/playlist/{id}/download', [SpotifyController::class, 'download'])->name('api.spotify.playlist.download');
Route::get('/api/tools/spotify/playlist/{id}/status', [SpotifyController::class, 'status'])->name('api.spotify.playlist.status');
Route::get('/api/tools/spotify/playlist/{id}/export', [SpotifyController::class, 'export'])->name('tools.spotify.export');

// Portfolio Routes
Route::get('/', [PortfolioController::class, 'home'])->name('home');
Route::get('/projects', [PortfolioController::class, 'projects'])->name('projects');
Route::get('/resume', [PortfolioController::class, 'resume'])->name('resume');
Route::get('/ideas', [PortfolioController::class, 'ideas'])->name('ideas');
Route::get('/arcade', [PortfolioController::class, 'arcade'])->name('arcade');
Route::get('/arcade/{slug}', [PortfolioController::class, 'arcadeView'])->name('arcade.view');

Route::get('/docs', [PortfolioController::class, 'apiDocs'])->name('api.docs');


Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);
