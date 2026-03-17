<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Records a single page visit event for analytics.
 *
 * Visits are deduplicated at middleware level (same IP + page within 10 min).
 */
class PageVisit extends Model
{
    protected $fillable = [
        'page_key',
        'ip_address',
        'designer_id',
    ];
}
