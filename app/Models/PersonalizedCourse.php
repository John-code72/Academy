<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalizedCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source_quiz_id',
        'source_submission_id',
        'source_course_id',
        'title',
        'slug',
        'summary',
        'weak_topics',
        'language',
        'level',
        'status',
        'source',
    ];

    protected $casts = [
        'weak_topics' => 'array',
    ];

    public function lessons(): HasMany
    {
        return $this->hasMany(PersonalizedCourseLesson::class)->orderBy('sort');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'source_course_id');
    }

    public function sourceQuiz(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'source_quiz_id');
    }
}
