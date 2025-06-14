<?php

use App\Console\Commands\RunDailySpiders;
use App\Enums\SpiderType;
use App\Jobs\RunSpiderJob;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    // Fake the queue to prevent actual job dispatching
    Queue::fake();
});

test('command dispatches all spiders when --all flag is used', function () {
    // Act
    $this->artisan('spiders:run-daily --all')
        ->expectsOutput('Starting daily spider run...')
        ->expectsOutput('Dispatching jobs for all ' . count(SpiderType::cases()) . ' spiders...')
        ->expectsOutput('Daily spider jobs dispatched successfully.')
        ->assertSuccessful();
    
    // Assert - Verify all spider types were dispatched
    foreach (SpiderType::cases() as $spiderType) {
        Queue::assertPushed(RunSpiderJob::class, function ($job) use ($spiderType) {
            return $job->spiderType === $spiderType;
        });
    }
});

test('command dispatches specific spider when --type is provided', function () {
    // Act
    $this->artisan('spiders:run-daily --type=' . SpiderType::AMAZON->value)
        ->expectsOutput('Starting daily spider run...')
        ->expectsOutput('Dispatching job for spider: ' . SpiderType::AMAZON->getDisplayName())
        ->expectsOutput('Daily spider jobs dispatched successfully.')
        ->assertSuccessful();
    
    // Assert - Verify only the specified spider was dispatched
    Queue::assertPushed(RunSpiderJob::class, 1);
    Queue::assertPushed(RunSpiderJob::class, function ($job) {
        return $job->spiderType === SpiderType::AMAZON;
    });
});

test('command dispatches default spiders when no options are provided', function () {
    // Act
    $this->artisan('spiders:run-daily')
        ->expectsOutput('Starting daily spider run...')
        ->expectsOutput('Dispatching jobs for default spiders...')
        ->expectsOutput('Daily spider jobs dispatched successfully.')
        ->assertSuccessful();
    
    // Assert - Verify only the default spiders were dispatched
    Queue::assertPushed(RunSpiderJob::class, 1); // Only AMAZON is in the default list
    Queue::assertPushed(RunSpiderJob::class, function ($job) {
        return $job->spiderType === SpiderType::AMAZON;
    });
});

test('command handles invalid spider type', function () {
    // Act & Assert
    $this->artisan('spiders:run-daily --type=invalid_type')
        ->expectsOutput('Starting daily spider run...')
        ->expectsOutput('Invalid spider type: invalid_type')
        ->assertFailed();
    
    // Verify no jobs were dispatched
    Queue::assertNothingPushed();
});
