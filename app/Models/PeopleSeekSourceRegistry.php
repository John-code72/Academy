<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeopleSeekSourceRegistry extends Model
{
    protected $table = 'peopleseek_source_registry';

    protected $fillable = [
        'source_key',
        'source_name',
        'source_url',
        'country_region',
        'source_category',
        'adapter',
        'access_method',
        'legal_status',
        'compliance_risk',
        'rate_limit_ms',
        'terms_notes',
        'robots_txt_snapshot',
        'robots_checked_at',
        'last_preflight_at',
        'last_preflight_result',
        'priority_tier',
        'is_active',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'meta' => 'array',
            'robots_checked_at' => 'datetime',
            'last_preflight_at' => 'datetime',
        ];
    }

    public function stagedProfiles(): HasMany
    {
        return $this->hasMany(StagedInterpreterProfile::class, 'source_key', 'source_key');
    }
}
