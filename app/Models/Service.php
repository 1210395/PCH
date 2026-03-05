<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasApprovalStatus;

class Service extends Model
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
        'name',
        'description',
        'category',
        'image',
        'approval_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'designer_id' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }
}
