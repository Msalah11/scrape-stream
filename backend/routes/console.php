<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule spiders to run daily at noon (12:00)
Schedule::command('spiders:run-daily --all')
    ->dailyAt('12:00')
    ->name('Run all spiders daily')
    ->withoutOverlapping()
    ->onSuccess(function () {
        info('Daily spider run completed successfully');
    })
    ->onFailure(function () {
        info('Daily spider run failed');
    });
