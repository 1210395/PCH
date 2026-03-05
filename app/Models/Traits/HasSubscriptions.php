<?php

namespace App\Models\Traits;

use App\Models\ProfileSubscription;
use App\Models\CategorySubscription;

trait HasSubscriptions
{
    /**
     * Get the subscriber type string for this model
     */
    public function getSubscriberType(): string
    {
        return $this instanceof \App\Models\Designer ? 'designer' : 'academic';
    }

    /**
     * Profile subscriptions this user has made (who they're subscribed to)
     */
    public function profileSubscriptions()
    {
        return $this->morphMany(ProfileSubscription::class, 'subscriber');
    }

    /**
     * People subscribed to this profile
     */
    public function profileSubscribers()
    {
        return $this->morphMany(ProfileSubscription::class, 'subscribable');
    }

    /**
     * Category subscriptions
     */
    public function categorySubscriptions()
    {
        return $this->morphMany(CategorySubscription::class, 'subscriber');
    }

    /**
     * Check if subscribed to a profile
     */
    public function isSubscribedTo(string $subscribableType, int $subscribableId): bool
    {
        return ProfileSubscription::isSubscribed(
            $this->getSubscriberType(),
            $this->id,
            $subscribableType,
            $subscribableId
        );
    }

    /**
     * Toggle profile subscription
     * Returns true if now subscribed, false if unsubscribed
     */
    public function toggleProfileSubscription(string $subscribableType, int $subscribableId): bool
    {
        return ProfileSubscription::toggleSubscription(
            $this->getSubscriberType(),
            $this->id,
            $subscribableType,
            $subscribableId
        );
    }

    /**
     * Subscribe to a profile
     */
    public function subscribeTo(string $subscribableType, int $subscribableId): void
    {
        if (!$this->isSubscribedTo($subscribableType, $subscribableId)) {
            ProfileSubscription::create([
                'subscriber_type' => $this->getSubscriberType(),
                'subscriber_id' => $this->id,
                'subscribable_type' => $subscribableType,
                'subscribable_id' => $subscribableId,
            ]);
        }
    }

    /**
     * Unsubscribe from a profile
     */
    public function unsubscribeFrom(string $subscribableType, int $subscribableId): void
    {
        ProfileSubscription::where('subscriber_type', $this->getSubscriberType())
            ->where('subscriber_id', $this->id)
            ->where('subscribable_type', $subscribableType)
            ->where('subscribable_id', $subscribableId)
            ->delete();
    }

    /**
     * Get the number of profile subscribers
     */
    public function getProfileSubscriberCount(): int
    {
        return ProfileSubscription::getSubscriberCount($this->getSubscriberType(), $this->id);
    }

    /**
     * Get or create category subscription for a content type
     */
    public function getCategorySubscription(string $contentType): ?CategorySubscription
    {
        return CategorySubscription::where('subscriber_type', $this->getSubscriberType())
            ->where('subscriber_id', $this->id)
            ->where('content_type', $contentType)
            ->first();
    }

    /**
     * Save category subscription preferences
     */
    public function saveCategorySubscription(
        string $contentType,
        ?array $categories = null,
        ?array $tags = null,
        ?array $types = null,
        ?array $levels = null,
        bool $isActive = true
    ): CategorySubscription {
        return CategorySubscription::updateOrCreate(
            [
                'subscriber_type' => $this->getSubscriberType(),
                'subscriber_id' => $this->id,
                'content_type' => $contentType,
            ],
            [
                'categories' => $categories,
                'tags' => $tags,
                'types' => $types,
                'levels' => $levels,
                'is_active' => $isActive,
            ]
        );
    }

    /**
     * Remove category subscription
     */
    public function removeCategorySubscription(string $contentType): void
    {
        CategorySubscription::where('subscriber_type', $this->getSubscriberType())
            ->where('subscriber_id', $this->id)
            ->where('content_type', $contentType)
            ->delete();
    }

    /**
     * Check if has an active category subscription for a content type
     */
    public function hasCategorySubscription(string $contentType): bool
    {
        return CategorySubscription::where('subscriber_type', $this->getSubscriberType())
            ->where('subscriber_id', $this->id)
            ->where('content_type', $contentType)
            ->where('is_active', true)
            ->exists();
    }
}
