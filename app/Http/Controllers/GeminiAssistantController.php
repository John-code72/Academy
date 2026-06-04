<?php

namespace App\Http\Controllers;

use App\Services\GeminiLiveService;
use App\Services\GeminiVisionChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeminiAssistantController extends Controller
{
    public function __construct(
        private GeminiVisionChatService $gemini,
        private GeminiLiveService $live
    ) {}

    public function status(): JsonResponse
    {
        return response()->json([
            'configured' => $this->gemini->isConfigured(),
        ]);
    }

    public function index()
    {
        if (! $this->gemini->isConfigured()) {
            return redirect()->route('dashboard')->with('error', get_phrase('AI assistant API key is not configured.'));
        }

        return view('frontend.default.student.gemini_assistant.index');
    }

    public function chat(Request $request): JsonResponse
    {
        if (! $this->gemini->isConfigured()) {
            return response()->json([
                'error' => get_phrase('AI assistant API key is not configured.'),
            ], 503);
        }

        $validated = $request->validate([
            'message'        => 'required|string|max:4000',
            'history'        => 'nullable|array|max:20',
            'history.*.role' => 'required|in:user,model',
            'history.*.text' => 'required|string|max:4000',
            'image'          => 'nullable|array',
            'image.mimeType' => 'required_with:image|in:image/jpeg,image/png,image/webp',
            'image.data'     => 'required_with:image|string|max:6000000',
            'live'           => 'nullable|boolean',
            'source'         => 'nullable|in:screen,camera',
        ]);

        $image = null;
        if (! empty($validated['image']['data'])) {
            $image = [
                'mimeType' => $validated['image']['mimeType'],
                'data'     => $validated['image']['data'],
            ];
        }

        $result = $this->gemini->chat(
            $validated['message'],
            $validated['history'] ?? [],
            $image,
            (bool) ($validated['live'] ?? false),
            $validated['source'] ?? null
        );

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'error' => $result['error'] ?? get_phrase('Unable to get a response. Please try again.'),
            ], 502);
        }

        return response()->json([
            'reply' => $result['reply'],
        ]);
    }

    public function liveToken(): JsonResponse
    {
        if (! $this->live->isConfigured()) {
            return response()->json([
                'error' => get_phrase('AI assistant API key is not configured.'),
            ], 503);
        }

        $result = $this->live->createSessionToken();

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
            'assistantName'     => ai_assistant_name(),
        ]);
    }
}
