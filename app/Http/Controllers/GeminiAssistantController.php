<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeSourceChunk;
use App\Models\KnowledgeSourceDocument;
use App\Services\CoachCurriculumService;
use App\Services\GeminiLiveService;
use App\Services\GeminiVisionChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GeminiAssistantController extends Controller
{
    public function __construct(
        private GeminiVisionChatService $gemini,
        private GeminiLiveService $live,
        private CoachCurriculumService $curriculum,
    ) {}

    public function status(): JsonResponse
    {
        return response()->json([
            'configured' => $this->gemini->isConfigured(),
            'knowledge' => [
                'enabled' => (bool) config('knowledge_sources.enabled', true),
                'sources' => KnowledgeSourceDocument::query()->where('status', 'chunked')->count(),
                'chunks' => KnowledgeSourceChunk::query()->count(),
            ],
            'coaching' => [
                'enabled' => true,
                'tracks' => $this->curriculum->listTracks(),
            ],
        ]);
    }

    public function index()
    {
        if (! $this->gemini->isConfigured()) {
            return redirect()->route('dashboard')->with('error', get_phrase('AI assistant API key is not configured.'));
        }

        $coachBootstrap = $this->curriculum->clientBootstrap((int) Auth::id());

        return view('frontend.default.student.gemini_assistant.index', [
            'coachBootstrap' => $coachBootstrap,
            'coachingTracks' => $coachBootstrap['tracks'],
            'defaultTrack' => $coachBootstrap['default_track'],
            'launchBriefing' => $coachBootstrap['launch_briefing'],
            'openingMessage' => $coachBootstrap['opening_message'],
        ]);
    }

    public function coachProgress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'track' => ['required', 'string', 'max:64', Rule::in(array_keys(config('coaching_curricula.tracks', [])))],
        ]);

        $progress = $this->curriculum->getProgress((int) Auth::id(), $validated['track']);

        return response()->json([
            'progress' => $this->curriculum->snapshot($progress),
            'curriculum' => $this->curriculum->buildCurriculumOutline($validated['track'], $progress),
        ]);
    }

    public function coachStart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'track' => ['required', 'string', 'max:64', Rule::in(array_keys(config('coaching_curricula.tracks', [])))],
            'restart' => 'nullable|boolean',
        ]);

        $progress = $this->curriculum->startTrack(
            (int) Auth::id(),
            $validated['track'],
            (bool) ($validated['restart'] ?? false),
        );

        return response()->json($this->curriculum->enrichCoachResponse([
            'progress' => $this->curriculum->snapshot($progress),
            'welcome' => $this->curriculum->welcomeMessage($progress),
            'kickoff' => true,
            'kickoff_mode' => 'start',
        ], $progress));
    }

    public function chat(Request $request): JsonResponse
    {
        if (! $this->gemini->isConfigured()) {
            return response()->json([
                'error' => get_phrase('AI assistant API key is not configured.'),
            ], 503);
        }

        $validated = $request->validate([
            'message'        => 'required_without:kickoff|string|max:4000',
            'kickoff'        => 'nullable|boolean',
            'kickoff_mode'   => 'nullable|in:start,resume',
            'history'        => 'nullable|array|max:20',
            'history.*.role' => 'required|in:user,model',
            'history.*.text' => 'required|string|max:4000',
            'image'          => 'nullable|array',
            'image.mimeType' => 'required_with:image|in:image/jpeg,image/png,image/webp',
            'image.data'     => 'required_with:image|string|max:6000000',
            'live'           => 'nullable|boolean',
            'source'         => 'nullable|in:screen,camera',
            'department'     => 'nullable|string|max:64',
            'track'          => ['nullable', 'string', 'max:64', Rule::in(array_keys(config('coaching_curricula.tracks', [])))],
        ]);

        $isKickoff = (bool) ($validated['kickoff'] ?? false);

        $image = null;
        if (! empty($validated['image']['data'])) {
            $image = [
                'mimeType' => $validated['image']['mimeType'],
                'data'     => $validated['image']['data'],
            ];
        }

        $userId = (int) Auth::id();
        $track = $validated['track'] ?? $this->curriculum->resolveActiveTrackForUser($userId);
        $department = $validated['department'] ?? $track;
        $curriculumPrompt = null;
        $coachProgress = null;

        if ($track) {
            $coachProgress = $this->curriculum->resolveProgress($userId, $track);
            if ($coachProgress) {
                $curriculumPrompt = $this->curriculum->buildCurriculumPrompt($coachProgress);
            }
        }

        if ($isKickoff && $coachProgress) {
            $kickoffMode = (string) ($validated['kickoff_mode'] ?? 'start');

            return response()->json($this->curriculum->enrichCoachResponse([
                'reply' => $this->curriculum->buildStepLessonMessage($coachProgress, $kickoffMode),
                'progress' => $this->curriculum->snapshot($coachProgress),
                'advanced' => false,
                'server_coach' => true,
            ], $coachProgress));
        }

        $history = $validated['history'] ?? [];
        $userMessageCount = 0;
        foreach ($history as $turn) {
            if (($turn['role'] ?? '') === 'user') {
                $userMessageCount++;
            }
        }

        if ($coachProgress && ! $image && $userMessageCount === 0 && ! $this->curriculum->historyContainsCoachPlan($history)) {
            return response()->json($this->curriculum->enrichCoachResponse([
                'reply' => $this->curriculum->buildStepLessonMessage($coachProgress, 'start'),
                'progress' => $this->curriculum->snapshot($coachProgress),
                'advanced' => false,
                'server_coach' => true,
            ], $coachProgress));
        }

        $message = (string) ($validated['message'] ?? '');

        $result = $this->gemini->chat(
            $message,
            $validated['history'] ?? [],
            $image,
            (bool) ($validated['live'] ?? false),
            $validated['source'] ?? null,
            $department,
            $curriculumPrompt,
        );

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'error' => $result['error'] ?? get_phrase('Unable to get a response. Please try again.'),
            ], 502);
        }

        $reply = $this->curriculum->enforceCoachReply((string) ($result['reply'] ?? ''), $coachProgress);
        $advanced = false;

        if ($coachProgress && $coachProgress->status === 'active') {
            $shouldAdvance = ! $isKickoff && (
                $this->curriculum->shouldAdvanceFromUserMessage((string) ($validated['message'] ?? ''))
                || $this->curriculum->replySignalsAdvance($reply)
            );

            $reply = $this->curriculum->stripAdvanceMarker($reply);

            if ($shouldAdvance) {
                $coachProgress = $this->curriculum->advance($coachProgress);
                $advanced = true;

                if ($coachProgress->status === 'active') {
                    $reply = trim($reply) . "\n\n" . $this->curriculum->buildAdvanceTransitionMessage($coachProgress);
                } else {
                    $reply .= "\n\n---\n" . get_phrase('Path complete. Great work!');
                }
            } else {
                $reply = $this->curriculum->ensureInteractiveReply($reply, $coachProgress);
            }
        } else {
            $reply = $this->curriculum->stripAdvanceMarker($reply);
        }

        return response()->json($this->curriculum->enrichCoachResponse([
            'reply' => trim($reply),
            'progress' => $this->curriculum->snapshot($coachProgress),
            'advanced' => $advanced,
        ], $coachProgress));
    }

    public function liveToken(Request $request): JsonResponse
    {
        if (! $this->live->isConfigured()) {
            return response()->json([
                'error' => get_phrase('AI assistant API key is not configured.'),
            ], 503);
        }

        $validated = $request->validate([
            'topic' => 'nullable|string|max:500',
            'track' => ['nullable', 'string', 'max:64', Rule::in(array_keys(config('coaching_curricula.tracks', [])))],
        ]);

        $userId = (int) Auth::id();
        $track = $validated['track'] ?? $this->curriculum->resolveActiveTrackForUser($userId);
        $curriculumPrompt = null;
        $progress = null;

        if ($track) {
            $progress = $this->curriculum->resolveProgress($userId, $track);
            if ($progress) {
                $curriculumPrompt = $this->curriculum->buildCurriculumPrompt($progress);
            }
        }

        $result = $this->live->createSessionToken(
            $validated['topic'] ?? null,
            $curriculumPrompt,
            $track,
            $progress ? $this->curriculum->buildStepLessonMessage($progress, 'resume') : null,
        );

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'error' => $result['error'] ?? get_phrase('Could not start live voice session.'),
            ], 502);
        }

        return response()->json([
            'token'             => $result['token'],
            'model'             => $result['model'],
            'voice'             => $result['voice'],
            'systemInstruction' => $result['systemInstruction'] ?? '',
            'openingMessage'    => $progress ? $this->curriculum->buildStepLessonMessage($progress, 'resume') : null,
            'assistantName'     => ai_assistant_name(),
            'progress'          => $progress ? $this->curriculum->snapshot($progress) : null,
        ]);
    }
}
