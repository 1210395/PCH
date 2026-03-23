<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A direct messaging thread between two designers.
 *
 * Created when a MessageRequest is accepted. Holds references to
 * both participants and an optional closed_at timestamp. Related
 * messages are accessed via the Message model.
 */
class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';

    protected $fillable = [
        'designer_1_id',
        'designer_2_id',
        'last_message_id',
        'last_message_at',
        'designer_1_unread_count',
        'designer_2_unread_count',
        'accepted_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'accepted_at' => 'datetime',
        'designer_1_unread_count' => 'integer',
        'designer_2_unread_count' => 'integer',
    ];

    /**
     * Get the first designer in the conversation
     */
    public function designer1()
    {
        return $this->belongsTo(Designer::class, 'designer_1_id');
    }

    /**
     * Get the second designer in the conversation
     */
    public function designer2()
    {
        return $this->belongsTo(Designer::class, 'designer_2_id');
    }

    /**
     * Get all messages in this conversation
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    /**
     * Get the last message in the conversation
     */
    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    /**
     * Get the other designer in the conversation (not the current user)
     */
    public function getOtherDesigner($currentDesignerId)
    {
        if ($this->designer_1_id == $currentDesignerId) {
            return $this->designer2;
        }
        return $this->designer1;
    }

    /**
     * Get unread count for a specific designer
     */
    public function getUnreadCount($designerId)
    {
        if ($this->designer_1_id == $designerId) {
            return $this->designer_1_unread_count;
        }
        return $this->designer_2_unread_count;
    }

    /**
     * Mark messages as read for a designer
     */
    public function markAsRead($designerId)
    {
        if ($this->designer_1_id == $designerId) {
            $this->designer_1_unread_count = 0;
        } else {
            $this->designer_2_unread_count = 0;
        }
        $this->save();
    }

    /**
     * Find or create a conversation between two designers
     */
    public static function findOrCreateBetween($designer1Id, $designer2Id)
    {
        // Ensure consistent ordering (smaller ID first)
        $ids = [$designer1Id, $designer2Id];
        sort($ids);

        return static::firstOrCreate([
            'designer_1_id' => $ids[0],
            'designer_2_id' => $ids[1],
        ]);
    }

    /**
     * Get all ratings for this conversation
     */
    public function ratings()
    {
        return $this->hasMany(ConversationRating::class);
    }

    /**
     * Check if rating is allowed (immediately after conversation is accepted)
     */
    public function canRate()
    {
        return $this->accepted_at !== null;
    }

    /**
     * Get hours remaining until rating is allowed (always 0 since rating is immediate)
     */
    public function hoursUntilRatingAllowed()
    {
        if (!$this->accepted_at) {
            return null;
        }

        return 0;
    }

    /**
     * Check if the 24-hour reminder should be sent (accepted 24+ hours ago and user hasn't rated)
     */
    public function shouldSendRatingReminder($designerId)
    {
        if (!$this->accepted_at) {
            return false;
        }

        // Only send reminder if 24 hours have passed since acceptance
        if (!$this->accepted_at->addHours(24)->isPast()) {
            return false;
        }

        // Only send if the user hasn't rated yet
        return !$this->hasUserRated($designerId);
    }

    /**
     * Check if a specific user has already rated
     */
    public function hasUserRated($designerId)
    {
        return ConversationRating::hasRated($this->id, $designerId);
    }

    /**
     * Get the rating a user gave
     */
    public function getUserRating($designerId)
    {
        return ConversationRating::getRating($this->id, $designerId);
    }
}
