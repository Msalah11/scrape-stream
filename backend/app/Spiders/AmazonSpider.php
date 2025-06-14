<?php

namespace App\Spiders;

use App\Spiders\Middleware\ProxyMiddleware;
use Generator;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;

class AmazonSpider extends BasicSpider
{
    /**
     * @var string[]
     */
    public array $startUrls = [
        'https://www.amazon.com/s?k=laptops',
    ];

    /**
     * Default request middleware
     */
    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
        UserAgentMiddleware::class,
        ProxyMiddleware::class,
    ];

    /**
     * Default extensions
     */
    public array $extensions = [
        LoggerExtension::class,
    ];

    /**
     * Default request delay in seconds
     */
    public int $requestDelay = 2;
    
    /**
     * Configure the spider with delay and middleware options
     */
    public function configure(): array
    {
        return [
            // Add a delay between requests to avoid rate limiting
            'download_delay' => $this->requestDelay,
            
            // Configure user agent middleware
            UserAgentMiddleware::class => [
                'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36',
            ],
            
            // Configure proxy middleware
            ProxyMiddleware::class => [
                'useProxy' => true,
            ],
        ];
    }

    /**
     * Parse the response and extract product information
     */
    public function parse(Response $response): Generator
    {
        // Extract all product items from the page
        $response->filter('.s-result-item')?->each(function ($node) {
            $title = $node->filter('a.a-link-normal h2 span')?->text() ?? '';            
            $productUrl = $node->filter('h2 a')?->attr('href') ?? '';
            
            if (!empty($productUrl) && !empty($title)) {
                if (!str_starts_with($productUrl, 'http')) {
                    $productUrl = 'https://www.amazon.com' . $productUrl;
                }
                
                // Request the detailed product page
                yield $this->request('GET', $productUrl, 'parseProduct');
            }
        });
        
        $paginationExists = $response->filter('.s-pagination-next')?->count() > 0;
        $isDisabled = $response->filter('.s-pagination-next.s-pagination-disabled')?->count() > 0;
        
        if ($paginationExists && !$isDisabled) {
            $nextPageUrl = $response->filter('.s-pagination-next')?->attr('href') ?? '';
            
            if (!empty($nextPageUrl)) {
                
                if (!str_starts_with($nextPageUrl, 'http')) {
                    $nextPageUrl = 'https://www.amazon.com' . $nextPageUrl;
                }
                
                yield $this->request('GET', $nextPageUrl);
            }
        }
    }
    
    /**
     * Parse a product detail page
     */
    public function parseProduct(Response $response): Generator
    {
        $title = $response->filter('#productTitle')?->text() ?? '';
        $price = match(true) {
            $response->filter('#priceblock_ourprice')?->count() > 0 => $response->filter('#priceblock_ourprice')?->text() ?? '',
            $response->filter('.a-offscreen')?->count() > 0 => $response->filter('.a-offscreen')?->first()?->text() ?? '',
            default => ''
        };
        
        $imageUrl = $response->filter('#landingImage')?->attr('src') ?? '';
        
        if (!empty($title)) {
            yield $this->item([
                'title' => trim($title),
                'price' => $this->cleanPrice($price),
                'image_url' => $imageUrl,
            ]);
        }
    }
    
    /**
     * Clean price text and convert to float
     */
    private function cleanPrice(string $price): float
    {
        return (float) preg_replace('/[^0-9.]/', '', $price);
    }
}
