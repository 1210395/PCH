<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores one designer's score for a single RatingCriteria within a ProfileRating.
 *
 * Pivot between ProfileRating and RatingCriteria; also stores the numeric score
 * given for that criterion.
 */
class RatingCriteriaResponse extends Model
{
    protected $fillable = ['profile_rating_id', 'rating_criteria_id'];

    public function rating()
    {
        return $this->belongsTo(ProfileRating::class, 'profile_rating_id');
    }

    public function criteria()
    {
        return $this->belongsTo(RatingCriteria::class, 'rating_criteria_id');
    }
}
