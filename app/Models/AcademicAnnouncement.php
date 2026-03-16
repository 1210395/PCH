<?php

namespace App\Models;

use App\Models\Traits\HasApprovalStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicAnnouncement extends Model
{
    use HasFactory, HasApprovalStatus;

    protected $table = 'academic_announcements';

    protected $fillable = [
        'academic_account_id',
        'title',
        'content',
        'image',
        'category',
        'priority',
        'publish_date',
        'expiry_date',
        'external_link',
        'views_count',
        // Approval fields
        'approval_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'expiry_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    // ==========================================
    // Relationships
    // ==========================================

    public function academicAccount()
    {
        return $this->belongsTo(AcademicAccount::class, 'academic_account_id');
    }

    public function approvedByAdmin()
    {
        return $this->belongsTo(Designer::class, 'approved_by');
    }

    // ==========================================
    // Accessors
    // ==========================================

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return url('media/' . $this->image);
        }
        return null;
    }

    public function getIsExpiredAttribute()
    {
        if (!$this->expiry_date) {
            return false;
        }
        return Carbon::parse($this->expiry_date)->isPast();
    }

    public function getIsPublishedAttribute()
    {
        return Carbon::parse($this->publish_date)->isPast() || Carbon::parse($this->publish_date)->isToday();
    }

    public function getIsScheduledAttribute()
    {
        return Carbon::parse($this->publish_date)->isFuture();
    }

    public function getCategoryLabelAttribute()
    {
        return match($this->category) {
            'general' => __('General'),
            'admission' => __('Admission'),
            'event' => __('Event'),
            'scholarship' => __('Scholarship'),
            'job' => __('Job Opportunity'),
            'other' => __('Other'),
            default => __(ucfirst($this->category ?? 'General')),
        };
    }

    public function getCategoryColorAttribute()
    {
        return match($this->category) {
            'general' => 'gray',
            'admission' => 'blue',
            'event' => 'purple',
            'scholarship' => 'green',
            'job' => 'orange',
            'other' => 'gray',
            default => 'gray',
        };
    }

    public function getPriorityLabelAttribute()
    {
        return match($this->priority) {
            'normal' => __('Normal'),
            'important' => __('Important'),
            'urgent' => __('Urgent'),
            default => __(ucfirst($this->priority ?? 'Normal')),
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'normal' => 'gray',
            'important' => 'orange',
            'urgent' => 'red',
            default => 'gray',
        };
    }

    // ==========================================
    // Scopes
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('approval_status', 'approved')
                     ->where('publish_date', '<=', now()->toDateString())
                     ->where(function ($q) {
                         $q->whereNull('expiry_date')
                           ->orWhere('expiry_date', '>=', now()->toDateString());
                     });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<', now()->toDateString());
    }

    public function scopeScheduled($query)
    {
        return $query->where('publish_date', '>', now()->toDateString());
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeImportant($query)
    {
        return $query->whereIn('priority', ['urgent', 'important']);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    /**
     * Visible to academic account (owner sees all their content)
     */
    public function scopeVisibleToAccount($query, $accountId)
    {
        return $query->where(function ($q) use ($accountId) {
            $q->where('approval_status', 'approved')
              ->orWhere('academic_account_id', $accountId);
        });
    }

    /**
     * Public visible (approved, published, and not expired)
     */
    public function scopePublicVisible($query)
    {
        return $query->where('approval_status', 'approved')
                     ->where('publish_date', '<=', now()->toDateString())
                     ->where(function ($q) {
                         $q->whereNull('expiry_date')
                           ->orWhere('expiry_date', '>=', now()->toDateString());
                     });
    }

    // ==========================================
    // Methods
    // ==========================================

    public function incrementViews()
    {
        $this->increment('views_count');
    }
}
