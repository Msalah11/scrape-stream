<?php

namespace App\Spiders;

use Generator;
use RoachPHP\Http\Response;
use RoachPHP\Spider\ParseResult;

class AmazonSpider extends BaseSpider
{

    /**
     * @var string[]
     */
    public array $startUrls = [
        'https://www.amazon.com/s?k=laptops',
    ];

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
}
