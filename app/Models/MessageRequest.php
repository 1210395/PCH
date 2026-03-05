<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageRequest extends Model
{
    use HasFactory;

    protected $table = 'message_requests';

    protected $fillable = [
        'from_designer_id',
        'to_designer_id',
        'message',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the designer sending the request
     */
    public function fromDesigner()
    {
        return $this->belongsTo(Designer::class, 'from_designer_id');
    }

    /**
     * Get the designer receiving the request
     */
    public function toDesigner()
    {
        return $this->belongsTo(Designer::class, 'to_designer_id');
    }

    /**
     * Accept the message request and create a conversation
     */
    public function accept()
    {
        $this->status = 'accepted';
        $this->save();

        // Create or get conversation
        $conversation = Conversation::findOrCreateBetween($this->from_designer_id, $this->to_designer_id);

        // Create first message in conversation
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->from_designer_id,
            'receiver_id' => $this->to_designer_id,
            'message' => $this->message,
        ]);

        // Update conversation with first message and set accepted_at for rating eligibility
        $conversation->last_message_id = $message->id;
        $conversation->last_message_at = $message->created_at;
        $conversation->accepted_at = now(); // Set when conversation is accepted for 24h rating countdown
        $conversation->save();

        return $conversation;
    }

    /**
     * Decline the message request
     */
    public function decline()
    {
        $this->status = 'declined';
        $this->save();
    }

    /**
     * Check if there's a pending request between two designers
     */
    public static function hasPendingRequest($fromDesignerId, $toDesignerId)
    {
        return static::where('from_designer_id', $fromDesignerId)
            ->where('to_designer_id', $toDesignerId)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Scope to get pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get requests for a designer
     */
    public function scopeForDesigner($query, $designerId)
    {
        return $query->where('to_designer_id', $designerId);
    }
}
