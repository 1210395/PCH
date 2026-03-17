<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A comment posted by a designer on a Project.
 *
 * Stores the commenter's designer ID, the project ID, and the comment body.
 */
class ProjectComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'designer_id',
        'project_id',
        'parent_id',
        'content',
    ];

    /**
     * Get the designer that made the comment.
     */
    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }

    /**
     * Get the project that was commented on.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent()
    {
        return $this->belongsTo(ProjectComment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies()
    {
        return $this->hasMany(ProjectComment::class, 'parent_id');
    }
}