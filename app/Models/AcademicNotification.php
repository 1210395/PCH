<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicNotification extends Model
{
    use HasFactory;

    protected $table = 'academic_notifications';

    protected $fillable = [
        'academic_account_id',
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
     * Get the academic account that owns the notification
     */
    public function academicAccount()
    {
        return $this->belongsTo(AcademicAccount::class);
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
     * Scope for specific academic account
     */
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('academic_account_id', $accountId);
    }
}
