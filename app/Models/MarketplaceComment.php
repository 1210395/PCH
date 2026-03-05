<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketplaceComment extends Model
{
    use HasFactory;

    protected $table = 'marketplace_comments';

    protected $fillable = [
        'marketplace_post_id',
        'designer_id',
        'parent_id',
        'content',
        'is_edited',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
    ];

    /**
     * Get the post this comment belongs to
     */
    public function post()
    {
        return $this->belongsTo(MarketplacePost::class, 'marketplace_post_id');
    }

    /**
     * Get the designer who wrote this comment
     */
    public function designer()
    {
        return $this->belongsTo(Designer::class, 'designer_id');
    }

    /**
     * Get the parent comment (for replies)
     */
    public function parent()
    {
        return $this->belongsTo(MarketplaceComment::class, 'parent_id');
    }

    /**
     * Get replies to this comment
     */
    public function replies()
    {
        return $this->hasMany(MarketplaceComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    /**
     * Scope to get only top-level comments (not replies)
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}
