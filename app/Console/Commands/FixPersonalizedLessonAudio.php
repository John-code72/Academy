<?php

namespace App\Console\Commands;

use App\Models\PersonalizedCourse;
use App\Models\PersonalizedCourseLesson;
use App\Services\AiCoursePersonalizer;
use Illuminate\Console\Command;

class FixPersonalizedLessonAudio extends Command
{
    protected $signature = 'personalized:fix-audio {--course= : Optional course ID}';

    protected $description = 'Regenerate Gemini audio for personalized lessons missing a playable file';

    public function handle(AiCoursePersonalizer $personalizer): int
    {
        $query = PersonalizedCourseLesson::query()
            ->where('lesson_format', 'audio')
            ->where(function ($q) {
                $q->whereNull('audio_url')
                    ->orWhereIn('audio_status', ['browser', 'failed', 'pending'])
                    ->orWhere('audio_provider', 'browser')
                    ->orWhere('audio_url', 'like', '%artisan/storage%')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('audio_url')
                            ->where('audio_url', 'not like', '/storage/%')
                            ->where('audio_url', 'not like', 'http%/storage/%');
                    });
            });

        if ($courseId = $this->option('course')) {
            $query->where('personalized_course_id', $courseId);
        }

        $lessons = $query->get();
        if ($lessons->isEmpty()) {
            $this->info('No audio lessons need fixing.');

            return self::SUCCESS;
        }

        $this->info('Fixing ' . $lessons->count() . ' lesson(s)...');

        foreach ($lessons as $lesson) {
            $course = PersonalizedCourse::find($lesson->personalized_course_id);
            $language = $course->language ?? 'English';
            $ok = $personalizer->regenerateLessonAudio($lesson);
            $lesson->refresh();
            $this->line(
                sprintf(
                    'Lesson #%d: %s → %s',
                    $lesson->id,
                    $ok ? 'OK' : 'FAIL',
                    $lesson->audio_url ?: $lesson->audio_status
                )
            );
        }

        return self::SUCCESS;
    }
}
