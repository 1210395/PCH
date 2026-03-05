<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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