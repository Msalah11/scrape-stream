<?php

use App\Enums\SpiderType;
use App\Jobs\RunSpiderJob;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

beforeEach(function () {
    // Mock the Roach facade
    $this->mock = Mockery::mock('alias:' . Roach::class);
});

test('job runs spider with correct class', function () {
    // Arrange
    $spiderType = SpiderType::AMAZON;
    $spiderClass = $spiderType->getSpiderClass();
    
    // Expect
    $this->mock->shouldReceive('startSpider')
        ->once()
        ->with($spiderClass, []);
    
    // Act
    $job = new RunSpiderJob($spiderType);
    $job->handle();
});

test('job runs spider with overrides', function () {
    // Arrange
    $spiderType = SpiderType::AMAZON;
    $spiderClass = $spiderType->getSpiderClass();
    $overrides = ['startUrls' => ['https://example.com']];
    
    // Expect
    $this->mock->shouldReceive('startSpider')
        ->once()
        ->with($spiderClass, $overrides);
    
    // Act
    $job = new RunSpiderJob($spiderType, $overrides);
    $job->handle();
});

test('job logs error when spider fails', function () {
    // Arrange
    $spiderType = SpiderType::AMAZON;
    Log::spy();
    
    // Expect
    $this->mock->shouldReceive('startSpider')
        ->once()
        ->andThrow(new Exception('Spider failed'));
    
    // Act & Assert
    try {
        $job = new RunSpiderJob($spiderType);
        $job->handle();
        $this->fail('Exception was not thrown');
    } catch (Exception $e) {
        // Verify exception is rethrown
        expect($e->getMessage())->toBe('Spider failed');
        
        // Verify error was logged
        Log::shouldHaveReceived('error')
            ->with('Spider job failed', Mockery::subset([
                'spider_type' => $spiderType->value,
                'error' => 'Spider failed'
            ]));
    }
});
