<?php

namespace App\Models\Traits;

use App\Models\ProfileSubscription;
use App\Models\CategorySubscription;

/**
 * Provides profile and category subscription capabilities to Designer and Academic models.
 *
 * Profile subscriptions track who a user follows (and who follows them) via a polymorphic
 * morph-many relationship. Category subscriptions store per-content-type filter preferences
 * (categories, tags, types, levels) used to personalise notification feeds.
 */
trait HasSubscriptions
{
    /**
     * Get the subscriber type string for this model.
     *
     * Returns 'designer' for Designer instances and 'academic' for all others.
     *
     * @return string
     */
    public function getSubscriberType(): string
    {
        return $this instanceof \App\Models\Designer ? 'designer' : 'academic';
    }

    /**
     * Profile subscriptions this user has made (who they're subscribed to).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function profileSubscriptions()
    {
        return $this->morphMany(ProfileSubscription::class, 'subscriber');
    }

    /**
     * People subscribed to this profile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function profileSubscribers()
    {
        return $this->morphMany(ProfileSubscription::class, 'subscribable');
    }

    /**
     * Category subscriptions belonging to this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function categorySubscriptions()
    {
        return $this->morphMany(CategorySubscription::class, 'subscriber');
    }

    /**
     * Check if this user is subscribed to the given profile.
     *
     * @param  string  $subscribableType  Morph type string (e.g. 'designer')
     * @param  int     $subscribableId
     * @return bool
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
     * Toggle profile subscription on or off.
     *
     * @param  string  $subscribableType  Morph type string (e.g. 'designer')
     * @param  int     $subscribableId
     * @return bool  True if the user is now subscribed, false if unsubscribed
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
     * Subscribe this user to a profile (no-op if already subscribed).
     *
     * @param  string  $subscribableType  Morph type string
     * @param  int     $subscribableId
     * @return void
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
     * Remove this user's subscription from a profile.
     *
     * @param  string  $subscribableType  Morph type string
     * @param  int     $subscribableId
     * @return void
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
     * Get the total number of users subscribed to this profile.
     *
     * @return int
     */
    public function getProfileSubscriberCount(): int
    {
        return ProfileSubscription::getSubscriberCount($this->getSubscriberType(), $this->id);
    }

    /**
     * Get the active category subscription record for a given content type, if any.
     *
     * @param  string  $contentType  e.g. 'marketplace', 'training', 'tender'
     * @return \App\Models\CategorySubscription|null
     */
    public function getCategorySubscription(string $contentType): ?CategorySubscription
    {
        return CategorySubscription::where('subscriber_type', $this->getSubscriberType())
            ->where('subscriber_id', $this->id)
            ->where('content_type', $contentType)
            ->first();
    }

    /**
     * Create or update a category subscription with the given filter preferences.
     *
     * Uses updateOrCreate keyed on subscriber + content_type so calling this method
     * repeatedly is idempotent for the same content type.
     *
     * @param  string       $contentType
     * @param  array|null   $categories
     * @param  array|null   $tags
     * @param  array|null   $types
     * @param  array|null   $levels
     * @param  bool         $isActive
     * @return \App\Models\CategorySubscription
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
     * Delete the category subscription for the given content type.
     *
     * @param  string  $contentType
     * @return void
     */
    public function removeCategorySubscription(string $contentType): void
    {
        CategorySubscription::where('subscriber_type', $this->getSubscriberType())
            ->where('subscriber_id', $this->id)
            ->where('content_type', $contentType)
            ->delete();
    }

    /**
     * Check whether this user has an active category subscription for the given content type.
     *
     * @param  string  $contentType
     * @return bool
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
