<?php

namespace App\Http\Controllers;

use App\Enums\SpiderType;
use App\Http\Requests\RunSpiderRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class ScraperController extends Controller
{
    use ApiResponse;
    
    /**
     * Run a specific spider
     * 
     * @param RunSpiderRequest $request
     * @return JsonResponse
     */
    public function __invoke(RunSpiderRequest $request): JsonResponse
    {
        try {
            // Get the spider type from the request and convert to enum
            $spiderTypeValue = $request->validated('spider_type');
            $spiderType = SpiderType::from($spiderTypeValue);
            
            // Get the spider class from the enum
            $spiderClass = $spiderType->getSpiderClass();
            
            // Configure overrides if start_url is provided
            $overrides = $this->buildOverrides($request);
            
            // Run the spider
            Roach::startSpider($spiderClass, $overrides);
            
            return $this->successResponse('Spider started successfully', [
                'spider_type' => $spiderType->value,
                'spider_name' => $spiderType->getDisplayName()
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Failed to run spider: ' . $e->getMessage(), [
                'spider_type' => $request->validated('spider_type'),
                'exception' => $e,
            ]);
            
            return $this->errorResponse('Failed to run spider: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Build overrides configuration for the spider
     * 
     * @param RunSpiderRequest $request
     * @return Overrides|null
     */
    private function buildOverrides(RunSpiderRequest $request): ?Overrides
    {
        if ($request->has('start_url')) {
            return new Overrides(
                startUrls: [$request->validated('start_url')]
            );
        }
        
        return null;
    }
}
