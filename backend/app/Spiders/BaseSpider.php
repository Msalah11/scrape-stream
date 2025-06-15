<?php

namespace App\Spiders;

use App\Traits\SpiderHelpers;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Spider\BasicSpider;
use App\Spiders\Middleware\ProxyMiddleware;

/**
 * Base Spider class with common configuration and methods
 * 
 * All spiders should extend this class instead of BasicSpider directly
 */
abstract class BaseSpider extends BasicSpider
{
    use SpiderHelpers;
    
    /**
     * Constructor to ensure proper initialization
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Request delay in seconds
     */
    public int $requestDelay = 2;
    
    /**
     * Default request middleware
     */
    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
        UserAgentMiddleware::class,
        // ProxyMiddleware::class,
    ];
    
    /**
     * Default extensions
     */
    public array $extensions = [
        LoggerExtension::class,
    ];
    
    /**
     * Configure the spider with delay and middleware options
     */
    public function configure(): array
    {
        return [
            // Add a delay between requests to avoid rate limiting
            // 'download_delay' => $this->requestDelay,
            
            // Configure user agent middleware
            UserAgentMiddleware::class => [
                'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36',
            ],
            
            // Configure proxy middleware
            // ProxyMiddleware::class => [
            //     'useProxy' => true,
            // ],
        ];
    }
}
