<?php

namespace App\Console\Commands;

use App\Enums\SpiderType;
use App\Jobs\RunSpiderJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunDailySpiders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spiders:run-daily {--type= : Specific spider type to run} {--all : Run all available spiders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run spiders on a daily schedule';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting daily spider run...');
        
        try {
            if ($this->option('all')) {
                $this->runAllSpiders();
            } elseif ($type = $this->option('type')) {
                $this->runSpecificSpider($type);
            } else {
                $this->runDefaultSpiders();
            }
            
            $this->info('Daily spider jobs dispatched successfully.');
        } catch (\Throwable $e) {
            $this->error('Failed to dispatch spider jobs: ' . $e->getMessage());
            Log::error('Failed to dispatch daily spider jobs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Run all available spiders
     */
    private function runAllSpiders(): void
    {
        $spiderTypes = SpiderType::cases();
        $this->info('Dispatching jobs for all ' . count($spiderTypes) . ' spiders...');
        
        foreach ($spiderTypes as $spiderType) {
            $this->dispatchSpider($spiderType);
        }
    }
    
    /**
     * Run a specific spider by type
     */
    private function runSpecificSpider(string $type): void
    {
        try {
            $spiderType = SpiderType::from($type);
            $this->info('Dispatching job for spider: ' . $spiderType->getDisplayName());
            $this->dispatchSpider($spiderType);
        } catch (\ValueError $e) {
            $this->error("Invalid spider type: $type");
            $this->info('Available spider types: ' . implode(', ', array_column(SpiderType::cases(), 'value')));
            throw $e;
        }
    }
    
    /**
     * Run default set of spiders
     */
    private function runDefaultSpiders(): void
    {
        $defaultSpiders = [
            SpiderType::AMAZON,
        ];
        
        $this->info('Dispatching jobs for default spiders...');
        
        foreach ($defaultSpiders as $spiderType) {
            $this->dispatchSpider($spiderType);
        }
    }
    
    /**
     * Dispatch a spider job
     */
    private function dispatchSpider(SpiderType $spiderType): void
    {
        $this->line('Dispatching ' . $spiderType->getDisplayName() . ' spider...');
        RunSpiderJob::dispatch($spiderType);
    }
}
