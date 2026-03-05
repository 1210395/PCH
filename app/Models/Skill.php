<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function designers()
    {
        return $this->belongsToMany(Designer::class, 'designer_skills', 'skill_id', 'designer_id');
    }
}
