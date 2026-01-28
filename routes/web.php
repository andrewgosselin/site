<?php

use App\Http\Controllers\Portfolio\PortfolioController;
use App\Http\Controllers\Tools\MetadataController;
use App\Http\Controllers\Tools\ToolsController;
use App\Http\Controllers\INDController;
use Illuminate\Support\Facades\Route;

// Tools Routes
Route::prefix('tools')->name('tools.')->group(function () {
    Route::get('/', [ToolsController::class, 'index'])->name('index');
    Route::get('/json', [ToolsController::class, 'json'])->name('json');
    Route::get('/encoder', [ToolsController::class, 'encoder'])->name('encoder');
    Route::get('/generator', [ToolsController::class, 'generator'])->name('generator');
    Route::get('/seo-checker', [ToolsController::class, 'seoChecker'])->name('seo-checker');
    Route::get('/image', [ToolsController::class, 'image'])->name('image');
    Route::get('/ind', [INDController::class, 'index'])->name('ind');
});

// Tools API Routes
Route::post('/api/tools/fetch-metadata', [MetadataController::class, 'fetch']);
Route::get('/api/tools/ind-register', [INDController::class, 'fetchRegister']);

// Portfolio Routes
Route::get('/', [PortfolioController::class, 'home'])->name('home');
Route::get('/projects', [PortfolioController::class, 'projects'])->name('projects');
Route::get('/resume', [PortfolioController::class, 'resume'])->name('resume');
Route::get('/ideas', [PortfolioController::class, 'ideas'])->name('ideas');
Route::get('/arcade', [PortfolioController::class, 'arcade'])->name('arcade');
Route::get('/arcade/{slug}', [PortfolioController::class, 'arcadeView'])->name('arcade.view');

Route::get('/docs', [PortfolioController::class, 'apiDocs'])->name('api.docs');


Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);
