<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A user's subscription to a content type/category combination.
 *
 * Stores filter preferences as JSON arrays (categories, tags, types, levels)
 * so notifications can be sent for only the content slices the subscriber
 * is interested in. The `getMatchingSubscriptions()` scope finds all
 * subscriptions that match a given piece of content.
 */
class CategorySubscription extends Model
{
    protected $fillable = [
        'subscriber_type',
        'subscriber_id',
        'content_type',
        'categories',
        'tags',
        'types',
        'levels',
        'is_active',
    ];

    protected $casts = [
        'categories' => 'array',
        'tags' => 'array',
        'types' => 'array',
        'levels' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the subscriber
     */
    public function subscriber()
    {
        return $this->morphTo();
    }

    /**
     * Scope: Get subscription for a specific subscriber and optionally content type
     */
    public function scopeForSubscriber($query, string $type, int $id, ?string $contentType = null)
    {
        $query->where('subscriber_type', $type)->where('subscriber_id', $id);

        if ($contentType) {
            $query->where('content_type', $contentType);
        }

        return $query;
    }

    /**
     * Scope: Active subscriptions only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get subscriptions for a specific content type
     */
    public function scopeForContentType($query, string $contentType)
    {
        return $query->where('content_type', $contentType);
    }

    /**
     * Check if subscription matches a new content item
     *
     * @param string|null $category The content's category
     * @param array $tags The content's tags (for marketplace)
     * @param string|null $type The content's type (for marketplace)
     * @param string|null $level The content's level (for training)
     * @return bool
     */
    public function matchesContent(?string $category, array $tags = [], ?string $type = null, ?string $level = null): bool
    {
        // For marketplace: use tags instead of categories
        if ($this->content_type === 'marketplace') {
            // Check tags if specified (null/empty means "all")
            if ($this->tags !== null && !empty($this->tags)) {
                if (empty($tags) || empty(array_intersect($tags, $this->tags))) {
                    return false;
                }
            }

            // Check type if specified
            if ($this->types !== null && !empty($this->types)) {
                if ($type === null || !in_array($type, $this->types)) {
                    return false;
                }
            }

            return true;
        }

        // For other content types: check categories if specified (null/empty means "all")
        if ($this->categories !== null && !empty($this->categories)) {
            if ($category === null || !in_array($category, $this->categories)) {
                return false;
            }
        }

        // For training: check level if specified
        if ($this->content_type === 'training' && $this->levels !== null && !empty($this->levels)) {
            if ($level === null || !in_array($level, $this->levels)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get or create a subscription for a subscriber and content type
     */
    public static function getOrCreate(string $subscriberType, int $subscriberId, string $contentType): self
    {
        return static::firstOrCreate(
            [
                'subscriber_type' => $subscriberType,
                'subscriber_id' => $subscriberId,
                'content_type' => $contentType,
            ],
            [
                'is_active' => true,
            ]
        );
    }

    /**
     * Get all active subscriptions that might match content
     */
    public static function getMatchingSubscriptions(string $contentType, ?string $category, array $tags = [], ?string $type = null, ?string $level = null, ?int $excludeDesignerId = null)
    {
        $query = static::where('content_type', $contentType)
            ->where('is_active', true);

        // Exclude the content creator
        if ($excludeDesignerId !== null) {
            $query->where(function ($q) use ($excludeDesignerId) {
                $q->where('subscriber_type', '!=', 'designer')
                  ->orWhere('subscriber_id', '!=', $excludeDesignerId);
            });
        }

        return $query->get()->filter(function ($subscription) use ($category, $tags, $type, $level) {
            return $subscription->matchesContent($category, $tags, $type, $level);
        });
    }
}
