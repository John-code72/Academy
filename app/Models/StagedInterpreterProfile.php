<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StagedInterpreterProfile extends Model
{
    protected $fillable = [
        'source_key',
        'source_record_id',
        'content_hash',
        'profile',
        'dry_run',
        'legal_status',
        'provenance',
        'suppressed',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'profile' => 'array',
            'provenance' => 'array',
            'dry_run' => 'boolean',
            'suppressed' => 'boolean',
            'fetched_at' => 'datetime',
        ];
    }

    public function sourceRegistry(): BelongsTo
    {
        return $this->belongsTo(PeopleSeekSourceRegistry::class, 'source_key', 'source_key');
    }
}
