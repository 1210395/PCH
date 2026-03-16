<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'designer_id',
        'rater_id',
        'rating',
        'comment',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * Boot the model - set initial status based on admin auto-accept setting
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rating) {
            // Only set status if not already explicitly set
            if (!isset($rating->status) || $rating->status === 'pending') {
                // Check if auto-accept is enabled for ratings
                if (AdminSetting::isAutoAcceptEnabled('ratings')) {
                    $rating->status = 'approved';
                    $rating->approved_at = now();
                }
            }
        });

        static::created(function ($rating) {
            // Notify the profile owner that they received a rating
            Notification::create([
                'designer_id' => $rating->designer_id,
                'type' => 'profile_rating',
                'title' => 'New Profile Rating!',
                'message' => "{$rating->rater->name} rated your profile",
                'read' => false,
                'data' => [
                    'rating_id' => $rating->id,
                    'rating' => $rating->rating,
                    'rater_id' => $rating->rater_id,
                    'rater_name' => $rating->rater->name,
                ]
            ]);
        });
    }

    // ==========================================
    // Relationships
    // ==========================================

    /**
     * Get the designer profile being rated
     */
    public function designer()
    {
        return $this->belongsTo(Designer::class, 'designer_id');
    }

    /**
     * Get the designer who gave the rating
     */
    public function rater()
    {
        return $this->belongsTo(Designer::class, 'rater_id');
    }

    /**
     * Get the admin who approved the rating
     */
    public function approver()
    {
        return $this->belongsTo(Designer::class, 'approved_by');
    }

    /**
     * Get the criteria checked by the rater
     */
    public function criteria()
    {
        return $this->belongsToMany(
            RatingCriteria::class,
            'rating_criteria_responses',
            'profile_rating_id',
            'rating_criteria_id'
        );
    }

    /**
     * Get the criteria response pivot records
     */
    public function criteriaResponses()
    {
        return $this->hasMany(RatingCriteriaResponse::class, 'profile_rating_id');
    }

    // ==========================================
    // Scopes
    // ==========================================

    /**
     * Scope to get only approved ratings
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get only pending ratings
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get only rejected ratings
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to get ratings for a specific designer profile
     */
    public function scopeForDesigner($query, $designerId)
    {
        return $query->where('designer_id', $designerId);
    }

    /**
     * Scope to get ratings by a specific rater
     */
    public function scopeByRater($query, $raterId)
    {
        return $query->where('rater_id', $raterId);
    }

    // ==========================================
    // Methods
    // ==========================================

    /**
     * Approve the rating
     */
    public function approve($adminId = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject/Delete the rating with a reason
     */
    public function reject($reason, $adminId = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        // Notify the rater that their rating was removed
        Notification::create([
            'designer_id' => $this->rater_id,
            'type' => 'rating_rejected',
            'title' => 'Rating Removed',
            'message' => "Your rating for {$this->designer->name}'s profile was removed. Reason: {$reason}",
            'read' => false,
            'data' => [
                'rating_id' => $this->id,
                'designer_id' => $this->designer_id,
                'designer_name' => $this->designer->name,
                'reason' => $reason,
            ]
        ]);
    }

    /**
     * Check if this rating is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if this rating is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if this rating is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // ==========================================
    // Static Methods
    // ==========================================

    /**
     * Get average rating for a designer
     */
    public static function getAverageRating($designerId)
    {
        return self::approved()
            ->where('designer_id', $designerId)
            ->avg('rating') ?? 0;
    }

    /**
     * Get total rating count for a designer
     */
    public static function getRatingCount($designerId)
    {
        return self::approved()
            ->where('designer_id', $designerId)
            ->count();
    }

    /**
     * Check if a user has already rated a designer
     */
    public static function hasRated($designerId, $raterId)
    {
        return self::where('designer_id', $designerId)
            ->where('rater_id', $raterId)
            ->exists();
    }
}
