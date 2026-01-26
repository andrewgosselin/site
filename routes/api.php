<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\UserController;

Route::prefix('v1')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    
    // Route::post('/screenshots', [ScreenshotController::class, 'store']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [UserController::class, 'me']);
    });
});
