<?php

namespace App\Models;

use App\Models\Traits\HasApprovalStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Workshop event submitted by an academic institution.
 *
 * Requires admin approval via HasApprovalStatus before becoming publicly
 * visible. Displayed on the public trainings listing page.
 */
class AcademicWorkshop extends Model
{
    use HasFactory, HasApprovalStatus;

    protected $table = 'academic_workshops';

    protected $fillable = [
        'academic_account_id',
        'title',
        'short_description',
        'description',
        'image',
        'category',
        'location_type',
        'location',
        'price',
        'duration',
        'workshop_date',
        'start_time',
        'end_time',
        'max_participants',
        'requirements',
        'tools_provided',
        'has_certificate',
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
            'workshop_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'requirements' => 'array',
            'tools_provided' => 'array',
            'has_certificate' => 'boolean',
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
        return $this->workshop_date ? Carbon::parse($this->workshop_date)->isPast() : false;
    }

    public function getIsUpcomingAttribute()
    {
        return $this->workshop_date ? Carbon::parse($this->workshop_date)->isFuture() : false;
    }

    public function getIsTodayAttribute()
    {
        return $this->workshop_date ? Carbon::parse($this->workshop_date)->isToday() : false;
    }

    public function getFormattedTimeAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return Carbon::parse($this->start_time)->format('g:i A') . ' - ' . Carbon::parse($this->end_time)->format('g:i A');
        }
        if ($this->start_time) {
            return Carbon::parse($this->start_time)->format('g:i A');
        }
        return null;
    }

    public function getLocationTypeLabelAttribute()
    {
        return match($this->location_type) {
            'online' => __('Online'),
            'in-person' => __('In Person'),
            'hybrid' => __('Hybrid'),
            default => __(ucfirst($this->location_type ?? 'TBD')),
        };
    }

    public function getLocationTypeColorAttribute()
    {
        return match($this->location_type) {
            'online' => 'blue',
            'in-person' => 'green',
            'hybrid' => 'purple',
            default => 'gray',
        };
    }

    // ==========================================
    // Scopes
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('approval_status', 'approved')
                     ->where('workshop_date', '>=', now()->toDateString());
    }

    public function scopeExpired($query)
    {
        return $query->where('workshop_date', '<', now()->toDateString());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('workshop_date', '>=', now()->toDateString());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('workshop_date', now()->toDateString());
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLocationType($query, $type)
    {
        return $query->where('location_type', $type);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('short_description', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%");
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
     * Public visible (approved only, includes past workshops)
     */
    public function scopePublicVisible($query)
    {
        return $query->where('approval_status', 'approved');
    }

    // ==========================================
    // Methods
    // ==========================================

    public function incrementViews()
    {
        $this->increment('views_count');
    }
}
