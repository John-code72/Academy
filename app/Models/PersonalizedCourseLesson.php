<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalizedCourseLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'personalized_course_id',
        'sort',
        'section_title',
        'title',
        'content',
        'key_points',
        'practice_prompt',
        'lesson_format',
        'video_provider',
        'video_id',
        'video_url',
        'video_embed',
        'video_title',
        'video_thumbnail',
        'video_query',
        'video_status',
        'video_external_id',
        'video_script',
        'video_error',
        'video_requested_at',
        'audio_url',
        'audio_status',
        'audio_provider',
    ];

    protected $casts = [
        'key_points' => 'array',
        'video_requested_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(PersonalizedCourse::class, 'personalized_course_id');
    }
}
