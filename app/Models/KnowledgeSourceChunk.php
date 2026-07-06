<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeSourceChunk extends Model
{
    protected $fillable = [
        'document_id',
        'chunk_index',
        'content',
        'char_count',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(KnowledgeSourceDocument::class, 'document_id');
    }
}
