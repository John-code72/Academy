<?php

namespace App\Services;

use App\Models\CoachProgress;

class CoachCurriculumService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listTracks(): array
    {
        $tracks = [];

        foreach ($this->tracksConfig() as $slug => $track) {
            $tracks[] = [
                'slug' => $slug,
                'name' => $track['name'] ?? $slug,
                'description' => $track['description'] ?? '',
                'total_steps' => $this->totalSteps($slug),
                'module_count' => count($track['modules'] ?? []),
            ];
        }

        return $tracks;
    }

    public function getProgress(int $userId, string $track): ?CoachProgress
    {
        return CoachProgress::query()
            ->where('user_id', $userId)
            ->where('track', $track)
            ->first();
    }

    public function startTrack(int $userId, string $track, bool $restart = false): CoachProgress
    {
        $this->assertTrackExists($track);

        if ($restart) {
            CoachProgress::query()
                ->where('user_id', $userId)
                ->where('track', $track)
                ->delete();
        }

        return CoachProgress::query()->updateOrCreate(
            ['user_id' => $userId, 'track' => $track],
            [
                'module_index' => 0,
                'step_index' => 0,
                'completed_step_ids' => [],
                'status' => 'active',
                'meta' => ['started_at' => now()->toIso8601String()],
            ]
        );
    }

    public function resolveProgress(int $userId, string $track, bool $autoStart = true): ?CoachProgress
    {
        $progress = $this->getProgress($userId, $track);

        if ($progress || ! $autoStart) {
            return $progress;
        }

        return $this->startTrack($userId, $track);
    }

    /**
     * @return array<string, mixed>
     */
    public function currentStep(CoachProgress $progress): array
    {
        $track = $this->trackConfig($progress->track);
        $module = $track['modules'][$progress->module_index] ?? null;
        $step = is_array($module) ? ($module['steps'][$progress->step_index] ?? null) : null;

        if (! is_array($module) || ! is_array($step)) {
            return [];
        }

        return [
            'module_id' => $module['id'] ?? null,
            'module_title' => $module['title'] ?? '',
            'module_index' => $progress->module_index,
            'step_id' => $step['id'] ?? null,
            'step_title' => $step['title'] ?? '',
            'step_index' => $progress->step_index,
            'objective' => $step['objective'] ?? '',
            'practice' => $step['practice'] ?? '',
        ];
    }

    public function buildCurriculumPrompt(CoachProgress $progress): string
    {
        $marker = $this->advanceMarker();

        if ($progress->status === 'completed') {
            $trackName = $this->trackConfig($progress->track)['name'] ?? $progress->track;

            return str_replace('{track}', $trackName, (string) config('coach_prompts.curriculum_completed', ''));
        }

        $step = $this->currentStep($progress);
        if ($step === []) {
            return '';
        }

        $trackName = $this->trackConfig($progress->track)['name'] ?? $progress->track;

        return str_replace(
            ['{track}', '{position}', '{total}', '{plan}', '{module}', '{step}', '{objective}', '{practice}', '{marker}'],
            [
                $trackName,
                (string) $this->stepPosition($progress),
                (string) $this->totalSteps($progress->track),
                $this->buildTrackPlan($progress->track),
                (string) $step['module_title'],
                (string) $step['step_title'],
                (string) $step['objective'],
                (string) $step['practice'],
                $marker,
            ],
            (string) config('coach_prompts.curriculum_active', '')
        );
    }

    public function buildTrackPlan(string $track): string
    {
        $config = $this->trackConfig($track);
        $lines = [];

        foreach ($config['modules'] ?? [] as $module) {
            if (! is_array($module)) {
                continue;
            }

            $moduleTitle = (string) ($module['title'] ?? '');
            $stepTitles = [];

            foreach ($module['steps'] ?? [] as $step) {
                if (is_array($step) && ! empty($step['title'])) {
                    $stepTitles[] = (string) $step['title'];
                }
            }

            if ($moduleTitle !== '' && $stepTitles !== []) {
                $lines[] = '• ' . $moduleTitle . ' : ' . implode(', ', $stepTitles);
            }
        }

        return $lines !== [] ? implode("\n", $lines) : (string) ($config['description'] ?? '');
    }

    public function kickoffInstruction(string $mode = 'start'): string
    {
        $key = $mode === 'resume' ? 'coach_prompts.kickoff_resume' : 'coach_prompts.kickoff_start';

        return (string) config($key, '');
    }

    public function buildStepQuestion(array $step): string
    {
        $practice = trim((string) ($step['practice'] ?? ''));

        if ($practice === '') {
            return 'What is your perspective on this topic?';
        }

        if (str_ends_with($practice, '?')) {
            return $practice;
        }

        $lower = mb_strtolower($practice);
        $imperatives = [
            'describe', 'list', 'write', 'name', 'identify', 'explain', 'paraphrase',
            'draft', 'outline', 'respond', 'propose', 'rewrite', 'walk through',
            'compare', 'summarize', 'give', 'share', 'tell',
        ];

        foreach ($imperatives as $verb) {
            if (str_starts_with($lower, $verb . ' ') || $lower === $verb) {
                return ucfirst(rtrim($practice, '.')) . '?';
            }
        }

        return 'How would you approach this: ' . rtrim($practice, '.') . '?';
    }

    /**
     * Message de cours généré côté serveur — ne dépend pas de Gemini.
     */
    public function buildStepLessonMessage(CoachProgress $progress, string $mode = 'start'): string
    {
        $name = function_exists('ai_assistant_name') ? ai_assistant_name() : 'your coach';
        $trackName = $this->trackConfig($progress->track)['name'] ?? $progress->track;
        $step = $this->currentStep($progress);
        $plan = $this->buildTrackPlan($progress->track);
        $position = $this->stepPosition($progress);
        $total = $this->totalSteps($progress->track);

        if ($step === []) {
            return $this->buildLaunchBriefing($progress->track, $progress);
        }

        $question = $this->buildStepQuestion($step);
        $lines = [];

        if ($mode === 'start') {
            $lines[] = 'I\'m ' . $name . ', your Defrilex Academy coach.';
            $lines[] = '';
            $lines[] = 'We\'ll work through "' . $trackName . '" together (' . $total . ' steps). Here\'s the roadmap:';
            $lines[] = $plan;
            $lines[] = '';
            $lines[] = 'Let\'s start with step ' . $position . '/' . $total . ' — ' . $step['step_title'] . ':';
        } else {
            $lines[] = 'Welcome back! We\'re on step ' . $position . '/' . $total . ' — ' . $step['step_title'] . '.';
        }

        $lines[] = '';
        $lines[] = (string) ($step['objective'] ?? '');
        $lines[] = '';
        $lines[] = 'I\'d like to hear from you:';
        $lines[] = $question;

        return implode("\n", $lines);
    }

    public function buildAdvanceTransitionMessage(CoachProgress $progress): string
    {
        $step = $this->currentStep($progress);
        if ($step === []) {
            return '';
        }

        $position = $this->stepPosition($progress);
        $total = $this->totalSteps($progress->track);
        $question = $this->buildStepQuestion($step);

        $lines = [
            'Nice work — let\'s keep going!',
            '',
            'Step ' . $position . '/' . $total . ' — ' . $step['step_title'],
            '',
            (string) ($step['objective'] ?? ''),
            '',
            'Now tell me:',
            $question,
        ];

        return implode("\n", $lines);
    }

    public function ensureInteractiveReply(string $reply, ?CoachProgress $progress): string
    {
        if (! $progress || $progress->status !== 'active') {
            return $reply;
        }

        $step = $this->currentStep($progress);
        if ($step === []) {
            return $reply;
        }

        $tail = mb_substr(trim($reply), max(0, mb_strlen($reply) - 400));
        if (str_contains($tail, '?')) {
            return $reply;
        }

        $question = $this->buildStepQuestion($step);

        return trim($reply) . "\n\n" . $question;
    }

    /**
     * Quick replies + step card for the interactive UI.
     *
     * @return array{quick_replies: array<int, array<string, string>>, step_card: array<string, mixed>|null}
     */
    public function buildInteractiveMeta(?CoachProgress $progress): array
    {
        if (! $progress) {
            return [
                'quick_replies' => [
                    ['label' => 'Select a path above', 'message' => '', 'action' => 'focus_input'],
                ],
                'step_card' => null,
            ];
        }

        if ($progress->status === 'completed') {
            return [
                'quick_replies' => [
                    ['label' => 'Restart this path', 'message' => '', 'action' => 'restart'],
                ],
                'step_card' => null,
            ];
        }

        $step = $this->currentStep($progress);
        if ($step === []) {
            return ['quick_replies' => [], 'step_card' => null, 'coach_question' => null];
        }

        $question = $this->buildStepQuestion($step);

        return [
            'coach_question' => $question,
            'quick_replies' => [
                ['label' => 'My answer is…', 'message' => '', 'action' => 'answer_starter'],
                ['label' => 'I\'m not sure', 'message' => 'I\'m not sure — can you break the question down for me?'],
                ['label' => 'Give me a hint', 'message' => 'Give me a short hint to help me answer.'],
                ['label' => 'Show an example', 'message' => 'Can you show me an example answer?'],
            ],
            'step_card' => [
                'position' => $this->stepPosition($progress),
                'total' => $this->totalSteps($progress->track),
                'module_title' => (string) ($step['module_title'] ?? ''),
                'title' => (string) ($step['step_title'] ?? ''),
                'objective' => (string) ($step['objective'] ?? ''),
                'practice' => (string) ($step['practice'] ?? ''),
                'question' => $question,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function enrichCoachResponse(array $payload, ?CoachProgress $progress): array
    {
        return array_merge($payload, $this->buildInteractiveMeta($progress));
    }

    public function historyContainsCoachPlan(array $history): bool
    {
        foreach ($history as $turn) {
            if (($turn['role'] ?? '') !== 'model') {
                continue;
            }

            $text = mb_strtolower((string) ($turn['text'] ?? ''));
            if (str_contains($text, 'we\'ll work through')
                || str_contains($text, 'here\'s the curriculum')
                || str_contains($text, 'we\'ll start with step')
                || str_contains($text, 'nous allons travailler')
                || str_contains($text, 'voici les modules')) {
                return true;
            }
        }

        return false;
    }

    public function enforceCoachReply(string $reply, ?CoachProgress $progress): string
    {
        $text = mb_strtolower($reply);
        $forbidden = [
            'comment puis-je vous aider',
            'comment puis je vous aider',
            'comment puis-je vous assister',
            'how can i help',
            'how may i help',
            'how can i assist',
            'que puis-je faire pour vous',
            'de quoi avez-vous besoin',
            'what can i help',
            'how can i help you today',
        ];

        foreach ($forbidden as $phrase) {
            if (str_contains($text, $phrase)) {
                if ($progress && $progress->status === 'active') {
                    return $this->buildStepLessonMessage($progress, 'resume');
                }

                if ($progress) {
                    return $this->buildLaunchBriefing($progress->track, $progress);
                }

                return 'I\'m your coach. Select a learning path — I\'ll show you the modules we\'ll cover.';
            }
        }

        return $reply;
    }

    public function finalizeCoachReply(string $reply, ?CoachProgress $progress, bool $advanced = false): string
    {
        $reply = $this->enforceCoachReply($reply, $progress);

        if ($advanced || ! $progress || $progress->status !== 'active') {
            return $reply;
        }

        return $this->ensureInteractiveReply($reply, $progress);
    }

    public function resolveActiveTrackForUser(?int $userId = null): string
    {
        $tracks = $this->listTracks();
        $defaultSlug = (string) ($tracks[0]['slug'] ?? 'customer_service');

        if ($userId === null) {
            return $defaultSlug;
        }

        $active = CoachProgress::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->orderByDesc('updated_at')
            ->first();

        return $active?->track ?? $defaultSlug;
    }

    public function buildLaunchBriefing(string $track, ?CoachProgress $progress = null): string
    {
        $name = function_exists('ai_assistant_name') ? ai_assistant_name() : 'votre coach';
        $trackName = $this->trackConfig($track)['name'] ?? $track;
        $plan = $this->buildTrackPlan($track);
        $total = $this->totalSteps($track);

        $briefing = 'I\'m ' . $name . ', your Defrilex Academy coach.' . "\n\n";
        $briefing .= 'We\'ll work through the "' . $trackName . '" path (' . $total . ' steps).' . "\n\n";
        $briefing .= "Here's the curriculum:\n" . $plan;

        if ($progress && $progress->status === 'active' && $progress->track === $track) {
            $step = $this->currentStep($progress);
            $position = $this->stepPosition($progress);

            if ($step !== []) {
                $briefing .= "\n\nYou're on step " . $position . '/' . $total . ': ' . $step['step_title'] . '.';
                $briefing .= "\nI'll continue guiding you on this step.";
            }
        } else {
            $firstStep = $this->firstStepTitle($track);
            if ($firstStep !== '') {
                $briefing .= "\n\nWe'll start with: " . $firstStep . '.';
            }
        }

        return $briefing;
    }

    /**
     * Données coach injectées dans l'interface (page + widget).
     *
     * @return array{tracks: array<int, array<string, mixed>>, default_track: string, launch_briefing: string}
     */
    public function clientBootstrap(?int $userId = null): array
    {
        $tracks = $this->listTracks();
        $defaultTrack = $this->resolveActiveTrackForUser($userId);
        $progress = $userId ? $this->getProgress($userId, $defaultTrack) : null;

        if ($userId) {
            $active = CoachProgress::query()
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->orderByDesc('updated_at')
                ->first();

            if ($active) {
                $defaultTrack = $active->track;
                $progress = $active;
            }
        }

        $openingProgress = $progress;
        if (! $openingProgress && $userId) {
            $openingProgress = new CoachProgress([
                'user_id' => $userId,
                'track' => $defaultTrack,
                'module_index' => 0,
                'step_index' => 0,
                'status' => 'active',
                'completed_step_ids' => [],
            ]);
        }

        $openingMessage = $openingProgress
            ? $this->buildStepLessonMessage($openingProgress, ($progress && $progress->status === 'active') ? 'resume' : 'start')
            : '';

        $interactive = $this->buildInteractiveMeta($openingProgress);

        return [
            'tracks' => $tracks,
            'default_track' => $defaultTrack,
            'launch_briefing' => $this->buildLaunchBriefing($defaultTrack, $progress),
            'opening_message' => $openingMessage,
            'curriculum' => $this->buildCurriculumOutline($defaultTrack, $progress),
            'progress' => $progress ? $this->snapshot($progress) : null,
            'quick_replies' => $interactive['quick_replies'],
            'step_card' => $interactive['step_card'],
            'coach_question' => $interactive['coach_question'] ?? null,
        ];
    }

    private function firstStepTitle(string $track): string
    {
        foreach ($this->trackConfig($track)['modules'] ?? [] as $module) {
            if (! is_array($module)) {
                continue;
            }

            $first = $module['steps'][0]['title'] ?? null;
            if (is_string($first) && $first !== '') {
                return $first;
            }
        }

        return '';
    }

    public function welcomeMessage(CoachProgress $progress): string
    {
        $trackName = $this->trackConfig($progress->track)['name'] ?? $progress->track;
        $name = function_exists('ai_assistant_name') ? ai_assistant_name() : 'votre coach';
        $step = $this->currentStep($progress);
        $plan = $this->buildTrackPlan($progress->track);

        if ($progress->status === 'completed') {
            return str_replace(
                ['{track}', '{name}'],
                [$trackName, $name],
                (string) config('coach_prompts.welcome.completed', '')
            );
        }

        if ($step === []) {
            return str_replace(
                ['{track}', '{name}', '{plan}'],
                [$trackName, $name, $plan],
                (string) config('coach_prompts.welcome.empty', '')
            );
        }

        return str_replace(
            ['{track}', '{name}', '{position}', '{total}', '{step}', '{plan}'],
            [
                $trackName,
                $name,
                (string) $this->stepPosition($progress),
                (string) $this->totalSteps($progress->track),
                (string) $step['step_title'],
                $plan,
            ],
            (string) config('coach_prompts.welcome.start', '')
        );
    }

    public function noTrackWelcomeMessage(): string
    {
        $name = function_exists('ai_assistant_name') ? ai_assistant_name() : 'votre coach';

        return str_replace(
            '{name}',
            $name,
            (string) config('coach_prompts.welcome.no_track', '')
        );
    }

    public function advance(CoachProgress $progress): CoachProgress
    {
        if ($progress->status === 'completed') {
            return $progress;
        }

        $step = $this->currentStep($progress);
        if ($step !== [] && ! empty($step['step_id'])) {
            $progress->completed_step_ids = array_values(array_unique(array_merge(
                (array) ($progress->completed_step_ids ?? []),
                [$step['step_id']]
            )));
        }

        $modules = $this->trackConfig($progress->track)['modules'] ?? [];
        $module = $modules[$progress->module_index] ?? null;
        $stepsInModule = is_array($module) ? count($module['steps'] ?? []) : 0;

        if ($stepsInModule === 0) {
            $progress->status = 'completed';
            $progress->save();

            return $progress->fresh();
        }

        if ($progress->step_index + 1 < $stepsInModule) {
            $progress->step_index++;
        } elseif ($progress->module_index + 1 < count($modules)) {
            $progress->module_index++;
            $progress->step_index = 0;
        } else {
            $progress->status = 'completed';
        }

        $progress->save();

        return $progress->fresh();
    }

    public function shouldAdvanceFromUserMessage(string $message): bool
    {
        $text = mb_strtolower(trim($message));
        if ($text === '') {
            return false;
        }

        $noAdvance = [
            'give me a hint',
            'show me a',
            'show an example',
            'can you give me',
            'need a hint',
            'example for this',
            'i\'m not sure',
            'im not sure',
            'break the question',
            'break it down',
        ];

        foreach ($noAdvance as $phrase) {
            if (str_contains($text, $phrase)) {
                return false;
            }
        }

        $phrases = [
            'next step', 'next', 'continue', 'go on', 'move on', 'understood', 'got it',
            'i\'m ready for the next step', 'i am ready for the next step',
            'ready for the next step', 'let\'s move on', 'lets move on',
            'i am ready for this step', 'i\'m ready for this step',
        ];

        foreach ($phrases as $phrase) {
            if ($text === $phrase || str_starts_with($text, $phrase . ' ') || str_ends_with($text, ' ' . $phrase)) {
                return true;
            }
        }

        return false;
    }

    public function replySignalsAdvance(string $reply): bool
    {
        return str_contains($reply, $this->advanceMarker());
    }

    public function stripAdvanceMarker(string $reply): string
    {
        $cleaned = str_replace($this->advanceMarker(), '', $reply);

        return trim(preg_replace("/\n{3,}/", "\n\n", $cleaned) ?? $cleaned);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function snapshot(?CoachProgress $progress): ?array
    {
        if (! $progress) {
            return null;
        }

        $step = $this->currentStep($progress);
        $total = $this->totalSteps($progress->track);
        $position = min($this->stepPosition($progress), max(1, $total));
        $percent = $total > 0
            ? (int) round((count((array) ($progress->completed_step_ids ?? [])) / $total) * 100)
            : 0;

        if ($progress->status === 'completed') {
            $percent = 100;
            $position = $total;
        }

        return [
            'track' => $progress->track,
            'track_name' => $this->trackConfig($progress->track)['name'] ?? $progress->track,
            'status' => $progress->status,
            'module_index' => $progress->module_index,
            'step_index' => $progress->step_index,
            'module_title' => $step['module_title'] ?? null,
            'step_title' => $step['step_title'] ?? null,
            'step_position' => $position,
            'total_steps' => $total,
            'completed_steps' => count((array) ($progress->completed_step_ids ?? [])),
            'percent' => $percent,
            'curriculum' => $this->buildCurriculumOutline($progress->track, $progress),
        ];
    }

    /**
     * Structure du cursus pour l'interface (modules, étapes, statuts).
     *
     * @return array<string, mixed>
     */
    public function buildCurriculumOutline(string $track, ?CoachProgress $progress = null): array
    {
        $config = $this->trackConfig($track);
        $completedIds = $progress ? array_flip((array) ($progress->completed_step_ids ?? [])) : [];
        $isCompleted = $progress && $progress->status === 'completed';
        $currentModuleIndex = $progress ? (int) $progress->module_index : 0;
        $currentStepIndex = $progress ? (int) $progress->step_index : 0;
        $position = 0;
        $modules = [];

        foreach ($config['modules'] ?? [] as $moduleIndex => $module) {
            if (! is_array($module)) {
                continue;
            }

            $steps = [];
            foreach ($module['steps'] ?? [] as $stepIndex => $step) {
                if (! is_array($step)) {
                    continue;
                }

                $position++;
                $stepId = (string) ($step['id'] ?? '');
                $status = 'pending';

                if ($isCompleted || isset($completedIds[$stepId])) {
                    $status = 'completed';
                } elseif ($progress && $progress->status === 'active'
                    && $moduleIndex === $currentModuleIndex
                    && $stepIndex === $currentStepIndex) {
                    $status = 'current';
                }

                $steps[] = [
                    'id' => $stepId,
                    'title' => (string) ($step['title'] ?? ''),
                    'position' => $position,
                    'status' => $status,
                ];
            }

            if ($steps !== []) {
                $completedInModule = count(array_filter($steps, fn (array $s) => $s['status'] === 'completed'));
                $modules[] = [
                    'id' => (string) ($module['id'] ?? ''),
                    'title' => (string) ($module['title'] ?? ''),
                    'steps' => $steps,
                    'completed_count' => $completedInModule,
                    'total_count' => count($steps),
                    'is_current' => $progress && $progress->status === 'active' && $moduleIndex === $currentModuleIndex,
                ];
            }
        }

        return [
            'track' => $track,
            'track_name' => $config['name'] ?? $track,
            'description' => (string) ($config['description'] ?? ''),
            'total_steps' => $this->totalSteps($track),
            'modules' => $modules,
        ];
    }

    public function totalSteps(string $track): int
    {
        $count = 0;
        foreach ($this->trackConfig($track)['modules'] ?? [] as $module) {
            $count += count($module['steps'] ?? []);
        }

        return $count;
    }

    public function stepPosition(CoachProgress $progress): int
    {
        $offset = 0;
        $modules = $this->trackConfig($progress->track)['modules'] ?? [];

        for ($i = 0; $i < $progress->module_index; $i++) {
            $offset += count($modules[$i]['steps'] ?? []);
        }

        return $offset + $progress->step_index + 1;
    }

    private function advanceMarker(): string
    {
        return (string) config('coaching_curricula.advance_marker', '[[COACH_ADVANCE]]');
    }

    /**
     * @return array<string, mixed>
     */
    private function trackConfig(string $track): array
    {
        $this->assertTrackExists($track);

        return (array) config('coaching_curricula.tracks.' . $track, []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function tracksConfig(): array
    {
        return (array) config('coaching_curricula.tracks', []);
    }

    private function assertTrackExists(string $track): void
    {
        if (! array_key_exists($track, $this->tracksConfig())) {
            throw new \InvalidArgumentException('Unknown coaching track: ' . $track);
        }
    }
}
