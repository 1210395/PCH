<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Fabrication laboratory (FabLab) directory entry.
 *
 * Admin-managed; does not go through an approval workflow.
 * Stores bilingual name/description, contact details, city, type,
 * services offered, and an optional logo image.
 */
class FabLab extends Model
{
    use HasFactory;

    protected $table = 'fab_labs';

    protected $fillable = [
        'name',
        'location',
        'city',
        'description',
        'short_description',
        'image',
        'cover_image',
        'rating',
        'reviews_count',
        'members',
        'equipment',
        'services',
        'features',
        'opening_hours',
        'opening_hours_ar',
        'type',
        'verified',
        'phone',
        'email',
        'website',
    ];

    protected $casts = [
        'equipment' => 'array',
        'services' => 'array',
        'features' => 'array',
        'verified' => 'boolean',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'members' => 'integer',
    ];

    /**
     * Scope to filter by city
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter verified labs
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Get locale-aware opening hours
     */
    public function getLocalizedOpeningHoursAttribute(): ?string
    {
        if (app()->getLocale() === 'ar' && !empty($this->opening_hours_ar)) {
            return $this->opening_hours_ar;
        }
        return $this->opening_hours;
    }

    /**
     * Scope to search by name, description, or location
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'like', '%' . $searchTerm . '%')
              ->orWhere('description', 'like', '%' . $searchTerm . '%')
              ->orWhere('location', 'like', '%' . $searchTerm . '%');
        });
    }
}
