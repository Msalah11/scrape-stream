<?php

namespace App\Spiders\Middleware;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RoachPHP\Downloader\Middleware\RequestMiddlewareInterface;
use RoachPHP\Http\Request;
use RoachPHP\Support\Configurable;

class ProxyMiddleware implements RequestMiddlewareInterface
{
    use Configurable;

    /**
     * @var array<string, mixed>
     */
    protected array $options = [
        'useProxy' => true,
        'proxyServiceUrl' => null,
    ];

    /**
     * Handle the request before it is sent
     */
    public function handleRequest(Request $request): Request
    {
        // Skip if proxy usage is disabled
        if (!$this->option('useProxy')) {
            Log::info('Proxy usage disabled for request: ' . $request->getUri());
            return $request;
        }

        try {
            $proxy = $this->getProxy();
            
            if ($proxy) {
                $proxyUrl = $proxy['url'];
                // Add proxy to request
                $request = $request->addHeader('X-Proxy', $proxyUrl);
                
                Log::info('Using proxy for request: ' . $proxyUrl);
            } else {
                Log::warning('No valid proxy found, proceeding without proxy');
            }
        } catch (\Exception $e) {
            Log::error('Error getting proxy: ' . $e->getMessage());
        }

        return $request;
    }

    /**
     * Get a proxy from the proxy manager service
     */
    protected function getProxy(): ?array
    {
        $proxyServiceUrl = $this->option('proxyServiceUrl') ?? config('services.proxy_manager.url');
        
        if (!$proxyServiceUrl) {
            Log::warning('No proxy service URL configured');
            return null;
        }

        try {
            $response = Http::get($proxyServiceUrl . '/proxy');
            
            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['data'])) {
                    return [
                        'url' => $data['data'],
                    ];
                }
            }
            
            Log::warning('Failed to get proxy: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Exception while getting proxy: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Get an option value by key with optional default value
     */
    protected function option(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }
}
