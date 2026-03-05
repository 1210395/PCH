<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function ($model) {
            if ($model->designer_id) {
                \Illuminate\Support\Facades\Cache::forget("designer_{$model->designer_id}_unread_notifications");
            }
        });

        static::deleted(function ($model) {
            if ($model->designer_id) {
                \Illuminate\Support\Facades\Cache::forget("designer_{$model->designer_id}_unread_notifications");
            }
        });
    }

    protected $fillable = [
        'designer_id',
        'type',
        'title',
        'message',
        'read',
        'data'
    ];

    protected $casts = [
        'read' => 'boolean',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the designer that owns the notification
     */
    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read' => true]);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope for specific designer
     */
    public function scopeForDesigner($query, $designerId)
    {
        return $query->where('designer_id', $designerId);
    }
}
