<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Design-specific subcategory used in the designer registration wizard.
 *
 * Maps design disciplines (e.g., graphic design, interior design) to
 * the sector/sub_sector fields on the Designer model.
 */
class DesignCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'category_id');
    }
}
