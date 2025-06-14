<?php

namespace App\Jobs;

use App\Enums\SpiderType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RoachPHP\Roach;

class RunSpiderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly SpiderType $spiderType,
        private readonly ?array $overrides = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $spiderClass = $this->spiderType->getSpiderClass();
            
            Log::info('Starting spider job', [
                'spider_type' => $this->spiderType->value,
                'spider_name' => $this->spiderType->getDisplayName(),
                'overrides' => $this->overrides
            ]);
            
            Roach::startSpider($spiderClass, $this->overrides);
            
            Log::info('Spider job completed successfully', [
                'spider_type' => $this->spiderType->value
            ]);
        } catch (\Throwable $e) {
            Log::error('Spider job failed', [
                'spider_type' => $this->spiderType->value,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
