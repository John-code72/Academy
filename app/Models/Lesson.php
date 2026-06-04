<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    protected $fillable = [
        'title',
        'user_id',
        'course_id',
        'section_id',
        'lesson_type',
        'ai_lesson_format',
        'visibility_scope',
        'duration',
        'lesson_src',
        'attachment',
        'attachment_type',
        'video_type',
        'thumbnail',
        'is_free',
        'sort',
        'description',
        'summary',
        'video_script',
        'video_provider',
        'video_status',
        'video_external_id',
        'video_error',
        'video_requested_at',
        'audio_url',
        'audio_status',
        'audio_provider',
        'key_points',
        'status',
        'total_mark',
        'pass_mark',
        'retake',
    ];

    protected $casts = [
        'key_points' => 'array',
        'video_requested_at' => 'datetime',
    ];
}
