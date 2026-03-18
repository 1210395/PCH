<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Records a single full-page search event for analytics.
 * Only full searches are logged (not autocomplete keystrokes).
 */
class SearchLog extends Model
{
    protected $fillable = [
        'query',
        'results_count',
        'ip_address',
        'designer_id',
    ];
}
