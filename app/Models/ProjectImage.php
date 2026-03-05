<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
