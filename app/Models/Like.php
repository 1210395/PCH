<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic like pivot used for products and marketplace posts.
 *
 * Stores the designer ID (liker), the likeable model type (product or
 * marketplace_post), and the likeable ID.
 */
class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'designer_id',
        'likeable_type',
        'likeable_id',
    ];

    /**
     * Get the designer who made the like.
     */
    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }

    /**
     * Get the likeable model (Product or Project).
     */
    public function likeable()
    {
        return $this->morphTo();
    }
}
