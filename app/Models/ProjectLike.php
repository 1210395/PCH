<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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