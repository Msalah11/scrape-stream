<?php

use App\Models\Product;
use App\Spiders\Processors\ProductProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\Support\Configurable;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->processor = new ProductProcessor();
    
    // Create a mock item
    $this->item = Mockery::mock(ItemInterface::class);
});

test('processor creates new product when it does not exist', function () {
    // Arrange
    $this->item->shouldReceive('get')
        ->with('title')
        ->andReturn('Test Product');
        
    $this->item->shouldReceive('get')
        ->with('price')
        ->andReturn('$99.99');
        
    $this->item->shouldReceive('get')
        ->with('image_url')
        ->andReturn('https://example.com/image.jpg');
    
    // Act
    $result = $this->processor->processItem($this->item);
    
    // Assert
    expect($result)->toBe($this->item);
    
    // Verify product was created in database
    $this->assertDatabaseHas('products', [
        'title' => 'Test Product',
        'price' => 99.99,
        'image_url' => 'https://example.com/image.jpg',
    ]);
});

test('processor updates existing product', function () {
    // Arrange - Create existing product
    $product = Product::create([
        'title' => 'Test Product',
        'price' => 79.99,
        'image_url' => 'https://example.com/old-image.jpg',
    ]);
    
    $this->item->shouldReceive('get')
        ->with('title')
        ->andReturn('Test Product');
        
    $this->item->shouldReceive('get')
        ->with('price')
        ->andReturn('$89.99');
        
    $this->item->shouldReceive('get')
        ->with('image_url')
        ->andReturn('https://example.com/new-image.jpg');
    
    // Act
    $result = $this->processor->processItem($this->item);
    
    // Assert
    expect($result)->toBe($this->item);
    
    // Verify product was updated in database
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'title' => 'Test Product',
        'price' => 89.99,
        'image_url' => 'https://example.com/new-image.jpg',
    ]);
});

test('processor skips items with missing required fields', function () {
    // Arrange - Item with missing title
    $this->item->shouldReceive('get')
        ->with('title')
        ->andReturn('');
        
    $this->item->shouldReceive('get')
        ->with('price')
        ->andReturn('$99.99');
        
    $this->item->shouldReceive('get')
        ->with('image_url')
        ->andReturn('https://example.com/image.jpg');
    
    // Act
    $result = $this->processor->processItem($this->item);
    
    // Assert
    expect($result)->toBe($this->item);
    
    // Verify no product was created
    $this->assertDatabaseCount('products', 0);
});

test('processor cleans price correctly', function () {
    // Arrange
    $this->item->shouldReceive('get')
        ->with('title')
        ->andReturn('Test Product');
        
    $this->item->shouldReceive('get')
        ->with('price')
        ->andReturn('$1,234.56');
        
    $this->item->shouldReceive('get')
        ->with('image_url')
        ->andReturn('https://example.com/image.jpg');
    
    // Act
    $result = $this->processor->processItem($this->item);
    
    // Assert
    expect($result)->toBe($this->item);
    
    // Verify price was cleaned correctly
    $this->assertDatabaseHas('products', [
        'title' => 'Test Product',
        'price' => 1234.56,
    ]);
});
