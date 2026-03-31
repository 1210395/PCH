<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasApprovalStatus;

/**
 * Product catalogue entry submitted by a designer.
 *
 * Supports multi-image upload via ProductImage, approval workflow
 * via HasApprovalStatus, and like tracking via the Like pivot.
 * Cache is invalidated on save/delete via CacheService.
 */
class Product extends Model
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
        'title',
        'description',
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
        'featured' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the category label localized to the current locale.
     * DB stores English; this returns Arabic when locale is 'ar'.
     */
    public function getLocalizedCategoryAttribute(): string
    {
        return DropdownOption::localize($this->category ?? '', 'product_category');
    }

    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
