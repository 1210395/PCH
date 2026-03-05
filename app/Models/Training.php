<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'short_description',
        'description',
        'image',
        'cover_image',
        'instructor_name',
        'instructor_title',
        'instructor_bio',
        'instructor_image',
        'category',
        'level',
        'location_type',
        'location',
        'price',
        'duration',
        'schedule',
        'start_date',
        'end_date',
        'languages',
        'has_certificate',
        'features',
        'learning_outcomes',
        'syllabus',
        'requirements',
        'tools',
        'enrolled_students',
        'rating',
        'reviews_count',
        'views_count',
        'featured',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'languages' => 'array',
        'features' => 'array',
        'learning_outcomes' => 'array',
        'syllabus' => 'array',
        'requirements' => 'array',
        'tools' => 'array',
        'has_certificate' => 'boolean',
        'featured' => 'boolean',
        'enrolled_students' => 'integer',
        'rating' => 'decimal:1',
        'reviews_count' => 'integer',
        'views_count' => 'integer',
    ];

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope to filter by location type
     */
    public function scopeByLocationType($query, $type)
    {
        return $query->where('location_type', $type);
    }

    /**
     * Scope to get featured trainings
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for upcoming trainings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString());
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('short_description', 'like', "%{$search}%")
              ->orWhere('instructor_name', 'like', "%{$search}%");
        });
    }

    /**
     * Get level badge color for UI
     */
    public function getLevelColorAttribute(): string
    {
        return match($this->level) {
            'beginner' => 'green',
            'intermediate' => 'orange',
            'advanced' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get location type badge color for UI
     */
    public function getLocationTypeColorAttribute(): string
    {
        return match($this->location_type) {
            'online' => 'blue',
            'in-person' => 'green',
            'hybrid' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get formatted level label
     */
    public function getLevelLabelAttribute(): string
    {
        return ucfirst($this->level ?? 'beginner');
    }

    /**
     * Get formatted location type label
     */
    public function getLocationTypeLabelAttribute(): string
    {
        return match($this->location_type) {
            'online' => 'Online',
            'in-person' => 'In-Person',
            'hybrid' => 'Hybrid',
            default => 'Unknown',
        };
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
