<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachProgress extends Model
{
    protected $table = 'coach_progress';

    protected $fillable = [
        'user_id',
        'track',
        'module_index',
        'step_index',
        'completed_step_ids',
        'status',
        'meta',
    ];

    protected $casts = [
        'completed_step_ids' => 'array',
        'meta' => 'array',
    ];
}
