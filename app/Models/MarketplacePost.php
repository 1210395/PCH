<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasApprovalStatus;

/**
 * Community marketplace post submitted by a designer.
 *
 * Supports four post types: service, collaboration, showcase, and opportunity.
 * Uses HasApprovalStatus for moderation, stores tags as a JSON array,
 * and supports likes and comments.
 */
class MarketplacePost extends Model
{
    use HasFactory, HasApprovalStatus;

    protected static function booted(): void
    {
        static::saved(function ($model) {
            \App\Services\CacheService::clearDashboardCache();
            \App\Services\CacheService::clearMarketplaceCache();
            if ($model->designer_id) {
                \App\Services\CacheService::clearDesignerCache($model->designer_id);
            }
        });

        static::deleted(function ($model) {
            \App\Services\CacheService::clearDashboardCache();
            \App\Services\CacheService::clearMarketplaceCache();
            if ($model->designer_id) {
                \App\Services\CacheService::clearDesignerCache($model->designer_id);
            }
        });
    }

    protected $table = 'marketplace_posts';

    protected $fillable = [
        'designer_id',
        'title',
        'description',
        'category',
        'image',
        'type',
        'tags',
        'likes_count',
        'comments_count',
        'views_count',
        'bookmarks_count',
        'approval_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'views_count' => 'integer',
        'bookmarks_count' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the designer who created this post
     */
    public function designer()
    {
        return $this->belongsTo(Designer::class, 'designer_id');
    }

    /**
     * Get the comments for this post
     */
    public function comments()
    {
        return $this->hasMany(MarketplaceComment::class, 'marketplace_post_id');
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to search by title, description, or tags
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerm . '*'])
              ->orWhereRaw("JSON_SEARCH(tags, 'one', ?) IS NOT NULL", ["%{$searchTerm}%"]);
        });
    }

    /**
     * Scope to filter by tags
     */
    public function scopeWithTags($query, array $tags)
    {
        if (empty($tags)) {
            return $query;
        }

        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereRaw("JSON_SEARCH(tags, 'one', ?) IS NOT NULL", [$tag]);
            }
        });
    }
}
