<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Records a single view event on a Project for analytics.
 *
 * Stores the viewer's IP or designer ID and the project ID.
 * Duplicate views within a session window are typically deduplicated
 * at the controller level.
 */
class ProjectView extends Model
{
    use HasFactory;

    protected $fillable = [
        'designer_id',
        'project_id',
        'ip_address',
    ];

    /**
     * Get the designer that viewed the project.
     */
    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }

    /**
     * Get the project that was viewed.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}