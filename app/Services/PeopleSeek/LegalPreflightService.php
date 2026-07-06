<?php

namespace App\Services\PeopleSeek;

use App\Models\PeopleSeekSourceRegistry;
use Illuminate\Support\Facades\Http;

class LegalPreflightService
{
    public function __construct(
        private readonly PeopleSeekConfigRegistry $registry,
    ) {}

    /**
     * @param  array<string, mixed>  $source
     * @return array{passed: bool, reason: string, robots_txt: ?string}
     */
    public function run(array $source, bool $production = false): array
    {
        $sourceKey = (string) ($source['source_key'] ?? '');
        $legalStatus = (string) ($source['legal_status'] ?? 'dry_run_only');

        if ($legalStatus === 'no_go') {
            return $this->fail($sourceKey, 'Source classified as no_go.');
        }

        if ($production && ! in_array($legalStatus, ['allowed'], true)) {
            return $this->fail($sourceKey, "Production ingest blocked for legal_status={$legalStatus}.");
        }

        if (in_array($legalStatus, ['partnership_required', 'licensed_only'], true)) {
            return $this->fail($sourceKey, "Source requires partnership/license: {$legalStatus}.");
        }

        $robots = $this->fetchRobotsTxt((string) ($source['url'] ?? ''));

        $dbSource = PeopleSeekSourceRegistry::query()->where('source_key', $sourceKey)->first();
        if ($dbSource) {
            $dbSource->update([
                'robots_txt_snapshot' => $robots['body'],
                'robots_checked_at' => now(),
                'last_preflight_at' => now(),
                'last_preflight_result' => $robots['blocked'] ? 'robots_review' : 'passed',
            ]);
        }

        if ($robots['blocked']) {
            return [
                'passed' => false,
                'reason' => 'robots.txt disallows generic crawlers for this host.',
                'robots_txt' => $robots['body'],
            ];
        }

        return [
            'passed' => true,
            'reason' => 'Preflight passed.',
            'robots_txt' => $robots['body'],
        ];
    }

    /**
     * @return array{body: ?string, blocked: bool}
     */
    private function fetchRobotsTxt(string $url): array
    {
        if ($url === '') {
            return ['body' => null, 'blocked' => false];
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return ['body' => null, 'blocked' => false];
        }

        try {
            $response = Http::timeout(15)
                ->withOptions(['verify' => (bool) config('peopleseek_sources.verify_ssl', true)])
                ->withHeaders(['User-Agent' => (string) config('peopleseek_sources.user_agent')])
                ->get('https://' . $host . '/robots.txt');

            if (! $response->successful()) {
                return ['body' => null, 'blocked' => false];
            }

            $body = $response->body();
            $blocked = (bool) preg_match('/^Disallow:\s*\/\s*$/mi', $body)
                && ! preg_match('/^User-agent:\s*Googlebot/im', $body);

            return ['body' => $body, 'blocked' => $blocked];
        } catch (\Throwable) {
            return ['body' => null, 'blocked' => false];
        }
    }

    /**
     * @return array{passed: bool, reason: string, robots_txt: ?string}
     */
    private function fail(string $sourceKey, string $reason): array
    {
        PeopleSeekSourceRegistry::query()
            ->where('source_key', $sourceKey)
            ->update([
                'last_preflight_at' => now(),
                'last_preflight_result' => 'failed',
            ]);

        return ['passed' => false, 'reason' => $reason, 'robots_txt' => null];
    }
}
