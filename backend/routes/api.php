<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScraperController;
use App\Http\Controllers\ProductController;

Route::prefix('scraper')->group(function () {
    Route::post('/run', [ScraperController::class, '__invoke']);
});