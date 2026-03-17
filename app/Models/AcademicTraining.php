<?php

namespace App\Models;

use App\Models\Traits\HasApprovalStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Training programme submitted by an academic institution.
 *
 * Requires admin approval via HasApprovalStatus before becoming publicly
 * visible. Displayed on the public trainings listing page alongside
 * admin-managed trainings and AcademicWorkshops.
 */
class AcademicTraining extends Model
{
    use HasFactory, HasApprovalStatus;

    protected $table = 'academic_trainings';

    protected $fillable = [
        'academic_account_id',
        'title',
        'short_description',
        'description',
        'image',
        'category',
        'level',
        'location_type',
        'location',
        'price',
        'duration',
        'start_date',
        'end_date',
        'registration_deadline',
        'registration_link',
        'max_participants',
        'requirements',
        'features',
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
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_deadline' => 'date',
            'requirements' => 'array',
            'features' => 'array',
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
        if (!$this->end_date) {
            return false;
        }
        return Carbon::parse($this->end_date)->isPast();
    }

    public function getIsUpcomingAttribute()
    {
        return Carbon::parse($this->start_date)->isFuture();
    }

    public function getIsOngoingAttribute()
    {
        $now = Carbon::now();
        $start = Carbon::parse($this->start_date);
        $end = $this->end_date ? Carbon::parse($this->end_date) : null;

        if ($end) {
            return $now->between($start, $end);
        }
        return $now->gte($start);
    }

    public function getRegistrationOpenAttribute()
    {
        if (!$this->registration_deadline) {
            return $this->is_upcoming;
        }
        return Carbon::parse($this->registration_deadline)->isFuture();
    }

    public function getLevelLabelAttribute()
    {
        return match($this->level) {
            'beginner' => __('Beginner'),
            'intermediate' => __('Intermediate'),
            'advanced' => __('Advanced'),
            default => __(ucfirst($this->level ?? 'All Levels')),
        };
    }

    public function getLevelColorAttribute()
    {
        return match($this->level) {
            'beginner' => 'green',
            'intermediate' => 'orange',
            'advanced' => 'red',
            default => 'gray',
        };
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
                     ->where(function ($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now()->toDateString());
                     });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('end_date')
                     ->where('end_date', '<', now()->toDateString());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString());
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now()->toDateString())
                     ->where(function ($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now()->toDateString());
                     });
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
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
     * Public visible (approved and not expired)
     */
    public function scopePublicVisible($query)
    {
        return $query->where('approval_status', 'approved')
                     ->where(function ($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now()->toDateString());
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
