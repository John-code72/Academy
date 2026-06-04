<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiVisionChatService
{
    public function isConfigured(): bool
    {
        return (bool) $this->apiKey();
    }

    public function apiKey(): ?string
    {
        $key = config('gemini.api_key');

        if (function_exists('get_settings')) {
            $settingsKey = get_settings('gemini_api_key');
            if (! empty($settingsKey)) {
                $key = $settingsKey;
            }
        }

        if (! $key || str_contains($key, 'xxx')) {
            return null;
        }

        return $key;
    }

    /**
     * @param  array<int, array{role: string, text: string}>  $history
     * @param  array{mimeType: string, data: string}|null  $image
     * @return array{ok: bool, reply?: string, error?: string}
     */
    public function chat(string $message, array $history = [], ?array $image = null, bool $isLive = false, ?string $source = null): array
    {
        $apiKey = $this->apiKey();
        if (! $apiKey) {
            return ['ok' => false, 'error' => 'AI assistant API key is not configured.'];
        }

        $contents = [];

        foreach ($history as $turn) {
            $role = ($turn['role'] ?? '') === 'model' ? 'model' : 'user';
            $text = trim((string) ($turn['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            $contents[] = [
                'role'  => $role,
                'parts' => [['text' => $text]],
            ];
        }

        $liveNote = $isLive
            ? ($source === 'screen'
                ? '[Live frame from the user\'s screen share — describe exactly what is visible on their screen now and help with their question.] '
                : '[Live frame from the user\'s camera or screen share — describe what you see now and help with their question.] ')
            : '';

        $userParts = [['text' => $liveNote . $message]];

        if ($image && ! empty($image['data']) && ! empty($image['mimeType'])) {
            $userParts[] = [
                'inlineData' => [
                    'mimeType' => $image['mimeType'],
                    'data'     => $image['data'],
                ],
            ];
        }

        $contents[] = [
            'role'  => 'user',
            'parts' => $userParts,
        ];

        $model = config('gemini.vision_model', 'gemini-2.5-flash');

        $name = ai_assistant_name();

        $systemText = 'You are ' . $name . ', a helpful learning assistant for an online academy platform. '
            . 'Your name is ' . $name . '. '
            . 'Users may share camera photos or screen captures so you can see what they see. '
            . 'Help them understand course content, navigate the platform, troubleshoot issues, '
            . 'and answer questions about what appears on screen. Be concise, practical, and supportive. '
            . 'Respond in the same language the user writes in.';

        if ($isLive && $source === 'screen') {
            $systemText .= ' The user is sharing their SCREEN in real time. Focus on UI elements, buttons, menus, errors, and course content visible on screen. Give step-by-step guidance.';
        } elseif ($isLive) {
            $systemText .= ' The user is sharing a LIVE feed: each image is a real-time frame from their camera or screen. Analyze the current view and give immediate, actionable guidance.';
        }

        $response = Http::timeout(120)
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->post($this->endpoint($model), [
                'systemInstruction' => [
                    'parts' => [['text' => $systemText]],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.4,
                ],
            ]);

        if (! $response->successful()) {
            $apiMessage = (string) ($response->json('error.message') ?? '');

            Log::warning('Gemini vision chat failed', [
                'model'  => $model,
                'status' => $response->status(),
                'body'   => Str::limit($response->body(), 500),
            ]);

            return [
                'ok'    => false,
                'error' => $apiMessage !== '' ? $apiMessage : 'Assistant request failed (HTTP ' . $response->status() . ').',
            ];
        }

        $text = $this->extractReplyText($response->json());

        if ($text === '') {
            $blockReason = (string) ($response->json('promptFeedback.blockReason') ?? '');

            return [
                'ok'    => false,
                'error' => $blockReason !== ''
                    ? 'Request blocked: ' . $blockReason
                    : 'The assistant returned an empty response.',
            ];
        }

        return ['ok' => true, 'reply' => $text];
    }

    private function extractReplyText(?array $payload): string
    {
        if (! is_array($payload)) {
            return '';
        }

        $parts = $payload['candidates'][0]['content']['parts'] ?? [];
        $chunks = [];

        foreach ($parts as $part) {
            if (! empty($part['text'])) {
                $chunks[] = trim((string) $part['text']);
            }
        }

        return trim(implode("\n", $chunks));
    }

    private function endpoint(string $model): string
    {
        return 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent';
    }
}
