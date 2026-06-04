<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Services\AiCoursePersonalizer;
use Illuminate\Console\Command;

class RefreshMainCourseHeyGen extends Command
{
    protected $signature = 'course:refresh-heygen {--course= : Optional course ID}';

    protected $description = 'Refresh or retry HeyGen videos on main course lessons';

    public function handle(AiCoursePersonalizer $personalizer): int
    {
        if (!$personalizer->heyGenIsConfigured()) {
            $this->error('HeyGen is not configured.');

            return self::FAILURE;
        }

        $query = Lesson::query()->where('video_provider', 'heygen');

        if ($courseId = $this->option('course')) {
            $query->where('course_id', $courseId);
        }

        foreach ($query->get() as $lesson) {
            if ($lesson->video_status === 'failed') {
                $personalizer->regenerateMainLessonVideo($lesson);
                $lesson->refresh();
                $this->line("Lesson #{$lesson->id} retry → {$lesson->video_status}");
                continue;
            }

            if ($lesson->video_status === 'pending') {
                $personalizer->refreshHeyGenStatusForMainLesson($lesson);
                $lesson->refresh();
                $this->line("Lesson #{$lesson->id} refresh → {$lesson->video_status}");
            }
        }

        return self::SUCCESS;
    }
}
