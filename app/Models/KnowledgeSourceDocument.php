<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeSourceDocument extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'source_url',
        'source_type',
        'license',
        'commercial_use',
        'departments',
        'priority',
        'citation',
        'raw_path',
        'content_hash',
        'status',
        'chunk_count',
        'char_count',
        'error_message',
        'meta',
        'last_fetched_at',
    ];

    protected $casts = [
        'departments' => 'array',
        'commercial_use' => 'boolean',
        'meta' => 'array',
        'last_fetched_at' => 'datetime',
    ];

    public function chunks(): HasMany
    {
        return $this->hasMany(KnowledgeSourceChunk::class, 'document_id');
    }
}
