<?php

namespace App\Services\PeopleSeek;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PeopleSeekHttpClient
{
    public function __construct(
        private readonly PeopleSeekConfigRegistry $registry,
    ) {}

    public function get(string $url, array $headers = []): string
    {
        $this->assertAllowedUrl($url);

        $response = Http::timeout((int) config('peopleseek_sources.timeout', 45))
            ->withOptions(['verify' => (bool) config('peopleseek_sources.verify_ssl', true)])
            ->withHeaders(array_merge([
                'User-Agent' => (string) config('peopleseek_sources.user_agent'),
                'Accept' => 'text/html,application/xhtml+xml,application/pdf,*/*',
            ], $headers))
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("HTTP {$response->status()} for {$url}");
        }

        return $response->body();
    }

    public function storeRaw(string $sourceKey, string $extension, string $contents): string
    {
        $disk = (string) config('peopleseek_sources.storage_disk', 'local');
        $base = trim((string) config('peopleseek_sources.storage_path', 'peopleseek-sources'), '/');
        $path = $base . '/' . $sourceKey . '.' . $extension;

        Storage::disk($disk)->put($path, $contents);

        return $path;
    }

    private function assertAllowedUrl(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            throw new \RuntimeException('Invalid URL: ' . $url);
        }

        $allowed = $this->registry->allowedHosts();
        if (! in_array(strtolower($host), $allowed, true)) {
            throw new \RuntimeException('URL host not in PeopleSeek allowlist: ' . $host);
        }
    }
}
