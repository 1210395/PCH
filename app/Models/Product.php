<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasApprovalStatus;

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

    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
