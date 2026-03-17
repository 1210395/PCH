<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An image associated with a Product.
 *
 * Stores the image file path relative to the storage disk and an
 * optional sort order for gallery display. One product can have many images.
 */
class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
        'display_order',
        'is_primary',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'display_order' => 'integer',
        'is_primary' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
