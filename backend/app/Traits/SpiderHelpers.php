<?php

namespace App\Traits;

trait SpiderHelpers
{
    /**
     * Clean price text and convert to float
     * 
     * Removes all non-numeric characters except decimal point
     * 
     * @param string $price The raw price string from the page
     * @return float The cleaned price as a float
     */
    protected function cleanPrice(string $price): float
    {
        return (float) preg_replace('/[^0-9.]/', '', $price);
    }
    
    /**
     * Clean and normalize text content
     * 
     * @param string $text The raw text from the page
     * @return string The cleaned text
     */
    protected function cleanText(string $text): string
    {
        // Trim whitespace, normalize spaces, and remove special characters if needed
        return trim(preg_replace('/\s+/', ' ', $text));
    }
    
    /**
     * Validate URL format
     * 
     * @param string $url The URL to validate
     * @return string The validated URL or empty string if invalid
     */
    protected function validateUrl(string $url): string
    {
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
    }
    
    /**
     * Extract numeric value from text
     * 
     * @param string $text Text containing numbers
     * @param int $default Default value if no number found
     * @return int The extracted number
     */
    protected function extractNumber(string $text, int $default = 0): int
    {
        preg_match('/\d+/', $text, $matches);
        return isset($matches[0]) ? (int) $matches[0] : $default;
    }
}
