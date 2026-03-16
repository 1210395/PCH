<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingCriteria extends Model
{
    protected $table = 'rating_criteria';

    protected $fillable = ['en_label', 'ar_label', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function responses()
    {
        return $this->hasMany(RatingCriteriaResponse::class);
    }

    public function ratings()
    {
        return $this->belongsToMany(ProfileRating::class, 'rating_criteria_responses', 'rating_criteria_id', 'profile_rating_id');
    }

    public function getLabelAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->ar_label : $this->en_label;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
