<?php

namespace App\Http\Controllers;

use App\Enums\SpiderType;
use App\Http\Requests\RunSpiderRequest;
use App\Jobs\RunSpiderJob;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ScraperController extends Controller
{
    use ApiResponse;

    /**
     * Run a spider based on the provided type
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
            
            // Configure overrides if start_url is provided
            $overrides = $this->buildOverrides($request);
            
            // Dispatch the spider job to run in the background
            RunSpiderJob::dispatch($spiderType, $overrides);
            
            return $this->successResponse('Spider job dispatched successfully', [
                'spider_type' => $spiderType->value,
                'spider_name' => $spiderType->getDisplayName(),
                'background' => true,
                'status' => 'queued'
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch spider job: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return $this->errorResponse('Failed to dispatch spider job: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Build overrides array from request
     *
     * @param RunSpiderRequest $request
     * @return array
     */
    private function buildOverrides(RunSpiderRequest $request): array
    {
        $overrides = [];
        
        if ($startUrl = $request->validated('start_url')) {
            $overrides['startUrls'] = [$startUrl];
        }
        
        return $overrides;
    }
}
