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
use RoachPHP\Spider\Configuration\Overrides;

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
    /**
     * The spider type to run
     */
    protected SpiderType $spiderType;
    
    /**
     * Optional overrides for the spider
     */
    protected ?array $overrides;
    
    /**
     * Create a new job instance.
     */
    public function __construct(
        SpiderType $spiderType,
        ?array $overrides = []
    ) {
        $this->spiderType = $spiderType;
        $this->overrides = $overrides;
    }
    
    /**
     * Get the spider type
     */
    public function getSpiderType(): SpiderType
    {
        return $this->spiderType;
    }
    
    /**
     * Get the overrides
     */
    public function getOverrides(): ?array
    {
        return $this->overrides;
    }

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
                'overrides' => $this->overrides ?? []
            ]);
            
            // Create proper Overrides object if we have overrides
            $spiderOverrides = null;
            if (!empty($this->overrides)) {
                $spiderOverrides = new Overrides();
                
                // Handle startUrls override
                if (isset($this->overrides['startUrls'])) {
                    $spiderOverrides->startUrls = $this->overrides['startUrls'];
                }
            }
            
            Roach::startSpider($spiderClass, $spiderOverrides);
            
            Log::info('Spider job completed successfully', [
                'spider_type' => $this->spiderType->value
            ]);
        } catch (\Throwable $e) {
            Log::error('Spider job failed', [
                'spider_type' => $this->spiderType->value ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
