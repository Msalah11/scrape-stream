<?php

namespace App\Enums;

use App\Spiders\AmazonSpider;
use InvalidArgumentException;

enum SpiderType: string
{
    case AMAZON = 'amazon';

    /**
     * Get the spider class for this type
     *
     * @return string
     */
    public function getSpiderClass(): string
    {
        return match($this) {
            self::AMAZON => AmazonSpider::class,
            default => throw new InvalidArgumentException("Spider class not implemented for {$this->value}"),
        };
    }

    /**
     * Get the display name for this type
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return match($this) {
            self::AMAZON => 'Amazon Product Spider',
        };
    }

    /**
     * Get all available spiders as an array
     *
     * @return array<string, string>
     */
    public static function getAvailableSpiders(): array
    {
        $spiders = [];
        foreach (self::cases() as $case) {
            try {
                // Only include spiders that have an implemented class
                $case->getSpiderClass();
                $spiders[$case->value] = $case->getDisplayName();
            } catch (InvalidArgumentException) {
                // Skip spiders without implemented classes
                continue;
            }
        }
        return $spiders;
    }
}
