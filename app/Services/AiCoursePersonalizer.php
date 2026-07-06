<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\PersonalizedCourse;
use App\Models\PersonalizedCourseLesson;
use App\Models\Question;
use App\Models\QuizSubmission;
use App\Services\KnowledgeIngestion\KnowledgeIngestionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AiCoursePersonalizer
{
    /**
     * Generate a personalized course from a quiz submission.
     */
    public function generateForSubmission(QuizSubmission $submission): PersonalizedCourse
    {
        $quiz = Lesson::findOrFail($submission->quiz_id);
        $sourceCourse = $quiz->course_id ? Course::find($quiz->course_id) : null;

        $analysis = $this->analyzeSubmission($submission, $quiz);

        $existing = PersonalizedCourse::where('user_id', $submission->user_id)
            ->where('source_submission_id', $submission->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        $structure = $this->generateCourseStructure($quiz, $sourceCourse, $analysis);
        $structure['lessons'] = $this->applyHeyGenVideoMix(
            $this->balanceLessonFormats($structure['lessons'] ?? [])
        );

        $audioLessons = [];
        $language = $sourceCourse->language ?? 'English';

        $course = DB::transaction(function () use ($submission, $quiz, $sourceCourse, $analysis, $structure, &$audioLessons) {
            $course = PersonalizedCourse::create([
                'user_id' => $submission->user_id,
                'source_quiz_id' => $quiz->id,
                'source_submission_id' => $submission->id,
                'source_course_id' => $sourceCourse?->id,
                'title' => $structure['title'],
                'slug' => $this->uniqueSlug($structure['title'], $submission->user_id),
                'summary' => $structure['summary'] ?? null,
                'weak_topics' => $analysis['weak_topics'],
                'language' => $sourceCourse->language ?? 'en',
                'level' => $analysis['level'],
                'status' => 'ready',
                'source' => $structure['source'] ?? 'ai',
            ]);

            $lessonCount = count($structure['lessons']);

            foreach ($structure['lessons'] as $index => $lesson) {
                $format = $this->resolveLessonFormat($lesson, $index, $lessonCount);
                $lesson['lesson_format'] = $format;

                $narration = trim((string) ($lesson['video_script'] ?? $lesson['narration_script'] ?? ''));
                $media = $this->resolveLessonMedia($lesson, $language, $format);

                $created = PersonalizedCourseLesson::create([
                    'personalized_course_id' => $course->id,
                    'sort' => $index + 1,
                    'section_title' => $lesson['section_title'] ?? null,
                    'title' => $lesson['title'],
                    'content' => $lesson['content'] ?? '',
                    'key_points' => $lesson['key_points'] ?? [],
                    'practice_prompt' => $lesson['practice_prompt'] ?? null,
                    'lesson_format' => $format,
                    'video_provider' => $media['provider'] ?? null,
                    'video_id' => $media['video_id'] ?? null,
                    'video_url' => $media['url'] ?? null,
                    'video_embed' => $media['embed'] ?? null,
                    'video_title' => $media['title'] ?? null,
                    'video_thumbnail' => $media['thumbnail'] ?? null,
                    'video_query' => $media['query'] ?? null,
                    'video_status' => $media['status'] ?? null,
                    'video_external_id' => $media['external_id'] ?? null,
                    'video_script' => $media['script'] ?? $narration ?: null,
                    'video_error' => $media['error'] ?? null,
                    'video_requested_at' => ($media['status'] ?? null) === 'pending' ? now() : null,
                    'audio_url' => $media['audio_url'] ?? null,
                    'audio_status' => $media['audio_status'] ?? null,
                    'audio_provider' => $media['audio_provider'] ?? null,
                ]);

                if ($format === 'audio' && empty($created->audio_url)) {
                    $audioLessons[] = $created->id;
                }
            }

            return $course;
        });

        if (!empty($audioLessons)) {
            @set_time_limit(max(300, (int) ini_get('max_execution_time')));
            $lessons = PersonalizedCourseLesson::whereIn('id', $audioLessons)->get();
            foreach ($lessons as $audioLesson) {
                $this->finalizeLessonAudio($audioLesson, $language);
            }
        }

        return $course->fresh('lessons');
    }

    /**
     * Ensure Gemini/OpenAI narration file exists (used on lesson view if generation was skipped).
     */
    public function ensureLessonAudio(PersonalizedCourseLesson $lesson, string $language): bool
    {
        if ($lesson->audio_url && $lesson->audio_status === 'ready') {
            return true;
        }

        return $this->finalizeLessonAudio($lesson, $language);
    }

    /**
     * Rebalance formats and media for an already-generated course (all-text fix).
     */
    public function rebalanceExistingCourse(PersonalizedCourse $course): int
    {
        $language = $course->language ?? 'English';
        $lessons = $course->lessons()->orderBy('sort')->get();
        if ($lessons->isEmpty()) {
            return 0;
        }

        $structure = $lessons->map(fn ($l) => [
            'title' => $l->title,
            'content' => $l->content,
            'lesson_format' => $l->lesson_format,
            'video_script' => $l->video_script,
            'narration_script' => $l->video_script,
            'video_query' => $l->video_query,
            'key_points' => $l->key_points ?? [],
        ])->values()->all();

        $balanced = $this->balanceLessonFormats($structure);
        $updated = 0;

        foreach ($lessons as $i => $lesson) {
            $data = $balanced[$i] ?? null;
            if (!$data) {
                continue;
            }

            $format = $data['lesson_format'];
            $media = $this->resolveLessonMedia($data, $language, $format);
            $script = $media['script'] ?? $data['video_script'] ?? $lesson->video_script;

            if ($format === 'text') {
                $lesson->update([
                    'lesson_format' => 'text',
                    'video_provider' => null,
                    'video_id' => null,
                    'video_url' => null,
                    'video_embed' => null,
                    'video_title' => null,
                    'video_thumbnail' => null,
                    'video_status' => null,
                    'video_external_id' => null,
                    'video_error' => null,
                    'video_requested_at' => null,
                    'audio_url' => null,
                    'audio_status' => null,
                    'audio_provider' => null,
                    'video_script' => $script,
                ]);
                $updated++;
                continue;
            }

            $lesson->update([
                'lesson_format' => $format,
                'video_script' => $script,
                'video_provider' => $media['provider'] ?? null,
                'video_id' => $media['video_id'] ?? null,
                'video_url' => $media['url'] ?? null,
                'video_embed' => $media['embed'] ?? null,
                'video_title' => $media['title'] ?? null,
                'video_thumbnail' => $media['thumbnail'] ?? null,
                'video_query' => $media['query'] ?? null,
                'video_status' => $media['status'] ?? null,
                'video_external_id' => $media['external_id'] ?? null,
                'video_error' => $media['error'] ?? null,
                'video_requested_at' => ($media['status'] ?? null) === 'pending' ? now() : null,
                'audio_url' => $media['audio_url'] ?? null,
                'audio_status' => $media['audio_status'] ?? null,
                'audio_provider' => $media['audio_provider'] ?? null,
            ]);

            if ($format === 'audio' && empty($lesson->fresh()->audio_url)) {
                $this->finalizeLessonAudio($lesson->fresh(), $language);
            }

            $updated++;
        }

        return $updated;
    }

    /**
     * Re-trigger HeyGen video generation for an existing lesson.
     * Useful when a previous attempt failed, or to regenerate a fresh video.
     * Returns true if a new HeyGen job was successfully requested.
     */
    public function regenerateLessonVideo(PersonalizedCourseLesson $lesson): bool
    {
        $format = $this->normalizeLessonFormat($lesson->lesson_format) ?? 'video';

        if ($format === 'audio') {
            return $this->regenerateLessonAudio($lesson);
        }

        if ($format === 'text') {
            $lesson->update(['lesson_format' => 'video']);
        }

        return $this->regenerateLessonVideoOnly($lesson);
    }

    /**
     * Regenerate audio narration for an audio-format lesson.
     */
    public function regenerateLessonAudio(PersonalizedCourseLesson $lesson): bool
    {
        $language = optional($lesson->course)->language ?? 'English';

        $script = $lesson->video_script;
        if (!$script) {
            $script = $this->buildVideoScript($lesson, $language);
        }
        if (!$script) {
            $lesson->update([
                'audio_status' => 'failed',
                'lesson_format' => 'audio',
            ]);
            return false;
        }

        $lesson->update([
            'lesson_format' => 'audio',
            'video_script' => $script,
            'audio_url' => null,
            'audio_status' => null,
            'audio_provider' => null,
        ]);

        return $this->finalizeLessonAudio($lesson->fresh(), $language);
    }

    private function regenerateLessonVideoOnly(PersonalizedCourseLesson $lesson): bool
    {
        $apiKey = $this->heyGenApiKey();
        if (!$apiKey) {
            $lesson->update([
                'video_status' => 'failed',
                'video_error' => 'HeyGen API key is not configured.',
            ]);
            return false;
        }

        $language = optional($lesson->course)->language ?? 'English';

        $script = $lesson->video_script;
        if (!$script) {
            $script = $this->buildVideoScript($lesson, $language);
        }
        if (!$script) {
            $lesson->update([
                'video_status' => 'failed',
                'video_error' => 'No script available to regenerate the video.',
            ]);
            return false;
        }

        $lesson->update(['lesson_format' => 'video']);

        $result = $this->requestHeyGenVideo($apiKey, [
            'title' => $lesson->title,
            'video_query' => $lesson->video_query,
        ], $script, $language);

        if (!$result || ($result['status'] ?? null) === 'failed') {
            $lesson->update([
                'video_status' => 'failed',
                'video_error' => $result['error'] ?? 'Unable to start HeyGen generation.',
            ]);
            return false;
        }

        $lesson->update([
            'video_provider' => 'heygen',
            'video_external_id' => $result['external_id'] ?? $result['video_id'] ?? null,
            'video_status' => 'pending',
            'video_url' => null,
            'video_embed' => null,
            'video_thumbnail' => null,
            'video_script' => $script,
            'video_error' => null,
            'video_requested_at' => now(),
        ]);

        return true;
    }

    /**
     * Refresh a HeyGen video for a previously-pending lesson.
     * Returns true if the lesson is now ready, false otherwise.
     */
    public function refreshHeyGenStatus(PersonalizedCourseLesson $lesson): bool
    {
        if ($lesson->video_provider !== 'heygen' || !$lesson->video_external_id) {
            return $lesson->video_status === 'ready';
        }
        if ($lesson->video_status === 'ready') {
            return true;
        }

        $apiKey = $this->heyGenApiKey();
        if (!$apiKey) {
            return false;
        }

        $status = $this->fetchHeyGenStatus($apiKey, $lesson->video_external_id);
        if (!$status) {
            return false;
        }

        if ($status['status'] === 'completed' && !empty($status['video_url'])) {
            $videoUrl = $this->persistHeyGenVideo($status['video_url'], $lesson->id) ?: $status['video_url'];
            $lesson->update([
                'video_status' => 'ready',
                'video_url' => $this->sanitizeMediaUrl($videoUrl),
                'video_thumbnail' => $this->sanitizeMediaUrl($status['thumbnail_url'] ?? $lesson->video_thumbnail),
            ]);
            return true;
        }

        if ($status['status'] === 'failed') {
            $lesson->update([
                'video_status' => 'failed',
                'video_error' => $status['error'] ?? 'HeyGen reported failure.',
            ]);
            return false;
        }

        return false;
    }

    /**
     * Normalize AI-chosen lesson format or infer one when missing.
     */
    private function resolveLessonFormat(array $lesson, int $index, int $total): string
    {
        $fromAi = $this->normalizeLessonFormat($lesson['lesson_format'] ?? null);
        if ($fromAi) {
            return $fromAi;
        }

        return $this->inferLessonFormat($index, $total);
    }

    private function normalizeLessonFormat(?string $format): ?string
    {
        $format = strtolower(trim((string) $format));
        $aliases = [
            'reading' => 'text', 'read' => 'text', 'texte' => 'text', 'written' => 'text', 'lecture' => 'text',
            'listen' => 'audio', 'listening' => 'audio', 'audio' => 'audio', 'podcast' => 'audio', 'écoute' => 'audio',
            'video' => 'video', 'vidéo' => 'video', 'visual' => 'video', 'film' => 'video',
        ];
        if (isset($aliases[$format])) {
            $format = $aliases[$format];
        }
        return in_array($format, ['video', 'audio', 'text'], true) ? $format : null;
    }

    /**
     * Ensure the course is not 100% text — force a pedagogical mix.
     */
    private function balanceLessonFormats(array $lessons): array
    {
        $count = count($lessons);
        if ($count === 0) {
            return $lessons;
        }

        $formats = collect($lessons)
            ->map(fn ($l) => $this->normalizeLessonFormat($l['lesson_format'] ?? null))
            ->all();

        $videoCount = count(array_filter($formats, fn ($f) => $f === 'video'));
        $audioCount = count(array_filter($formats, fn ($f) => $f === 'audio'));
        $textCount = count(array_filter($formats, fn ($f) => $f === 'text' || $f === null));

        $needsBalance = ($count >= 3 && ($videoCount < 1 || $audioCount < 1))
            || ($count === 2 && ($videoCount + $audioCount) < 1)
            || ($textCount === $count);

        if (!$needsBalance) {
            foreach ($lessons as $i => $lesson) {
                $fmt = $formats[$i] ?? $this->inferLessonFormat($i, $count);
                $lessons[$i]['lesson_format'] = $fmt;
                if (in_array($fmt, ['video', 'audio'], true)) {
                    $this->ensureLessonNarrationInStructure($lessons[$i]);
                }
            }
            return $this->applyHeyGenVideoMix($lessons);
        }

        Log::info('Personalized course: balancing lesson formats (AI returned too much text-only).');

        for ($i = 0; $i < $count; $i++) {
            $target = $this->inferLessonFormat($i, $count);
            $lessons[$i]['lesson_format'] = $target;

            if ($target === 'video' && empty($lessons[$i]['video_query'])) {
                $lessons[$i]['video_query'] = $this->defaultVideoQuery($lessons[$i]);
            }

            if (in_array($target, ['video', 'audio'], true)) {
                $this->ensureLessonNarrationInStructure($lessons[$i]);
            }
        }

        return $this->applyHeyGenVideoMix($lessons);
    }

    /**
     * When HeyGen is configured, ensure enough lessons use video format (AI presenter).
     */
    private function applyHeyGenVideoMix(array $lessons): array
    {
        if (!$this->heyGenApiKey()) {
            return $lessons;
        }

        $count = count($lessons);
        if ($count === 0) {
            return $lessons;
        }

        $minVideos = max(1, (int) ceil($count * ($count >= 4 ? 0.5 : 0.34)));
        $videoCount = 0;
        foreach ($lessons as $lesson) {
            if (($lesson['lesson_format'] ?? '') === 'video') {
                $videoCount++;
            }
        }

        if ($videoCount >= $minVideos) {
            return $lessons;
        }

        for ($i = 0; $i < $count && $videoCount < $minVideos; $i++) {
            if (in_array($lessons[$i]['lesson_format'] ?? 'text', ['audio', 'text'], true)) {
                $lessons[$i]['lesson_format'] = 'video';
                if (empty($lessons[$i]['video_query'])) {
                    $lessons[$i]['video_query'] = $this->defaultVideoQuery($lessons[$i]);
                }
                $this->ensureLessonNarrationInStructure($lessons[$i]);
                $videoCount++;
            }
        }

        return $lessons;
    }

    private function defaultVideoQuery(array $lesson): string
    {
        $title = trim((string) ($lesson['title'] ?? 'lesson'));
        return Str::limit($title . ' explained tutorial', 80, '');
    }

    private function ensureLessonNarrationInStructure(array &$lesson): void
    {
        $script = trim((string) ($lesson['video_script'] ?? $lesson['narration_script'] ?? ''));
        if ($script !== '') {
            $lesson['video_script'] = $script;
            $lesson['narration_script'] = $script;
            return;
        }

        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($lesson['content'] ?? ''))));
        $title = trim((string) ($lesson['title'] ?? 'this topic'));
        $intro = "Welcome! In this lesson, you will work on {$title}. ";
        $body = Str::limit($plain, 650, '');
        $outro = ' Take a moment to try the practice exercise. You can do this!';
        $script = trim($intro . $body . $outro);

        $lesson['video_script'] = $script;
        $lesson['narration_script'] = $script;
    }

    /**
     * Balanced mix when AI does not specify formats (fallback courses).
     */
    private function inferLessonFormat(int $index, int $total): string
    {
        if ($total <= 1) {
            return $this->heyGenApiKey() ? 'video' : 'text';
        }

        $pattern = $this->heyGenApiKey()
            ? ['video', 'video', 'audio', 'text', 'video', 'audio']
            : ['video', 'audio', 'text', 'text', 'audio', 'video'];
        $format = $pattern[$index % count($pattern)];

        if ($format === 'video' && !$this->heyGenApiKey() && !$this->youtubeApiKey()) {
            return 'audio';
        }

        return $format;
    }

    /**
     * Resolve media based on AI-chosen lesson_format.
     */
    private function resolveLessonMedia(array $lesson, string $language, string $format): array
    {
        $script = trim((string) ($lesson['video_script'] ?? $lesson['narration_script'] ?? ''));

        return match ($format) {
            'video' => $this->resolveLessonVideo($lesson, $language),
            'audio' => $this->resolveLessonAudioPlan($lesson, $script),
            default => [
                'script' => $script ?: null,
            ],
        };
    }

    /**
     * Prepare audio lesson: store narration script; MP3 generated after lesson is saved.
     */
    private function resolveLessonAudioPlan(array $lesson, string $script): array
    {
        if ($script === '') {
            $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($lesson['content'] ?? ''))));
            $script = Str::limit($plain, 900, '');
        }

        return [
            'script' => $script ?: null,
            'audio_status' => $script ? 'pending' : null,
        ];
    }

    /**
     * Generate narration via Gemini TTS (preferred), OpenAI TTS, or browser fallback.
     */
    public function finalizeLessonAudio(PersonalizedCourseLesson $lesson, string $language): bool
    {
        $script = trim((string) $lesson->video_script);
        if ($script === '') {
            $script = $this->buildVideoScript($lesson, $language) ?? '';
        }
        if ($script === '') {
            $lesson->update(['audio_status' => 'failed', 'lesson_format' => 'audio']);
            return false;
        }

        $lesson->update([
            'lesson_format' => 'audio',
            'video_script' => $script,
            'audio_status' => 'pending',
            'audio_provider' => null,
        ]);

        $gemini = app(GeminiAudioService::class);
        if ($gemini->isConfigured()) {
            $audioUrl = $gemini->synthesizeSpeech($script, $language);
            if ($audioUrl) {
                $lesson->update([
                    'audio_url' => $audioUrl,
                    'audio_status' => 'ready',
                    'audio_provider' => 'gemini_tts',
                ]);
                return true;
            }
        }

        $audioUrl = $this->synthesizeOpenAiAudio($script, $language);
        if ($audioUrl) {
            $lesson->update([
                'audio_url' => $audioUrl,
                'audio_status' => 'ready',
                'audio_provider' => 'openai_tts',
            ]);
            return true;
        }

        $lesson->update([
            'audio_url' => null,
            'audio_status' => 'browser',
            'audio_provider' => 'browser',
        ]);

        return true;
    }

    private function synthesizeOpenAiAudio(string $script, string $language): ?string
    {
        $apiKey = $this->openAiTtsKey();
        if (!$apiKey) {
            return null;
        }

        $voice = $this->voiceForLanguage($language);

        $payload = [
            'model' => 'tts-1',
            'input' => Str::limit($script, 4000, ''),
            'voice' => $voice,
        ];

        $ch = curl_init('https://api.openai.com/v1/audio/speech');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$response || $httpCode >= 400) {
            Log::warning('OpenAI TTS failed (HTTP ' . $httpCode . '): ' . $err);
            return null;
        }

        $filename = 'personalized_audio/' . Str::uuid() . '.mp3';
        Storage::disk('public')->put($filename, $response);

        return Storage::disk('public')->url($filename);
    }

    private function openAiTtsKey(): ?string
    {
        $key = null;
        if (function_exists('get_settings')) {
            $key = get_settings('open_ai_secret_key');
        }
        $key = $key ?: env('OPENAI_API_KEY');

        if (!$key || str_contains($key, 'xxx')) {
            return null;
        }

        return $key;
    }

    private function voiceForLanguage(string $language): string
    {
        $lang = strtolower($language);
        if (str_contains($lang, 'french') || str_contains($lang, 'français') || $lang === 'fr') {
            return 'nova';
        }
        if (str_contains($lang, 'spanish') || str_contains($lang, 'español') || $lang === 'es') {
            return 'nova';
        }
        return 'alloy';
    }

    /**
     * Resolve a teaching video for a lesson.
     * Priority: HeyGen avatar video (when configured) > YouTube embed > YouTube search fallback.
     */
    private function resolveLessonVideo(array $lesson, string $language = 'English'): array
    {
        $this->ensureLessonNarrationInStructure($lesson);

        $query = trim((string) ($lesson['video_query'] ?? $lesson['title'] ?? ''));
        $script = trim((string) ($lesson['video_script'] ?? $lesson['narration_script'] ?? ''));

        if ($script === '') {
            $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($lesson['content'] ?? ''))));
            $script = Str::limit($plain, 900, '');
        }

        $heyGenKey = $this->heyGenApiKey();
        $heygenOnly = $heyGenKey && config('heygen.prefer_only', true);

        if ($heyGenKey && $script !== '') {
            $heygen = $this->requestHeyGenVideo($heyGenKey, $lesson, $script, $language);
            if ($heygen && ($heygen['status'] ?? null) !== 'failed') {
                return $heygen + ['query' => $query, 'script' => $script];
            }
            if ($heygenOnly) {
                return $heygen ?? [
                    'provider' => 'heygen',
                    'status' => 'failed',
                    'error' => 'HeyGen could not start video generation.',
                    'script' => $script,
                    'query' => $query,
                ];
            }
        }

        if ($heygenOnly) {
            return [
                'provider' => 'heygen',
                'status' => 'failed',
                'error' => 'HeyGen API key or narration script is missing.',
                'script' => $script ?: null,
                'query' => $query,
            ];
        }

        if ($query === '') {
            return ['script' => $script ?: null];
        }

        $youtubeKey = $this->youtubeApiKey();
        if ($youtubeKey) {
            $found = $this->searchYoutubeVideo($youtubeKey, $query, $language);
            if ($found) {
                return $found + ['query' => $query, 'status' => 'ready'];
            }
        }

        return [
            'provider' => 'youtube_search',
            'video_id' => null,
            'url' => 'https://www.youtube.com/results?search_query=' . urlencode($query),
            'embed' => null,
            'title' => null,
            'thumbnail' => null,
            'query' => $query,
            'status' => 'ready',
        ];
    }

    private function requestHeyGenVideo(string $apiKey, array $lesson, string $script, string $language): ?array
    {
        $avatarId = $this->heyGenAvatarId();
        $voiceId = $this->heyGenVoiceId($language);

        $payload = [
            'video_inputs' => [[
                'character' => [
                    'type' => 'avatar',
                    'avatar_id' => $avatarId,
                    'avatar_style' => 'normal',
                ],
                'voice' => [
                    'type' => 'text',
                    'input_text' => Str::limit($script, 1400, ''),
                    'voice_id' => $voiceId,
                ],
            ]],
            'dimension' => [
                'width' => 1280,
                'height' => 720,
            ],
            'caption' => false,
        ];

        $ch = curl_init('https://api.heygen.com/v2/video/generate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Api-Key: ' . $apiKey,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$response) {
            Log::warning('HeyGen generate returned empty: ' . $err);
            return null;
        }

        if ($httpCode >= 400) {
            Log::warning('HeyGen generate HTTP ' . $httpCode . ': ' . Str::limit($response, 500));
        }

        $decoded = json_decode($response, true);
        $videoId = $decoded['data']['video_id'] ?? null;

        if (!$videoId) {
            $message = $decoded['error']['message']
                ?? $decoded['message']
                ?? 'Unknown HeyGen error';
            Log::warning('HeyGen generation rejected: ' . $message);
            return [
                'provider' => 'heygen',
                'status' => 'failed',
                'error' => $message,
                'script' => $script,
            ];
        }

        return [
            'provider' => 'heygen',
            'external_id' => $videoId,
            'status' => 'pending',
            'script' => $script,
            'video_id' => $videoId,
            'url' => null,
            'embed' => null,
            'title' => $lesson['title'] ?? null,
            'thumbnail' => null,
        ];
    }

    private function fetchHeyGenStatus(string $apiKey, string $externalId): ?array
    {
        $url = 'https://api.heygen.com/v1/video_status.get?video_id=' . urlencode($externalId);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'X-Api-Key: ' . $apiKey,
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$response) {
            Log::warning('HeyGen status returned empty: ' . $err);
            return null;
        }

        $decoded = json_decode($response, true);
        $data = $decoded['data'] ?? null;
        if (!$data) {
            return null;
        }

        return [
            'status' => $data['status'] ?? 'processing',
            'video_url' => $data['video_url'] ?? null,
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'error' => $data['error']['detail'] ?? ($data['error']['message'] ?? null),
        ];
    }

    /**
     * Download HeyGen MP4 to public storage for stable playback.
     */
    private function persistHeyGenVideo(string $remoteUrl, int $lessonId): ?string
    {
        try {
            $response = Http::timeout(180)->get($remoteUrl);
            if (!$response->successful()) {
                return null;
            }
            $filename = 'personalized_video/lesson-' . $lessonId . '-' . Str::uuid() . '.mp4';
            Storage::disk('public')->put($filename, $response->body());

            return '/storage/' . ltrim(str_replace('\\', '/', $filename), '/');
        } catch (Throwable $e) {
            Log::warning('HeyGen video download failed: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * HeyGen signed URLs can exceed VARCHAR(512); keep a safe max for legacy columns.
     */
    private function sanitizeMediaUrl(?string $url, int $max = 65000): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        return Str::limit($url, $max, '');
    }

    private function stripHeyGenPendingNotice(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return null;
        }

        $cleaned = preg_replace(
            '/<p class="text-muted heygen-pending-notice">.*?<\/p>/s',
            '',
            $html,
            1
        );

        return trim((string) $cleaned) ?: null;
    }

    public function heyGenIsConfigured(): bool
    {
        return function_exists('heygen_is_configured') ? heygen_is_configured() : (bool) $this->heyGenApiKey();
    }

    private function heyGenApiKey(): ?string
    {
        $key = config('heygen.api_key');
        if (function_exists('get_settings')) {
            $settingsKey = get_settings('heygen_api_key');
            if (!empty($settingsKey)) {
                $key = $settingsKey;
            }
        }

        if (!$key || str_contains($key, 'xxx')) {
            return null;
        }

        return $key;
    }

    private function heyGenAvatarId(): string
    {
        if (function_exists('get_settings')) {
            $val = get_settings('heygen_avatar_id');
            if (!empty($val)) {
                return $val;
            }
        }

        return config('heygen.avatar_id');
    }

    private function heyGenVoiceId(string $language = 'English'): string
    {
        if (function_exists('get_settings')) {
            $val = get_settings('heygen_voice_id');
            if (!empty($val) && !$this->languageNeedsAlternateVoice($language)) {
                return $val;
            }
        }

        $lang = strtolower($language);
        if (str_contains($lang, 'french') || str_contains($lang, 'français') || $lang === 'fr') {
            if (function_exists('get_settings')) {
                $frSetting = get_settings('heygen_voice_id_fr');
                if (!empty($frSetting)) {
                    return $frSetting;
                }
            }
            $fr = config('heygen.voice_id_fr');
            if (!empty($fr)) {
                return $fr;
            }
        }
        if (str_contains($lang, 'spanish') || str_contains($lang, 'español') || $lang === 'es') {
            $es = config('heygen.voice_id_es');
            if (!empty($es)) {
                return $es;
            }
        }

        return config('heygen.voice_id');
    }

    private function languageNeedsAlternateVoice(string $language): bool
    {
        $lang = strtolower($language);

        return str_contains($lang, 'french') || str_contains($lang, 'français') || $lang === 'fr'
            || str_contains($lang, 'spanish') || str_contains($lang, 'español') || $lang === 'es';
    }

    private function searchYoutubeVideo(string $apiKey, string $query, string $language): ?array
    {
        $endpoint = 'https://www.googleapis.com/youtube/v3/search?'
            . http_build_query([
                'key' => $apiKey,
                'part' => 'snippet',
                'q' => $query,
                'maxResults' => 1,
                'type' => 'video',
                'safeSearch' => 'strict',
                'videoEmbeddable' => 'true',
                'relevanceLanguage' => $this->languageToCode($language),
            ]);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$response) {
            Log::warning('YouTube search returned empty: ' . $err);
            return null;
        }

        $decoded = json_decode($response, true);
        $item = $decoded['items'][0] ?? null;
        $videoId = $item['id']['videoId'] ?? null;

        if (!$videoId) {
            return null;
        }

        $snippet = $item['snippet'] ?? [];
        $thumbnail = $snippet['thumbnails']['high']['url']
            ?? $snippet['thumbnails']['default']['url']
            ?? null;

        return [
            'provider' => 'youtube',
            'video_id' => $videoId,
            'url' => 'https://www.youtube.com/watch?v=' . $videoId,
            'embed' => 'https://www.youtube.com/embed/' . $videoId,
            'title' => $snippet['title'] ?? null,
            'thumbnail' => $thumbnail,
        ];
    }

    private function languageToCode(string $language): string
    {
        $map = [
            'english' => 'en',
            'french' => 'fr',
            'francais' => 'fr',
            'français' => 'fr',
            'spanish' => 'es',
            'espanol' => 'es',
            'arabic' => 'ar',
            'german' => 'de',
            'portuguese' => 'pt',
            'italian' => 'it',
        ];

        $lc = strtolower(trim($language));
        return $map[$lc] ?? 'en';
    }

    private function youtubeApiKey(): ?string
    {
        if (function_exists('get_settings')) {
            $key = get_settings('youtube_api_key');
            if (!empty($key)) {
                return $key;
            }
        }
        return env('YOUTUBE_API_KEY') ?: null;
    }

    /**
     * Identify weak topics, score, level recommendation.
     */
    private function analyzeSubmission(QuizSubmission $submission, Lesson $quiz): array
    {
        $correctIds = collect(json_decode($submission->correct_answer ?? '[]', true) ?: [])
            ->filter(fn ($id) => is_numeric($id))->map(fn ($id) => (int) $id);
        $wrongIds = collect(json_decode($submission->wrong_answer ?? '[]', true) ?: [])
            ->filter(fn ($id) => is_numeric($id))->map(fn ($id) => (int) $id);

        $wrongQuestions = $wrongIds->isNotEmpty()
            ? Question::whereIn('id', $wrongIds)->get()
            : collect();

        $weakTitles = $wrongQuestions->map(fn ($q) => trim(strip_tags((string) $q->title)))
            ->filter()->values();

        $topics = $this->extractKeywords($weakTitles);

        $total = $correctIds->count() + $wrongIds->count();
        $ratio = $total > 0 ? $correctIds->count() / $total : 0;

        $level = match (true) {
            $ratio < 0.4 => 'beginner',
            $ratio < 0.75 => 'intermediate',
            default => 'advanced',
        };

        return [
            'score_ratio' => $ratio,
            'correct_count' => $correctIds->count(),
            'wrong_count' => $wrongIds->count(),
            'weak_questions' => $weakTitles->all(),
            'weak_topics' => $topics->all(),
            'level' => $level,
            'quiz_title' => $quiz->title,
        ];
    }

    private function extractKeywords(Collection $titles): Collection
    {
        $stopWords = [
            'the', 'and', 'for', 'with', 'from', 'that', 'this', 'your', 'you', 'are', 'was', 'were',
            'what', 'when', 'where', 'which', 'how', 'why', 'about', 'into', 'over', 'than',
            'les', 'des', 'pour', 'avec', 'dans', 'une', 'est', 'sur', 'par', 'que', 'qui', 'quoi',
            'comment', 'quand', 'mais', 'cette', 'cela', 'aussi', 'sont', 'leur', 'donc',
        ];

        return $titles->flatMap(function ($title) {
            $normalized = Str::of($title)->lower()->replaceMatches('/[^\p{L}0-9\s]/u', ' ')->value();
            return preg_split('/\s+/', trim($normalized)) ?: [];
        })->filter(fn ($w) => strlen($w) >= 4 && !in_array($w, $stopWords, true))
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(8)
            ->values();
    }

    /**
     * Build course structure via OpenAI when available, fallback template otherwise.
     */
    private function generateCourseStructure(Lesson $quiz, ?Course $sourceCourse, array $analysis): array
    {
        $key = $this->openAiKey();
        if ($key) {
            try {
                $aiStructure = $this->callOpenAi($key, $quiz, $sourceCourse, $analysis);
                if ($aiStructure) {
                    return $aiStructure + ['source' => 'ai'];
                }
            } catch (Throwable $e) {
                Log::warning('AI course generation failed, using fallback: ' . $e->getMessage());
            }
        }

        return $this->fallbackStructure($quiz, $analysis) + ['source' => 'fallback'];
    }

    private function callOpenAi(string $apiKey, Lesson $quiz, ?Course $sourceCourse, array $analysis): ?array
    {
        $model = $this->aiModel();
        $endpoint = $this->aiEndpoint();
        $language = $sourceCourse->language ?? 'English';
        $topics = implode(', ', $analysis['weak_topics']) ?: 'general fundamentals';
        $weakQuestions = collect($analysis['weak_questions'])->take(10)->implode("\n - ");

        $system = "You are a pedagogical course designer. You produce strict JSON only, no prose, no markdown fences.";
        $grounding = $this->knowledgeGroundingFor($quiz->title . ' ' . $topics . ' ' . $weakQuestions);
        $groundingBlock = $grounding !== ''
            ? "\n\nInternal reference (use only if directly relevant; do not cite sources in the course output):\n{$grounding}\n"
            : '';
        $user = <<<PROMPT
A student took the quiz "{$quiz->title}" and obtained a score ratio of {$analysis['score_ratio']}.
They missed the following questions (topics to remediate):
 - {$weakQuestions}

Detected weak topics: {$topics}.
Detected level: {$analysis['level']}.
{$groundingBlock}
Design a short remediation course in {$language} that specifically addresses these weaknesses.
The course must have:
- 1 title (clear, motivating)
- 1 summary (2-3 sentences)
- 4 to 6 lessons total, organized in 2 sections.
For each lesson include:
- section_title
- title
- content: 250-400 words HTML using <p>, <ul>, <li>, <strong>. No <html>, no <body>.
- key_points: array of 3-5 short strings
- practice_prompt: 1 actionable exercise the student can do alone
- lesson_format: YOU decide per lesson — exactly one of "video", "audio", or "text":
  * "video" — AI presenter video (HeyGen avatar reads the narration_script aloud). Prefer 2 video lessons when the course has 4+ lessons.
  * "audio" — definitions, grammar, pronunciation, concepts best heard (include narration_script)
  * "text" — reading, exercises, reflection
  CRITICAL (mandatory): the course MUST include at least 1 "video" AND at least 1 "audio" lesson. NEVER set every lesson to "text". Use a varied mix (example for 4 lessons: video, audio, text, text).
- narration_script: REQUIRED when lesson_format is "video" or "audio". A 90-130 word spoken script in {$language}, warm second-person tone ("you"). Plain text only, no HTML, no stage directions.
- video_query: REQUIRED only when lesson_format is "video". A 4-8 word YouTube search query in {$language}. Be specific. No brand names.

Respond ONLY with this JSON shape:
{
  "title": "...",
  "summary": "...",
  "lessons": [
    {"section_title": "...", "title": "...", "content": "<p>...</p>", "key_points": ["..."], "practice_prompt": "...", "lesson_format": "video|audio|text", "narration_script": "...", "video_query": "..."}
  ]
}
PROMPT;

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'temperature' => 0.4,
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$response) {
            Log::warning('AI returned empty response: ' . $err);
            return null;
        }

        $decoded = json_decode($response, true);
        $raw = $decoded['choices'][0]['message']['content'] ?? null;
        if (!$raw) {
            return null;
        }

        // Strip potential code fences just in case
        $cleaned = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($raw)));
        $parsed = json_decode($cleaned, true);

        if (!is_array($parsed) || empty($parsed['lessons']) || empty($parsed['title'])) {
            Log::warning('OpenAI returned invalid JSON for course generation.');
            return null;
        }

        $parsed['lessons'] = collect($parsed['lessons'])
            ->filter(fn ($l) => !empty($l['title']))
            ->map(function ($l) {
                $format = $this->normalizeLessonFormat($l['lesson_format'] ?? null);
                $narration = trim((string) ($l['narration_script'] ?? $l['video_script'] ?? ''));

                return [
                    'section_title' => trim((string) ($l['section_title'] ?? '')),
                    'title' => trim((string) $l['title']),
                    'content' => (string) ($l['content'] ?? ''),
                    'key_points' => array_values(array_filter(
                        is_array($l['key_points'] ?? null) ? $l['key_points'] : [],
                        fn ($v) => is_string($v) && trim($v) !== ''
                    )),
                    'practice_prompt' => trim((string) ($l['practice_prompt'] ?? '')),
                    'lesson_format' => $format,
                    'video_query' => trim((string) ($l['video_query'] ?? '')),
                    'video_script' => $narration,
                    'narration_script' => $narration,
                ];
            })->values()->all();

        if (empty($parsed['lessons'])) {
            return null;
        }

        $parsed['lessons'] = $this->balanceLessonFormats($parsed['lessons']);

        return $parsed;
    }

    private function fallbackStructure(Lesson $quiz, array $analysis): array
    {
        $topics = $analysis['weak_topics'] ?: ['key concepts'];
        $level = $analysis['level'];

        $title = 'Personalized review for: ' . $quiz->title;
        $summary = "This course was generated from your recent quiz results. "
            . "It focuses on the topics where you lost the most points and offers short lessons, "
            . "key takeaways and a practice prompt for each.";

        $lessons = [];
        $sectionA = 'Diagnostic & foundations';
        $sectionB = 'Targeted practice';

        $count = max(2, min(6, count($topics)));
        for ($i = 0; $i < $count; $i++) {
            $topic = $topics[$i] ?? ('topic ' . ($i + 1));
            $section = $i < ceil($count / 2) ? $sectionA : $sectionB;
            $format = $this->inferLessonFormat($i, $count);

            $lessons[] = [
                'section_title' => $section,
                'lesson_format' => $format,
                'title' => 'Master: ' . ucfirst($topic),
                'content' => '<p>This lesson targets <strong>' . e($topic) . '</strong>, '
                    . 'a topic detected as weak in your last attempt. Review the core idea, '
                    . 'try the suggested practice, then re-take the quiz.</p>'
                    . '<ul>'
                    . '<li>Definition and intuition</li>'
                    . '<li>Common pitfalls at the ' . e($level) . ' level</li>'
                    . '<li>Worked example you can reproduce</li>'
                    . '</ul>',
                'key_points' => [
                    'Understand the definition of ' . $topic,
                    'Spot the typical mistake on this topic',
                    'Reproduce a worked example',
                ],
                'practice_prompt' => 'Write a short note (5 lines) explaining "' . $topic . '" '
                    . 'to a friend who has never seen it before.',
                'video_query' => $format === 'video' ? ($topic . ' explained ' . $level) : '',
                'narration_script' => in_array($format, ['video', 'audio'], true)
                    ? ('Hi! Today we will focus on ' . $topic . '. '
                    . 'You missed it in your last attempt, so let\'s make it crystal clear. '
                    . 'First, remember the simple definition: ' . $topic . ' is one of the core ideas '
                    . 'at the ' . $level . ' level. Pay attention to the classic mistake people make on this topic, '
                    . 'then try the small practice exercise on your own. '
                    . 'Take your time, and re-take the quiz when you feel confident. You can do this!')
                    : '',
                'video_script' => in_array($format, ['video', 'audio'], true)
                    ? ('Hi! Today we will focus on ' . $topic . '. '
                    . 'You missed it in your last attempt, so let\'s make it crystal clear. '
                    . 'First, remember the simple definition: ' . $topic . ' is one of the core ideas '
                    . 'at the ' . $level . ' level. Pay attention to the classic mistake people make on this topic, '
                    . 'then try the small practice exercise on your own. '
                    . 'Take your time, and re-take the quiz when you feel confident. You can do this!')
                    : '',
            ];
        }

        return [
            'title' => $title,
            'summary' => $summary,
            'lessons' => $lessons,
        ];
    }

    private function uniqueSlug(string $title, int $userId): string
    {
        $base = Str::slug($title) ?: 'personalized-course';
        $slug = $base . '-' . $userId . '-' . substr((string) time(), -5);
        $i = 0;
        while (PersonalizedCourse::where('slug', $slug)->exists()) {
            $i++;
            $slug = $base . '-' . $userId . '-' . substr((string) time(), -5) . '-' . $i;
        }
        return $slug;
    }

    /**
     * Build a spoken video script automatically from a lesson.
     * Tries OpenAI first (for a natural pedagogical narration), falls back
     * to a cleaned version of the lesson HTML content.
     */
    private function buildVideoScript(PersonalizedCourseLesson $lesson, string $language): ?string
    {
        $plainContent = trim(preg_replace('/\s+/', ' ', strip_tags((string) $lesson->content)));
        $keyPoints = is_array($lesson->key_points) ? $lesson->key_points : [];

        $aiScript = $this->callOpenAiForScript(
            $lesson->title,
            $plainContent,
            $keyPoints,
            $language
        );

        if ($aiScript) {
            return $aiScript;
        }

        if ($plainContent === '') {
            return null;
        }

        $intro = sprintf("Welcome! In this short video, we will review %s.", (string) $lesson->title);
        $body = Str::limit($plainContent, 700, '');
        $outro = !empty($keyPoints)
            ? ' Remember these key points: ' . implode('. ', array_map('strval', array_slice($keyPoints, 0, 4))) . '.'
            : '';

        $script = trim($intro . ' ' . $body . $outro);
        return $script !== '' ? $script : null;
    }

    /**
     * Ask OpenAI to write a short spoken script (90-130 words) for an AI presenter.
     */
    private function callOpenAiForScript(string $title, string $content, array $keyPoints, string $language): ?string
    {
        $apiKey = $this->aiKey();
        if (!$apiKey) {
            return null;
        }
        if (trim($content) === '' && empty($keyPoints)) {
            return null;
        }

        $points = '';
        if (!empty($keyPoints)) {
            $points = "\nKey points to cover:\n - " . implode("\n - ", array_map('strval', $keyPoints));
        }

        $system = 'You write short spoken scripts for an AI presenter avatar. Output plain text only. No stage directions, no headings, no markdown, no quotes, no HTML.';
        $user = <<<PROMPT
Write a 90 to 130 word spoken narration in {$language} that teaches the following lesson to a student.

Lesson title: {$title}
Lesson content (reference, may be long, do NOT just copy it):
{$content}
{$points}

Rules:
- Warm, encouraging, second-person tone ("you").
- Start with a 1-sentence hook tied to the title.
- Explain the core idea in 2-3 clear sentences a beginner can follow.
- End with a 1-sentence call to practice.
- Plain text only. No markdown. No emojis. No stage directions.
PROMPT;

        $payload = [
            'model' => $this->aiModel(),
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'temperature' => 0.6,
        ];

        $ch = curl_init($this->aiEndpoint());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $httpCode >= 400) {
            Log::warning('AI script generation failed (HTTP ' . $httpCode . '): ' . $err . ' body=' . substr((string) $response, 0, 300));
            return null;
        }

        $decoded = json_decode($response, true);
        $raw = $decoded['choices'][0]['message']['content'] ?? null;
        if (!$raw) {
            return null;
        }

        $script = trim($raw);
        $script = preg_replace('/^["\'`]+|["\'`]+$/u', '', $script);
        $script = trim(preg_replace('/\s+/', ' ', $script));

        return $script !== '' ? Str::limit($script, 1500, '') : null;
    }

    /**
     * Which AI provider to use: 'openai' or 'deepseek'.
     */
    private function aiProvider(): string
    {
        if (function_exists('get_settings')) {
            $provider = strtolower((string) get_settings('ai_provider'));
            if (in_array($provider, ['openai', 'deepseek'], true)) {
                return $provider;
            }
        }
        $envProvider = strtolower((string) env('AI_PROVIDER', ''));
        if (in_array($envProvider, ['openai', 'deepseek'], true)) {
            return $envProvider;
        }
        return 'openai';
    }

    public function isAiConfigured(): bool
    {
        return (bool) $this->aiKey();
    }

    private function aiKey(): ?string
    {
        if ($this->aiProvider() === 'deepseek') {
            if (function_exists('get_settings')) {
                $key = get_settings('deepseek_api_key');
                if (!empty($key)) {
                    return $key;
                }
            }
            return env('DEEPSEEK_API_KEY') ?: null;
        }

        if (function_exists('get_settings')) {
            $key = get_settings('open_ai_secret_key');
            if (!empty($key)) {
                return $key;
            }
        }
        return env('OPENAI_API_KEY') ?: null;
    }

    private function aiModel(): string
    {
        if ($this->aiProvider() === 'deepseek') {
            if (function_exists('get_settings')) {
                $model = get_settings('deepseek_model');
                if (!empty($model)) {
                    return $model;
                }
            }
            return env('DEEPSEEK_MODEL', 'deepseek-chat');
        }

        if (function_exists('get_settings')) {
            $model = get_settings('open_ai_model');
            if (!empty($model)) {
                return $model;
            }
        }
        return env('OPENAI_MODEL', 'gpt-4o-mini');
    }

    private function aiEndpoint(): string
    {
        if ($this->aiProvider() === 'deepseek') {
            return 'https://api.deepseek.com/chat/completions';
        }
        return 'https://api.openai.com/v1/chat/completions';
    }

    /**
     * Kept for backwards-compatibility; now routes through the multi-provider
     * resolver so the caller can stay agnostic.
     */
    private function openAiKey(): ?string
    {
        return $this->aiKey();
    }

    private function openAiModel(): string
    {
        return $this->aiModel();
    }

    /**
     * Create one full course (metadata + curriculum) per platform category, from scratch.
     *
     * @return array{created: int, skipped: int, lesson_total: int, course_ids: int[], errors: string[]}
     */
    public function createAllCoursesFromScratch(
        int $lessonCount = 6,
        ?string $themeBrief = null,
        string $language = 'English',
        bool $skipExisting = true,
        ?int $userId = null
    ): array {
        $lessonCount = max(3, min(12, $lessonCount));
        $userId = $userId ?? auth()->id() ?? 1;
        $themeBrief = trim((string) ($themeBrief ?: get_phrase('Professional online training for each subject area.')));

        $categories = Category::query()
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->orderBy('title')
            ->get();

        if ($categories->isEmpty()) {
            $categories = Category::orderBy('title')->get();
        }

        if ($categories->isEmpty()) {
            throw new \RuntimeException(get_phrase('No categories found. Add categories before generating courses.'));
        }

        $toCreate = $categories->filter(function (Category $category) use ($skipExisting) {
            if (!$skipExisting) {
                return true;
            }

            return !Course::where('category_id', $category->id)
                ->whereIn('status', ['active', 'pending', 'upcoming'])
                ->exists();
        });

        $skipped = $categories->count() - $toCreate->count();
        $blueprints = $this->generateCourseCatalogBlueprints($toCreate, $themeBrief, $language);
        $blueprintByCategory = collect($blueprints)->keyBy('category_id');

        $created = 0;
        $lessonTotal = 0;
        $courseIds = [];
        $errors = [];

        foreach ($toCreate as $category) {
            $bp = $blueprintByCategory->get($category->id) ?? $this->fallbackCourseBlueprint($category, $themeBrief, $language);

            try {
                $course = $this->createMainCourseRecord($bp, $category, $userId, $language);
                $gen = $this->generateForMainCourse(
                    $course,
                    $lessonCount,
                    $bp['lesson_brief'] ?? $themeBrief
                );
                $created++;
                $lessonTotal += $gen['created'];
                $courseIds[] = $course->id;
            } catch (Throwable $e) {
                $errors[] = ($bp['title'] ?? $category->title) . ': ' . $e->getMessage();
                Log::warning('AI course creation failed for category ' . $category->id . ': ' . $e->getMessage());
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'lesson_total' => $lessonTotal,
            'course_ids' => $courseIds,
            'errors' => $errors,
        ];
    }

    /**
     * Generate main curriculum lessons (HeyGen video + Gemini audio + text) for a platform course.
     */
    /**
     * @return array{created: int, heygen: array{ready: int, pending: int, failed: int}}
     */
    public function generateForMainCourse(Course $course, int $lessonCount = 6, ?string $brief = null): array
    {
        $lessonCount = max(3, min(12, $lessonCount));
        $language = $course->language ?? 'English';
        $brief = trim((string) ($brief ?: strip_tags((string) ($course->description ?? $course->title))));

        $structure = $this->generateMainCourseStructure($course, $brief, $lessonCount, $language);
        $structure['lessons'] = $this->applyHeyGenVideoMix(
            $this->balanceLessonFormats($structure['lessons'] ?? [])
        );

        $audioLessonIds = [];
        $created = 0;
        $sectionSort = (int) (Section::where('course_id', $course->id)->max('sort') ?? 0);
        $lessonSort = (int) (Lesson::where('course_id', $course->id)->max('sort') ?? 0);
        $sectionMap = [];

        DB::transaction(function () use (
            $course,
            $structure,
            $language,
            $lessonCount,
            &$audioLessonIds,
            &$created,
            &$sectionSort,
            &$lessonSort,
            &$sectionMap
        ) {
            foreach ($structure['lessons'] as $index => $lessonData) {
                $format = $this->resolveLessonFormat($lessonData, $index, $lessonCount);
                $lessonData['lesson_format'] = $format;

                $sectionTitle = trim((string) ($lessonData['section_title'] ?? 'Section 1'));
                if (!isset($sectionMap[$sectionTitle])) {
                    $sectionSort++;
                    $section = Section::create([
                        'title' => $sectionTitle,
                        'user_id' => auth()->id() ?? $course->user_id,
                        'course_id' => $course->id,
                        'sort' => $sectionSort,
                    ]);
                    $sectionMap[$sectionTitle] = $section->id;
                }

                $media = $this->resolveLessonMedia($lessonData, $language, $format);
                $lessonSort++;
                $attrs = $this->mapMainLessonAttributes($lessonData, $media, $format, $course, $sectionMap[$sectionTitle], $lessonSort);

                $lesson = Lesson::create($attrs);
                $created++;

                if ($format === 'audio' && empty($lesson->audio_url)) {
                    $audioLessonIds[] = $lesson->id;
                }
            }
        });

        $heygenStats = $this->processHeyGenPendingForCourse($course->id, 3);

        if ($heygenStats['pending'] > 0) {
            Log::info("Course {$course->id}: {$heygenStats['ready']} HeyGen ready, {$heygenStats['pending']} still pending.");
        }

        return ['created' => $created, 'heygen' => $heygenStats];
    }

    /**
     * Poll HeyGen for pending main-course lessons (videos finish 1–5 min after the API call).
     *
     * @return array{ready: int, pending: int, failed: int}
     */
    public function processHeyGenPendingForCourse(int $courseId, int $pollRounds = 2): array
    {
        $stats = ['ready' => 0, 'pending' => 0, 'failed' => 0];

        if (!$this->heyGenApiKey()) {
            return $stats;
        }

        for ($round = 0; $round < $pollRounds; $round++) {
            $stats = ['ready' => 0, 'pending' => 0, 'failed' => 0];
            $lessons = Lesson::where('course_id', $courseId)
                ->where('video_provider', 'heygen')
                ->get();

            foreach ($lessons as $lesson) {
                if ($lesson->video_status === 'ready' && $lesson->lesson_src) {
                    $stats['ready']++;
                    continue;
                }
                if ($lesson->video_status === 'failed') {
                    $stats['failed']++;
                    continue;
                }
                if ($lesson->video_status === 'pending' && $lesson->video_external_id) {
                    $this->refreshHeyGenStatusForMainLesson($lesson);
                    $lesson->refresh();
                }
                if ($lesson->video_status === 'ready' && $lesson->lesson_src) {
                    $stats['ready']++;
                } elseif ($lesson->video_status === 'failed') {
                    $stats['failed']++;
                } else {
                    $stats['pending']++;
                }
            }

            if ($stats['pending'] === 0 || $round === $pollRounds - 1) {
                break;
            }

            sleep(10);
        }

        return $stats;
    }

    public function ensureMainLessonAudio(Lesson $lesson, ?string $language = null): bool
    {
        if ($lesson->audio_url && $lesson->audio_status === 'ready') {
            return true;
        }
        if (($lesson->ai_lesson_format ?? '') !== 'audio') {
            return false;
        }

        $language = $language ?? optional($lesson->course)->language ?? 'English';

        return $this->finalizeMainLessonAudio($lesson, $language);
    }

    public function refreshHeyGenStatusForMainLesson(Lesson $lesson): bool
    {
        if ($lesson->video_provider !== 'heygen' || !$lesson->video_external_id) {
            return $lesson->video_status === 'ready';
        }
        if ($lesson->video_status === 'ready' && $lesson->lesson_src) {
            return true;
        }

        $apiKey = $this->heyGenApiKey();
        if (!$apiKey) {
            return false;
        }

        $status = $this->fetchHeyGenStatus($apiKey, $lesson->video_external_id);
        if (!$status) {
            return false;
        }

        if ($status['status'] === 'completed' && !empty($status['video_url'])) {
            $videoUrl = $this->persistHeyGenVideo($status['video_url'], $lesson->id) ?: $status['video_url'];
            $content = $this->stripHeyGenPendingNotice($lesson->attachment) ?: $lesson->description;
            $lesson->update([
                'video_status' => 'ready',
                'ai_lesson_format' => 'video',
                'lesson_type' => 'html5',
                'lesson_src' => $this->sanitizeMediaUrl($videoUrl),
                'attachment' => $content,
                'thumbnail' => $this->sanitizeMediaUrl($status['thumbnail_url'] ?? $lesson->thumbnail),
                'duration' => $lesson->duration ?: '00:05:00',
            ]);

            return true;
        }

        if ($status['status'] === 'failed') {
            $lesson->update([
                'video_status' => 'failed',
                'video_error' => $status['error'] ?? 'HeyGen reported failure.',
            ]);

            return false;
        }

        return false;
    }

    public function regenerateMainLessonVideo(Lesson $lesson): bool
    {
        if (($lesson->ai_lesson_format ?? '') === 'audio') {
            return $this->finalizeMainLessonAudio($lesson, optional($lesson->course)->language ?? 'English');
        }

        $apiKey = $this->heyGenApiKey();
        if (!$apiKey) {
            $lesson->update(['video_status' => 'failed', 'video_error' => 'HeyGen API key is not configured.']);

            return false;
        }

        $language = optional($lesson->course)->language ?? 'English';
        $script = $lesson->video_script ?: $this->buildVideoScriptFromContent($lesson->title, $lesson->attachment, $lesson->key_points ?? [], $language);

        if (!$script) {
            $lesson->update(['video_status' => 'failed', 'video_error' => 'No script available.']);

            return false;
        }

        $result = $this->requestHeyGenVideo($apiKey, ['title' => $lesson->title], $script, $language);

        if (!$result || ($result['status'] ?? null) === 'failed') {
            $lesson->update([
                'video_status' => 'failed',
                'video_error' => $result['error'] ?? 'Unable to start HeyGen.',
                'ai_lesson_format' => 'video',
            ]);

            return false;
        }

        $lesson->update([
            'ai_lesson_format' => 'video',
            'lesson_type' => 'text',
            'video_provider' => 'heygen',
            'video_external_id' => $result['external_id'] ?? $result['video_id'] ?? null,
            'video_status' => 'pending',
            'lesson_src' => null,
            'video_script' => $script,
            'video_error' => null,
            'video_requested_at' => now(),
        ]);

        return true;
    }

    public function finalizeMainLessonAudio(Lesson $lesson, string $language): bool
    {
        $script = trim((string) $lesson->video_script);
        if ($script === '') {
            $script = $this->buildVideoScriptFromContent(
                $lesson->title,
                $lesson->attachment,
                is_array($lesson->key_points) ? $lesson->key_points : [],
                $language
            ) ?? '';
        }
        if ($script === '') {
            $lesson->update(['audio_status' => 'failed', 'ai_lesson_format' => 'audio']);

            return false;
        }

        $lesson->update(['video_script' => $script, 'audio_status' => 'pending']);

        $gemini = app(GeminiAudioService::class);
        if ($gemini->isConfigured()) {
            $url = $gemini->synthesizeSpeech($script, $language);
            if ($url) {
                $lesson->update([
                    'audio_url' => $url,
                    'audio_status' => 'ready',
                    'audio_provider' => 'gemini_tts',
                    'ai_lesson_format' => 'audio',
                    'lesson_type' => 'text',
                ]);

                return true;
            }
        }

        $lesson->update([
            'audio_url' => null,
            'audio_status' => 'browser',
            'audio_provider' => 'browser',
            'ai_lesson_format' => 'audio',
            'lesson_type' => 'text',
        ]);

        return true;
    }

    private function mapMainLessonAttributes(
        array $lessonData,
        array $media,
        string $format,
        Course $course,
        int $sectionId,
        int $sort
    ): array {
        $content = (string) ($lessonData['content'] ?? '');
        $keyPoints = $lessonData['key_points'] ?? [];
        if (!empty($keyPoints)) {
            $content .= '<h5>' . e(get_phrase('Key points')) . '</h5><ul>';
            foreach ($keyPoints as $point) {
                $content .= '<li>' . e($point) . '</li>';
            }
            $content .= '</ul>';
        }
        if (!empty($lessonData['practice_prompt'])) {
            $content .= '<p><strong>' . e(get_phrase('Practice')) . ':</strong> ' . e($lessonData['practice_prompt']) . '</p>';
        }

        $script = $media['script'] ?? $lessonData['video_script'] ?? $lessonData['narration_script'] ?? null;
        $attrs = [
            'title' => $lessonData['title'],
            'user_id' => auth()->id() ?? $course->user_id,
            'course_id' => $course->id,
            'section_id' => $sectionId,
            'sort' => $sort,
            'is_free' => 0,
            'status' => 1,
            'ai_lesson_format' => $format,
            'video_script' => $script,
            'video_provider' => $media['provider'] ?? null,
            'video_status' => $media['status'] ?? null,
            'video_external_id' => $media['external_id'] ?? null,
            'video_error' => $media['error'] ?? null,
            'video_requested_at' => ($media['status'] ?? null) === 'pending' ? now() : null,
            'audio_url' => $media['audio_url'] ?? null,
            'audio_status' => $media['audio_status'] ?? null,
            'audio_provider' => $media['audio_provider'] ?? null,
            'key_points' => $keyPoints,
            'summary' => Str::limit(strip_tags($content), 200),
            'attachment' => $content,
            'description' => $content,
            'duration' => '00:05:00',
        ];

        if ($format === 'video' && ($media['status'] ?? null) === 'ready' && !empty($media['url'])) {
            $attrs['lesson_type'] = 'html5';
            $attrs['lesson_src'] = $this->sanitizeMediaUrl($media['url']);
        } elseif ($format === 'video' && ($media['provider'] ?? null) === 'heygen' && ($media['status'] ?? null) === 'failed') {
            $attrs['lesson_type'] = 'text';
            $attrs['attachment'] = '<p class="alert alert-warning"><strong>' . e(get_phrase('Video generation failed')) . ':</strong> '
                . e($media['error'] ?? get_phrase('HeyGen error')) . '</p>' . $content;
        } elseif ($format === 'video' && ($media['provider'] ?? null) === 'heygen') {
            $attrs['lesson_type'] = 'text';
            $attrs['attachment'] = '<p class="text-muted heygen-pending-notice"><i class="fi fi-rr-spinner"></i> '
                . e(get_phrase('AI presenter video is being generated… Page will update when ready.')) . '</p>' . $content;
        } elseif ($format === 'video' && !empty($media['embed'])) {
            $attrs['lesson_type'] = 'video-url';
            $attrs['lesson_src'] = $media['embed'];
        } elseif ($format === 'video' && !empty($media['url'])) {
            $attrs['lesson_type'] = 'video-url';
            $attrs['lesson_src'] = $media['url'];
        } else {
            $attrs['lesson_type'] = 'text';
        }

        return $attrs;
    }

    private function generateMainCourseStructure(Course $course, string $brief, int $lessonCount, string $language): array
    {
        $key = $this->aiKey();
        if ($key) {
            try {
                $ai = $this->callOpenAiForMainCourse($key, $course, $brief, $lessonCount, $language);
                if ($ai) {
                    return $ai;
                }
            } catch (Throwable $e) {
                Log::warning('Main course AI generation failed: ' . $e->getMessage());
            }
        }

        return $this->fallbackMainCourseStructure($course, $brief, $lessonCount);
    }

    private function callOpenAiForMainCourse(string $apiKey, Course $course, string $brief, int $lessonCount, string $language): ?array
    {
        $system = 'You are a pedagogical course designer. You produce strict JSON only, no prose, no markdown fences.';
        $grounding = $this->knowledgeGroundingFor($course->title . ' ' . $brief);
        $groundingBlock = $grounding !== ''
            ? "\n\nInternal reference (use only if directly relevant; do not cite sources in the course output):\n{$grounding}\n"
            : '';
        $user = <<<PROMPT
Design a complete online course curriculum in {$language}.

Course title: {$course->title}
Brief: {$brief}
Number of lessons: {$lessonCount}
{$groundingBlock}
For each lesson include:
- section_title (use 2 section names across the course)
- title
- content: 300-450 words HTML using <p>, <ul>, <li>, <strong> only
- key_points: array of 3-5 strings
- practice_prompt: one exercise
- lesson_format: "video", "audio", or "text"
  * "video" = HeyGen AI presenter (include narration_script). Use at least 2 video lessons if {$lessonCount} >= 4
  * "audio" = Gemini narration (include narration_script). At least 1 audio lesson
  * "text" = reading only
- narration_script: required for video and audio (90-130 words, warm tone)
- video_query: only for video lessons (YouTube fallback search, 4-8 words)

Return JSON:
{"title":"...","summary":"...","lessons":[{"section_title":"...","title":"...","content":"...","key_points":[],"practice_prompt":"...","lesson_format":"video|audio|text","narration_script":"...","video_query":"..."}]}
PROMPT;

        $payload = [
            'model' => $this->aiModel(),
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'temperature' => 0.4,
        ];

        $ch = curl_init($this->aiEndpoint());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return null;
        }

        $decoded = json_decode($response, true);
        $raw = $decoded['choices'][0]['message']['content'] ?? null;
        if (!$raw) {
            return null;
        }

        $cleaned = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($raw)));
        $parsed = json_decode($cleaned, true);

        if (!is_array($parsed) || empty($parsed['lessons'])) {
            return null;
        }

        $parsed['lessons'] = collect($parsed['lessons'])
            ->filter(fn ($l) => !empty($l['title']))
            ->map(function ($l) {
                $format = $this->normalizeLessonFormat($l['lesson_format'] ?? null);
                $narration = trim((string) ($l['narration_script'] ?? $l['video_script'] ?? ''));

                return [
                    'section_title' => trim((string) ($l['section_title'] ?? 'Main section')),
                    'title' => trim((string) $l['title']),
                    'content' => (string) ($l['content'] ?? ''),
                    'key_points' => is_array($l['key_points'] ?? null) ? $l['key_points'] : [],
                    'practice_prompt' => trim((string) ($l['practice_prompt'] ?? '')),
                    'lesson_format' => $format,
                    'video_query' => trim((string) ($l['video_query'] ?? '')),
                    'video_script' => $narration,
                    'narration_script' => $narration,
                ];
            })->values()->all();

        return $parsed;
    }

    private function fallbackMainCourseStructure(Course $course, string $brief, int $lessonCount): array
    {
        $lessons = [];
        for ($i = 0; $i < $lessonCount; $i++) {
            $format = $this->inferLessonFormat($i, $lessonCount);
            $lessons[] = [
                'section_title' => $i < ceil($lessonCount / 2) ? 'Fundamentals' : 'Practice',
                'title' => ($course->title ?? 'Lesson') . ' — Part ' . ($i + 1),
                'lesson_format' => $format,
                'content' => '<p>' . e($brief) . '</p><p>' . e(get_phrase('Lesson')) . ' ' . ($i + 1) . '</p>',
                'key_points' => ['Core concept', 'Example', 'Practice'],
                'practice_prompt' => get_phrase('Review this lesson and write a short summary.'),
                'video_query' => $format === 'video' ? ($course->title . ' tutorial') : '',
                'narration_script' => in_array($format, ['video', 'audio'], true)
                    ? ('Welcome to lesson ' . ($i + 1) . ' about ' . $course->title . '. Let us learn step by step.')
                    : '',
                'video_script' => in_array($format, ['video', 'audio'], true)
                    ? ('Welcome to lesson ' . ($i + 1) . ' about ' . $course->title . '. Let us learn step by step.')
                    : '',
            ];
        }

        return [
            'title' => $course->title,
            'summary' => $brief,
            'lessons' => $lessons,
        ];
    }

    private function buildVideoScriptFromContent(string $title, ?string $html, array $keyPoints, string $language): ?string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)));

        return $this->callOpenAiForScript($title, $plain, $keyPoints, $language)
            ?: ($plain !== '' ? Str::limit($plain, 1200, '') : null);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Category>  $categories
     * @return array<int, array<string, mixed>>
     */
    private function generateCourseCatalogBlueprints(Collection $categories, string $themeBrief, string $language): array
    {
        if ($categories->isEmpty()) {
            return [];
        }

        $key = $this->aiKey();
        if ($key) {
            try {
                $ai = $this->callOpenAiForCourseCatalog($key, $categories, $themeBrief, $language);
                if ($ai) {
                    return $ai;
                }
            } catch (Throwable $e) {
                Log::warning('Course catalog AI failed: ' . $e->getMessage());
            }
        }

        return $categories->map(fn (Category $cat) => $this->fallbackCourseBlueprint($cat, $themeBrief, $language))->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Category>  $categories
     * @return array<int, array<string, mixed>>|null
     */
    private function callOpenAiForCourseCatalog(string $apiKey, Collection $categories, string $themeBrief, string $language): ?array
    {
        $lines = $categories->map(fn (Category $c) => "- id {$c->id}: {$c->title}")->implode("\n");

        $system = 'You are an instructional designer building a full online academy catalog. Return strict JSON only.';
        $user = <<<PROMPT
Create exactly ONE complete course per category below. Language: {$language}.
Global theme: {$themeBrief}

Categories:
{$lines}

For each course return:
- category_id (must match id above)
- title (clear, marketable, max 80 chars)
- short_description (max 200 chars, plain text)
- description (HTML: only <p>, <ul>, <li>, <strong>, 2-4 paragraphs)
- level: beginner, intermediate, or advanced
- requirements: array of 4-6 strings
- outcomes: array of 4-6 strings
- lesson_brief: one paragraph of topics for the lesson generator

Return JSON:
{"courses":[{"category_id":1,"title":"...","short_description":"...","description":"...","level":"beginner","requirements":[],"outcomes":[],"lesson_brief":"..."}]}
PROMPT;

        $payload = [
            'model' => $this->aiModel(),
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'temperature' => 0.5,
        ];

        $ch = curl_init($this->aiEndpoint());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return null;
        }

        $decoded = json_decode($response, true);
        $raw = $decoded['choices'][0]['message']['content'] ?? null;
        if (!$raw) {
            return null;
        }

        $cleaned = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($raw)));
        $parsed = json_decode($cleaned, true);
        if (!is_array($parsed) || empty($parsed['courses'])) {
            return null;
        }

        $validIds = $categories->pluck('id')->flip();

        return collect($parsed['courses'])
            ->filter(fn ($c) => !empty($c['title']) && isset($c['category_id']) && $validIds->has((int) $c['category_id']))
            ->map(function ($c) {
                $level = strtolower((string) ($c['level'] ?? 'beginner'));
                if (!in_array($level, ['everyone', 'beginner', 'intermediate', 'advanced'], true)) {
                    $level = 'beginner';
                }

                return [
                    'category_id' => (int) $c['category_id'],
                    'title' => Str::limit(trim((string) $c['title']), 255, ''),
                    'short_description' => Str::limit(trim((string) ($c['short_description'] ?? '')), 500, ''),
                    'description' => (string) ($c['description'] ?? ''),
                    'level' => $level,
                    'requirements' => array_values(array_filter((array) ($c['requirements'] ?? []))),
                    'outcomes' => array_values(array_filter((array) ($c['outcomes'] ?? []))),
                    'lesson_brief' => trim((string) ($c['lesson_brief'] ?? '')),
                ];
            })
            ->values()
            ->all();
    }

    private function fallbackCourseBlueprint(Category $category, string $themeBrief, string $language): array
    {
        $title = $category->title . ' — ' . get_phrase('Complete Course');

        return [
            'category_id' => $category->id,
            'title' => $title,
            'short_description' => get_phrase('Full training in') . ' ' . $category->title . ' (' . $language . ').',
            'description' => '<p>' . e($themeBrief) . '</p><p>' . e(get_phrase('Category')) . ': ' . e($category->title) . '</p>',
            'level' => 'beginner',
            'requirements' => [get_phrase('Motivation to learn')],
            'outcomes' => [get_phrase('Master the fundamentals of') . ' ' . $category->title],
            'lesson_brief' => $themeBrief . ' — ' . $category->title,
        ];
    }

    private function createMainCourseRecord(array $blueprint, Category $category, int $userId, string $language): Course
    {
        $title = $blueprint['title'] ?? ($category->title . ' Course');
        $now = date('Y-m-d H:i:s');

        $courseId = Course::insertGetId([
            'title' => $title,
            'slug' => slugify($title),
            'user_id' => $userId,
            'category_id' => $category->id,
            'course_type' => 'general',
            'status' => 'active',
            'level' => $blueprint['level'] ?? 'beginner',
            'language' => strtolower($language),
            'is_paid' => 0,
            'price' => 0,
            'discount_flag' => 0,
            'discounted_price' => 0,
            'short_description' => $blueprint['short_description'] ?? '',
            'description' => $blueprint['description'] ?? '',
            'requirements' => json_encode($blueprint['requirements'] ?? []),
            'outcomes' => json_encode($blueprint['outcomes'] ?? []),
            'instructor_ids' => json_encode([$userId]),
            'enable_drip_content' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Course::where('id', $courseId)->update(['slug' => slugify($title . '-' . $courseId)]);

        return Course::findOrFail($courseId);
    }

    private function knowledgeGroundingFor(string $text, ?string $department = null): string
    {
        if (! config('knowledge_sources.enabled', true)) {
            return '';
        }

        try {
            return app(KnowledgeIngestionService::class)->buildGrounding(trim($text), $department, 6);
        } catch (Throwable $e) {
            Log::debug('Course knowledge grounding skipped', ['error' => $e->getMessage()]);

            return '';
        }
    }
}
