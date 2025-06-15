<?php

namespace App\Spiders;

use Generator;
use RoachPHP\Http\Response;

class ProductPageSpider extends BaseSpider
{
    /**
     * @var string[]
     */
    public array $startUrls = [];
    
    /**
     * Constructor to initialize dynamic properties
     */
    public function __construct()
    {
        parent::__construct();
        $this->startUrls = [config('app.url') . '/product'];
    }

    /**
     * Parse the response and extract product data
     */
    public function parse(Response $response): Generator
    {
        $title = $response->filter('#product-name')->text();
        $imageUrl = $response->filter('#product-image img.product-image')->attr('src');
        $priceText = $response->filter('#product-price')->text();
        $price = $this->cleanPrice($priceText);
        
        yield $this->item([
            'title' => $title,
            'price' => $price,
            'image_url' => $imageUrl,
        ]);
    }
}
