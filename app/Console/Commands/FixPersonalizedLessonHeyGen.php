<?php

namespace App\Console\Commands;

use App\Models\PersonalizedCourse;
use App\Models\PersonalizedCourseLesson;
use App\Services\AiCoursePersonalizer;
use Illuminate\Console\Command;

class FixPersonalizedLessonHeyGen extends Command
{
    protected $signature = 'personalized:fix-heygen {--course= : Optional course ID} {--retry-all : Retry all failed/pending HeyGen jobs}';

    protected $description = 'Refresh, retry, or start HeyGen videos for personalized lessons';

    public function handle(AiCoursePersonalizer $personalizer): int
    {
        if (!$personalizer->heyGenIsConfigured()) {
            $this->error('HeyGen API key is not configured (.env or Admin → Open AI settings).');

            return self::FAILURE;
        }

        $courseId = $this->option('course');

        $pending = PersonalizedCourseLesson::query()
            ->where('video_provider', 'heygen')
            ->where('video_status', 'pending')
            ->when($courseId, fn ($q) => $q->where('personalized_course_id', $courseId))
            ->get();

        foreach ($pending as $lesson) {
            $before = $lesson->video_status;
            $personalizer->refreshHeyGenStatus($lesson);
            $lesson->refresh();
            $this->line(sprintf('Lesson #%d pending → %s%s', $lesson->id, $lesson->video_status, $lesson->video_url ? ' (ready)' : ''));
        }

        $failed = PersonalizedCourseLesson::query()
            ->where('video_provider', 'heygen')
            ->where('video_status', 'failed')
            ->when($courseId, fn ($q) => $q->where('personalized_course_id', $courseId))
            ->get();

        foreach ($failed as $lesson) {
            $lesson->update(['lesson_format' => 'video']);
            $ok = $personalizer->regenerateLessonVideo($lesson);
            $lesson->refresh();
            $this->line(sprintf('Lesson #%d retry → %s%s', $lesson->id, $ok ? 'started' : 'failed', $lesson->video_error ? ' — ' . $lesson->video_error : ''));
        }

        $query = PersonalizedCourseLesson::query()
            ->where('lesson_format', 'video')
            ->where(function ($q) {
                $q->whereNull('video_provider')
                    ->orWhere('video_provider', '!=', 'heygen')
                    ->orWhereNull('video_status');
            });

        if ($courseId) {
            $query->where('personalized_course_id', $courseId);
        }

        $lessons = $query->get();
        foreach ($lessons as $lesson) {
            $ok = $personalizer->regenerateLessonVideo($lesson);
            $lesson->refresh();
            $this->line(sprintf('Lesson #%d new HeyGen → %s', $lesson->id, $lesson->video_status));
        }

        if ($pending->isEmpty() && $failed->isEmpty() && $lessons->isEmpty()) {
            $this->info('No HeyGen lessons to refresh or retry.');
        }

        return self::SUCCESS;
    }
}
