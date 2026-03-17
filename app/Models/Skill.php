<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A skill tag that can be associated with a designer's profile.
 *
 * Stores bilingual name (en/ar). Linked to designers via the
 * designer_skills pivot table.
 */
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
