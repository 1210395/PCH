<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An image associated with a Project.
 *
 * Stores the image file path relative to the storage disk and an
 * optional sort order for gallery display. One project can have many images.
 */
class ProjectImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'image_path',
        'display_order',
        'is_primary',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'display_order' => 'integer',
        'is_primary' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
