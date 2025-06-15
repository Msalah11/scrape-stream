<?php

namespace App\Spiders\Processors;

use Throwable;
use App\Models\Product;
use App\Traits\SpiderHelpers;
use RoachPHP\Support\Configurable;
use Illuminate\Support\Facades\Log;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;

class ProductProcessor implements ItemProcessorInterface
{
    use Configurable, SpiderHelpers;

    /**
     * Process a scraped item.
     */
    public function processItem(ItemInterface $item): ItemInterface
    {
        try {
            $title = trim($item->get('title') ?? '');
            $priceRaw = $item->get('price');
            $imageUrl = $item->get('image_url');
            
            if (empty($title) || empty($priceRaw)) {
                Log::warning('Skipping product with missing required fields', [
                    'title' => $title,
                    'price' => $priceRaw,
                ]);
                return $item;
            }
            
            $price = $this->cleanPrice($priceRaw);
            
            $attributes = ['title' => $title];
            $values = [
                'price' => $price,
                'image_url' => $imageUrl ?: null,
            ];
            
            $product = Product::updateOrCreate($attributes, $values);
            
            $isNewRecord = $product->wasRecentlyCreated;
            Log::info($isNewRecord ? 'Created new product' : 'Updated existing product', [
                'id' => $product->id,
                'title' => $product->title,
            ]);
            
            // Add the product ID to the item
            return $item->set('product_id', $product->id);
            
        } catch (Throwable $e) {
            Log::error('Error processing product', [
                'message' => $e->getMessage(),
                'data' => $item->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $item;
        }
    }
}
