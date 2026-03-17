<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Records a like from a designer on a Project.
 *
 * Stores the designer ID and the project ID. Used to display like counts
 * and to toggle the liked state on project cards.
 */
class ProjectLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'designer_id',
        'project_id',
    ];

    /**
     * Get the designer that liked the project.
     */
    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }

    /**
     * Get the project that was liked.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}