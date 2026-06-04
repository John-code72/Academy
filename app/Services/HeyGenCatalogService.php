<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HeyGenCatalogService
{
    public function isConfigured(): bool
    {
        return function_exists('heygen_is_configured') && heygen_is_configured();
    }

    /**
     * @return array<int, array{avatar_id: string, avatar_name: string, gender?: string, preview_image_url?: string}>
     */
    public function avatars(): array
    {
        if (!$this->apiKey()) {
            return $this->fallbackAvatars();
        }

        return Cache::remember('heygen.catalog.avatars', 3600, function () {
            try {
                $response = Http::timeout(30)
                    ->withHeaders(['X-Api-Key' => $this->apiKey()])
                    ->get('https://api.heygen.com/v2/avatars');

                if (!$response->successful()) {
                    Log::warning('HeyGen avatars HTTP ' . $response->status());

                    return $this->fallbackAvatars();
                }

                $data = $response->json('data.avatars') ?? $response->json('data') ?? [];

                return collect($data)
                    ->map(fn ($a) => [
                        'avatar_id' => (string) ($a['avatar_id'] ?? ''),
                        'avatar_name' => (string) ($a['avatar_name'] ?? $a['avatar_id'] ?? ''),
                        'gender' => $a['gender'] ?? null,
                        'preview_image_url' => $a['preview_image_url'] ?? $a['preview_url'] ?? null,
                    ])
                    ->filter(fn ($a) => $a['avatar_id'] !== '')
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                Log::warning('HeyGen avatars fetch failed: ' . $e->getMessage());

                return $this->fallbackAvatars();
            }
        });
    }

    /**
     * @return array<int, array{voice_id: string, name: string, language?: string, gender?: string}>
     */
    public function voices(): array
    {
        if (!$this->apiKey()) {
            return $this->fallbackVoices();
        }

        return Cache::remember('heygen.catalog.voices', 3600, function () {
            try {
                $response = Http::timeout(30)
                    ->withHeaders(['X-Api-Key' => $this->apiKey()])
                    ->get('https://api.heygen.com/v2/voices');

                if (!$response->successful()) {
                    Log::warning('HeyGen voices HTTP ' . $response->status());

                    return $this->fallbackVoices();
                }

                $data = $response->json('data.voices') ?? $response->json('data') ?? [];

                return collect($data)
                    ->map(fn ($v) => [
                        'voice_id' => (string) ($v['voice_id'] ?? ''),
                        'name' => (string) ($v['name'] ?? $v['voice_id'] ?? ''),
                        'language' => $v['language'] ?? null,
                        'gender' => $v['gender'] ?? null,
                    ])
                    ->filter(fn ($v) => $v['voice_id'] !== '')
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                Log::warning('HeyGen voices fetch failed: ' . $e->getMessage());

                return $this->fallbackVoices();
            }
        });
    }

    public function clearCache(): void
    {
        Cache::forget('heygen.catalog.avatars');
        Cache::forget('heygen.catalog.voices');
    }

    private function apiKey(): ?string
    {
        $key = config('heygen.api_key');
        if (function_exists('get_settings')) {
            $settingsKey = get_settings('heygen_api_key');
            if (!empty($settingsKey)) {
                $key = $settingsKey;
            }
        }

        return ($key && !str_contains((string) $key, 'xxx')) ? $key : null;
    }

    private function fallbackAvatars(): array
    {
        $id = function_exists('get_settings') ? get_settings('heygen_avatar_id') : null;
        $id = $id ?: config('heygen.avatar_id', 'Daisy-inskirt-20220818');

        return [['avatar_id' => $id, 'avatar_name' => $id . ' (default)', 'gender' => null]];
    }

    private function fallbackVoices(): array
    {
        $id = function_exists('get_settings') ? get_settings('heygen_voice_id') : null;
        $id = $id ?: config('heygen.voice_id');

        return [['voice_id' => $id, 'name' => $id . ' (default)', 'language' => 'English']];
    }
}
