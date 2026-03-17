<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * A rating given by one participant after a conversation closes.
 *
 * Stores the rater ID, the rated designer ID, a star score, and an optional
 * comment. Cache for the rated designer's rating stats is invalidated on save.
 */
class ConversationRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'rater_id',
        'rated_id',
        'rating',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($rating) {
            Cache::forget("conv_rating:avg:{$rating->rated_id}");
            Cache::forget("conv_rating:count:{$rating->rated_id}");
        });

        static::updated(function ($rating) {
            Cache::forget("conv_rating:avg:{$rating->rated_id}");
            Cache::forget("conv_rating:count:{$rating->rated_id}");
        });

        static::deleted(function ($rating) {
            Cache::forget("conv_rating:avg:{$rating->rated_id}");
            Cache::forget("conv_rating:count:{$rating->rated_id}");
        });
    }

    protected $casts = [
        'rating' => 'integer',
    ];

    // ==========================================
    // Relationships
    // ==========================================

    /**
     * Get the conversation this rating belongs to
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the designer who gave the rating
     */
    public function rater()
    {
        return $this->belongsTo(Designer::class, 'rater_id');
    }

    /**
     * Get the designer who was rated
     */
    public function rated()
    {
        return $this->belongsTo(Designer::class, 'rated_id');
    }

    // ==========================================
    // Scopes
    // ==========================================

    /**
     * Scope to get ratings for a specific conversation
     */
    public function scopeForConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    /**
     * Scope to get ratings by a specific rater
     */
    public function scopeByRater($query, $raterId)
    {
        return $query->where('rater_id', $raterId);
    }

    /**
     * Scope to get ratings for a specific rated user
     */
    public function scopeForRated($query, $ratedId)
    {
        return $query->where('rated_id', $ratedId);
    }

    // ==========================================
    // Static Methods
    // ==========================================

    /**
     * Check if a user has already rated in a conversation
     */
    public static function hasRated($conversationId, $raterId)
    {
        return self::where('conversation_id', $conversationId)
            ->where('rater_id', $raterId)
            ->exists();
    }

    /**
     * Get the rating a user gave in a conversation
     */
    public static function getRating($conversationId, $raterId)
    {
        return self::where('conversation_id', $conversationId)
            ->where('rater_id', $raterId)
            ->first();
    }

    /**
     * Get average conversation rating for a user (cached for 10 minutes)
     */
    public static function getAverageRating($designerId)
    {
        return Cache::remember("conv_rating:avg:{$designerId}", 600, function () use ($designerId) {
            return self::where('rated_id', $designerId)->avg('rating') ?? 0;
        });
    }

    /**
     * Get total conversation ratings count for a user (cached for 10 minutes)
     */
    public static function getRatingCount($designerId)
    {
        return Cache::remember("conv_rating:count:{$designerId}", 600, function () use ($designerId) {
            return self::where('rated_id', $designerId)->count();
        });
    }
}
