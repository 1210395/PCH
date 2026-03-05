<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasApprovalStatus;

class Project extends Model
{
    use HasFactory, HasApprovalStatus;

    protected static function booted(): void
    {
        static::saved(function ($model) {
            \App\Services\CacheService::clearDashboardCache();
            if ($model->designer_id) {
                \App\Services\CacheService::clearDesignerCache($model->designer_id);
            }
        });

        static::deleted(function ($model) {
            \App\Services\CacheService::clearDashboardCache();
            if ($model->designer_id) {
                \App\Services\CacheService::clearDesignerCache($model->designer_id);
            }
        });
    }

    protected $fillable = [
        'designer_id',
        'category_id',
        'title',
        'description',
        'role',
        'category',
        'image',
        'featured',
        'views_count',
        'likes_count',
        'approval_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'designer_id' => 'integer',
        'category_id' => 'integer',
        'featured' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }

    public function category()
    {
        return $this->belongsTo(DesignCategory::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ProjectImage::class);
    }

    /**
     * Get the project's image URL.
     * Returns the first image from the images relationship if the image column is empty.
     */
    public function getImageAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Check if images relationship is loaded to avoid N+1 query
        if ($this->relationLoaded('images')) {
            $firstImage = $this->images->first();
            return $firstImage ? $firstImage->image_path : null;
        }

        // Fallback: query the database if relationship not loaded
        $firstImage = $this->images()->first();
        return $firstImage ? $firstImage->image_path : null;
    }
}
