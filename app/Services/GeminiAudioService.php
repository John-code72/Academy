<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeminiAudioService
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
            if (!empty($settingsKey)) {
                $key = $settingsKey;
            }
        }

        if (!$key || str_contains($key, 'xxx')) {
            return null;
        }

        return $key;
    }

    /**
     * Generate lesson narration audio; returns public storage URL or null.
     */
    public function synthesizeSpeech(string $script, string $language = 'en'): ?string
    {
        $apiKey = $this->apiKey();
        if (!$apiKey) {
            return null;
        }

        $model = config('gemini.tts_model');
        $voice = $this->voiceForLanguage($language);
        $prompt = $this->ttsPrompt($script, $language);

        $response = Http::timeout(120)
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->post($this->endpoint($model), [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'responseModalities' => ['AUDIO'],
                    'speechConfig' => [
                        'voiceConfig' => [
                            'prebuiltVoiceConfig' => [
                                'voiceName' => $voice,
                            ],
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            Log::warning('Gemini TTS failed', [
                'status' => $response->status(),
                'body'   => Str::limit($response->body(), 500),
            ]);

            return null;
        }

        $inline = $response->json('candidates.0.content.parts.0.inlineData');
        $data = $inline['data'] ?? null;

        if (!$data) {
            Log::warning('Gemini TTS: no inline audio in response');

            return null;
        }

        $mime = strtolower((string) ($inline['mimeType'] ?? 'audio/L16'));
        $binary = base64_decode($data, true);

        if ($binary === false || $binary === '') {
            return null;
        }

        $binary = $this->normalizeAudioBinary($binary, $mime);
        $filename = 'personalized_audio/' . Str::uuid() . '.wav';
        Storage::disk('public')->put($filename, $binary);

        return $this->publicStorageUrl($filename);
    }

    /**
     * Transcribe a local audio/video recording file.
     */
    public function transcribeFile(string $absolutePath): ?string
    {
        $apiKey = $this->apiKey();
        if (!$apiKey || !is_readable($absolutePath)) {
            return null;
        }

        $size = filesize($absolutePath);
        if ($size === false || $size > 18 * 1024 * 1024) {
            Log::warning('Gemini transcription: file too large or unreadable', [
                'path' => $absolutePath,
                'size' => $size,
            ]);

            return null;
        }

        $model = config('gemini.audio_model');
        $mime = $this->mimeFromPath($absolutePath);
        $payload = base64_encode((string) file_get_contents($absolutePath));

        $response = Http::timeout(180)
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->post($this->endpoint($model), [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'inlineData' => [
                                    'mimeType' => $mime,
                                    'data'     => $payload,
                                ],
                            ],
                            [
                                'text' => 'Transcribe this recording verbatim. Return only the transcript text, no commentary.',
                            ],
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            Log::warning('Gemini transcription failed', [
                'status' => $response->status(),
                'body'   => Str::limit($response->body(), 500),
            ]);

            return null;
        }

        $text = trim((string) ($response->json('candidates.0.content.parts.0.text') ?? ''));

        return $text !== '' ? $text : null;
    }

    /**
     * Ask Gemini to return strict JSON (e.g. speaking scores).
     */
    public function generateJson(string $userPrompt, string $systemInstruction = 'Return strict JSON only.'): ?array
    {
        $apiKey = $this->apiKey();
        if (!$apiKey) {
            return null;
        }

        $model = config('gemini.audio_model');

        $response = Http::timeout(120)
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->post($this->endpoint($model), [
                'systemInstruction' => [
                    'parts' => [['text' => $systemInstruction]],
                ],
                'contents' => [
                    ['parts' => [['text' => $userPrompt]]],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature'      => 0.2,
                ],
            ]);

        if (!$response->successful()) {
            Log::warning('Gemini JSON generation failed', [
                'status' => $response->status(),
                'body'   => Str::limit($response->body(), 500),
            ]);

            return null;
        }

        $raw = trim((string) ($response->json('candidates.0.content.parts.0.text') ?? ''));
        if ($raw === '') {
            return null;
        }

        $parsed = json_decode($raw, true);

        return is_array($parsed) ? $parsed : ['raw' => $raw];
    }

    private function endpoint(string $model): string
    {
        return 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent';
    }

    private function publicStorageUrl(string $filename): string
    {
        return '/storage/' . ltrim(str_replace('\\', '/', $filename), '/');
    }

    /**
     * Gemini TTS returns raw PCM (audio/L16); browsers need a proper WAV container.
     */
    private function normalizeAudioBinary(string $binary, string $mime): string
    {
        if (str_starts_with($binary, 'RIFF')) {
            return $binary;
        }

        if (str_contains($mime, 'mpeg') || str_contains($mime, 'mp3')) {
            return $binary;
        }

        $sampleRate = config('gemini.tts_sample_rate', 24000);

        return $this->pcmToWav($binary, $sampleRate);
    }

    private function pcmToWav(string $pcm, int $sampleRate, int $channels = 1, int $bitsPerSample = 16): string
    {
        $dataSize = strlen($pcm);
        $blockAlign = (int) ($channels * ($bitsPerSample / 8));
        $byteRate = $sampleRate * $blockAlign;

        $header = pack(
            'a4Va4a4VvvVVvva4V',
            'RIFF',
            36 + $dataSize,
            'WAVE',
            'fmt ',
            16,
            1,
            $channels,
            $sampleRate,
            $byteRate,
            $blockAlign,
            $bitsPerSample,
            'data',
            $dataSize
        );

        return $header . $pcm;
    }

    private function ttsPrompt(string $script, string $language): string
    {
        $lang = trim($language) !== '' ? $language : 'English';
        $text = Str::limit(trim($script), 4000, '');

        return "Say the following lesson narration clearly and naturally in {$lang}. "
            . "Use a calm, professional teaching tone:\n\n{$text}";
    }

    private function voiceForLanguage(string $language): string
    {
        $lang = strtolower($language);
        if (str_contains($lang, 'french') || str_contains($lang, 'français') || $lang === 'fr') {
            return 'Aoede';
        }
        if (str_contains($lang, 'spanish') || str_contains($lang, 'español') || $lang === 'es') {
            return 'Aoede';
        }

        return 'Kore';
    }

    private function mimeFromPath(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'webm'          => 'video/webm',
            'mp4', 'm4v'    => 'video/mp4',
            'mov'           => 'video/quicktime',
            'mp3'           => 'audio/mpeg',
            'wav'           => 'audio/wav',
            'ogg'           => 'audio/ogg',
            default         => 'application/octet-stream',
        };
    }
}
