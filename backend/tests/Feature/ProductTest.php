<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test products
    Product::factory()->count(15)->create();
});

test('can fetch paginated products', function () {
    // Act
    $response = $this->getJson('/api/products');
    
    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'products' => [
                '*' => [
                    'id',
                    'title',
                    'price',
                    'image_url',
                    'created_at',
                    'updated_at',
                ]
            ],
            'meta' => [
                'total',
                'count',
                'per_page',
                'current_page',
                'total_pages',
            ]
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Products retrieved successfully',
        ]);
    
    // Verify pagination is working
    expect($response->json('meta.per_page'))->toBe(15);
    expect($response->json('products'))->toHaveCount(15);
});

test('can filter products by search term', function () {
    // Create a product with a specific title
    $product = Product::factory()->create(['title' => 'Special Test Product']);
    
    // Act
    $response = $this->getJson('/api/products?search=Special');
    
    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Products retrieved successfully',
        ]);
    
    // Verify only the matching product is returned
    expect($response->json('products.0.title'))->toBe('Special Test Product');
    expect($response->json('meta.count'))->toBe(1);
});

test('can filter products by price range', function () {
    // Create products with specific prices
    Product::factory()->create(['price' => 50.00]);
    Product::factory()->create(['price' => 100.00]);
    Product::factory()->create(['price' => 150.00]);
    
    // Act - Get products between 75 and 125
    $response = $this->getJson('/api/products?min_price=75&max_price=125');
    
    // Assert
    $response->assertStatus(200);
    
    // Verify only products in the price range are returned
    $products = collect($response->json('products'));
    $prices = $products->pluck('price');
    
    expect($prices->min())->toBeGreaterThanOrEqual(75);
    expect($prices->max())->toBeLessThanOrEqual(125);
});

test('can sort products', function () {
    // Create products with specific titles for sorting
    Product::factory()->create(['title' => 'A Product']);
    Product::factory()->create(['title' => 'B Product']);
    Product::factory()->create(['title' => 'C Product']);
    
    // Act - Sort by title ascending
    $response = $this->getJson('/api/products?sort_by=title&sort_dir=asc');
    
    // Assert
    $response->assertStatus(200);
    
    // Verify products are sorted correctly
    $titles = collect($response->json('products'))->pluck('title');
    expect($titles->first())->toBe('A Product');
});

test('can change pagination size', function () {
    // Act - Request 5 products per page
    $response = $this->getJson('/api/products?per_page=5');
    
    // Assert
    $response->assertStatus(200);
    
    // Verify pagination size is respected
    expect($response->json('meta.per_page'))->toBe(5);
    expect($response->json('products'))->toHaveCount(5);
});
