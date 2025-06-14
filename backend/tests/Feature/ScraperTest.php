<?php

use App\Enums\SpiderType;
use App\Jobs\RunSpiderJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Fake the queue to prevent actual job dispatching
    Queue::fake();
});

test('can dispatch spider job', function () {
    // Act
    $response = $this->postJson('/api/scraper/run', [
        'spider_type' => SpiderType::AMAZON->value,
    ]);
    
    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Spider job dispatched successfully',
            'data' => [
                'spider_type' => SpiderType::AMAZON->value,
                'background' => true,
                'status' => 'queued'
            ]
        ]);
    
    // Verify the job was dispatched
    Queue::assertPushed(RunSpiderJob::class, function ($job) {
        return $job->spiderType === SpiderType::AMAZON;
    });
});

test('can dispatch spider job with custom start url', function () {
    // Arrange
    $startUrl = 'https://example.com/products';
    
    // Act
    $response = $this->postJson('/api/scraper/run', [
        'spider_type' => SpiderType::AMAZON->value,
        'start_url' => $startUrl
    ]);
    
    // Assert
    $response->assertStatus(200);
    
    // Verify the job was dispatched with correct overrides
    Queue::assertPushed(RunSpiderJob::class, function ($job) use ($startUrl) {
        return $job->spiderType === SpiderType::AMAZON && 
               isset($job->overrides['startUrls']) && 
               $job->overrides['startUrls'][0] === $startUrl;
    });
});

test('returns error for invalid spider type', function () {
    // Act
    $response = $this->postJson('/api/scraper/run', [
        'spider_type' => 'invalid_type',
    ]);
    
    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['spider_type']);
    
    // Verify no job was dispatched
    Queue::assertNothingPushed();
});

test('requires spider_type parameter', function () {
    // Act
    $response = $this->postJson('/api/scraper/run', []);
    
    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['spider_type']);
    
    // Verify no job was dispatched
    Queue::assertNothingPushed();
});
