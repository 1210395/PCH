<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Records a subscription from one user to another user's profile.
 *
 * Polymorphic on both sides: subscriber can be a Designer or AcademicAccount;
 * subscribable can also be a Designer or AcademicAccount. Subscribers receive
 * in-app notifications when the subscribed profile publishes new approved content.
 */
class ProfileSubscription extends Model
{
    protected $fillable = [
        'subscriber_type',
        'subscriber_id',
        'subscribable_type',
        'subscribable_id',
    ];

    /**
     * Get the subscriber (designer or academic who is subscribing)
     */
    public function subscriber()
    {
        return $this->morphTo();
    }

    /**
     * Get the profile being subscribed to
     */
    public function subscribable()
    {
        return $this->morphTo();
    }

    /**
     * Scope: Get subscriptions for a specific subscriber
     */
    public function scopeForSubscriber($query, string $type, int $id)
    {
        return $query->where('subscriber_type', $type)
                     ->where('subscriber_id', $id);
    }

    /**
     * Scope: Get subscribers for a specific profile
     */
    public function scopeForProfile($query, string $type, int $id)
    {
        return $query->where('subscribable_type', $type)
                     ->where('subscribable_id', $id);
    }

    /**
     * Check if subscriber is subscribed to a profile
     */
    public static function isSubscribed(string $subscriberType, int $subscriberId, string $subscribableType, int $subscribableId): bool
    {
        return static::where('subscriber_type', $subscriberType)
            ->where('subscriber_id', $subscriberId)
            ->where('subscribable_type', $subscribableType)
            ->where('subscribable_id', $subscribableId)
            ->exists();
    }

    /**
     * Toggle subscription - returns true if now subscribed, false if unsubscribed
     */
    public static function toggleSubscription(string $subscriberType, int $subscriberId, string $subscribableType, int $subscribableId): bool
    {
        $existing = static::where('subscriber_type', $subscriberType)
            ->where('subscriber_id', $subscriberId)
            ->where('subscribable_type', $subscribableType)
            ->where('subscribable_id', $subscribableId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false; // Now unsubscribed
        }

        static::create([
            'subscriber_type' => $subscriberType,
            'subscriber_id' => $subscriberId,
            'subscribable_type' => $subscribableType,
            'subscribable_id' => $subscribableId,
        ]);

        return true; // Now subscribed
    }

    /**
     * Get all subscribers for a profile
     */
    public static function getSubscribers(string $profileType, int $profileId)
    {
        return static::where('subscribable_type', $profileType)
            ->where('subscribable_id', $profileId)
            ->get();
    }

    /**
     * Get subscriber count for a profile
     */
    public static function getSubscriberCount(string $profileType, int $profileId): int
    {
        return static::where('subscribable_type', $profileType)
            ->where('subscribable_id', $profileId)
            ->count();
    }
}
