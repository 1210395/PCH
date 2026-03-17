<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Hierarchical content category used for projects and products.
 *
 * Uses `c_id` as the primary key (legacy naming convention).
 * Stores bilingual name (en/ar) and an optional parent category ID.
 */
class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'c_id';

    protected $fillable = [
        'c_name',
        'c_ar_name',
        'c_slug',
        'c_description',
        'c_ar_description',
        'c_thumb',
        'c_priority',
        'c_parent',
        'c_template',
        'c_ar_template',
        'c_itemsperpage',
        'c_editable',
        'c_type',
        'c_date',
        'c_showinpath',
        'c_view_option',
        'c_showthumb',
        'c_showtitle',
        'c_showdescription',
        'c_showdate',
        'c_showcatlist',
        'c_image_width',
        'c_image_height',
        'c_active',
        'c_gallery',
        'c_gallery_loc',
        'c_added_by',
        'c_edited_by',
        'c_lang',
        'c_deleted',
    ];

    // Accessor for name (to maintain compatibility)
    public function getNameAttribute()
    {
        return $this->c_name;
    }
}
