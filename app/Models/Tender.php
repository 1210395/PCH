<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'external_source',
        'title',
        'short_description',
        'description',
        'publisher',
        'company_name',
        'company_url',
        'publisher_type',
        'budget',
        'location',
        'locations',
        'status',
        'published_date',
        'deadline',
        'requirements',
        'source_url',
        'views_count',
        'active',
        'is_visible',
    ];

    protected $casts = [
        'published_date' => 'date',
        'deadline' => 'date',
        'locations' => 'array',
        'is_visible' => 'boolean',
        'active' => 'boolean',
        'views_count' => 'integer',
    ];

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by publisher type
     */
    public function scopeByPublisherType($query, $type)
    {
        return $query->where('publisher_type', $type);
    }

    /**
     * Scope for open tenders
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for tenders closing soon (within 14 days)
     */
    public function scopeClosingSoon($query)
    {
        return $query->where('status', 'closing_soon')
                     ->orWhere(function ($q) {
                         $q->where('deadline', '>=', now()->toDateString())
                           ->where('deadline', '<=', now()->addDays(14)->toDateString());
                     });
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('publisher', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to get only visible tenders
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope to get only hidden tenders
     */
    public function scopeHidden($query)
    {
        return $query->where('is_visible', false);
    }

    /**
     * Scope to filter by source (API or manual)
     */
    public function scopeFromApi($query)
    {
        return $query->whereNotNull('external_id');
    }

    /**
     * Scope to filter by manual creation
     */
    public function scopeManual($query)
    {
        return $query->whereNull('external_id');
    }

    /**
     * Check if tender is from external API
     */
    public function isFromApi(): bool
    {
        return !empty($this->external_id);
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'green',
            'closing_soon' => 'orange',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get publisher type badge color for UI
     */
    public function getPublisherTypeColorAttribute(): string
    {
        return match($this->publisher_type) {
            'government' => 'blue',
            'ngo' => 'green',
            'private' => 'purple',
            'academic' => 'orange',
            'media' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get formatted status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open' => 'Open',
            'closing_soon' => 'Closing Soon',
            'closed' => 'Closed',
            default => 'Unknown',
        };
    }

    /**
     * Get formatted publisher type label
     */
    public function getPublisherTypeLabelAttribute(): string
    {
        return match($this->publisher_type) {
            'government' => 'Government',
            'ngo' => 'NGO',
            'private' => 'Private Sector',
            'academic' => 'Academic',
            'media' => 'Media',
            'other' => 'Other',
            default => 'Other',
        };
    }

    /**
     * Get days until deadline
     */
    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (!$this->deadline) {
            return null;
        }
        return now()->diffInDays($this->deadline, false);
    }

    /**
     * Check if deadline is approaching (within 14 days)
     */
    public function isDeadlineApproaching(): bool
    {
        $days = $this->days_until_deadline;
        return $days !== null && $days >= 0 && $days <= 14;
    }

    /**
     * Auto-update status based on deadline
     */
    public function updateStatusBasedOnDeadline(): void
    {
        if (!$this->deadline) {
            return;
        }

        $days = $this->days_until_deadline;

        if ($days < 0) {
            $this->update(['status' => 'closed']);
        } elseif ($days <= 14) {
            $this->update(['status' => 'closing_soon']);
        }
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
