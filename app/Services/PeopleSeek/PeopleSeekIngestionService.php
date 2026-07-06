<?php

namespace App\Services\PeopleSeek;

use App\Models\PeopleSeekSourceRegistry;
use App\Models\StagedInterpreterProfile;
use App\Services\PeopleSeek\Adapters\FederalFcciPrrPdfAdapter;
use App\Services\PeopleSeek\Adapters\MnCourtRosterAdapter;
use App\Services\PeopleSeek\Contracts\InterpreterSourceAdapter;
use Illuminate\Support\Facades\Log;

class PeopleSeekIngestionService
{
    public function __construct(
        private readonly PeopleSeekConfigRegistry $registry,
        private readonly LegalPreflightService $preflight,
        private readonly InterpreterProfileNormalizer $normalizer,
        private readonly MnCourtRosterAdapter $mnCourtRosterAdapter,
        private readonly FederalFcciPrrPdfAdapter $federalFcciPrrPdfAdapter,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     * @return array{processed: int, skipped: int, failed: int, details: array<int, array<string, mixed>>}
     */
    public function ingest(?string $sourceKey = null, array $options = []): array
    {
        if (! $this->registry->isEnabled()) {
            throw new \RuntimeException('PeopleSeek ingestion is disabled. Set PEOPLESEEK_INGEST_ENABLED=true.');
        }

        $dryRun = $options['dry_run'] ?? $this->registry->isDryRun();
        $production = ! $dryRun && config('peopleseek_sources.mode') === 'production';
        $keys = $sourceKey ? [$sourceKey] : $this->registry->slugs();

        $summary = ['processed' => 0, 'skipped' => 0, 'failed' => 0, 'details' => []];

        foreach ($keys as $key) {
            $source = $this->registry->get($key);
            if (! $source) {
                $summary['failed']++;
                $summary['details'][] = ['source_key' => $key, 'status' => 'failed', 'message' => 'Unknown source.'];

                continue;
            }

            try {
                $preflight = $this->preflight->run($source, $production);
                if (! $preflight['passed']) {
                    $summary['failed']++;
                    $summary['details'][] = [
                        'source_key' => $key,
                        'status' => 'failed',
                        'message' => 'Legal preflight failed: ' . $preflight['reason'],
                    ];

                    continue;
                }

                $adapter = $this->resolveAdapter((string) ($source['adapter'] ?? ''));
                $rawProfiles = $adapter->fetchProfiles($source, $options);
                $stored = 0;
                $skipped = 0;

                foreach ($rawProfiles as $rawProfile) {
                    $profile = $this->normalizer->normalize($rawProfile, $source);
                    $hash = $this->normalizer->contentHash($profile);

                    $exists = StagedInterpreterProfile::query()
                        ->where('source_key', $key)
                        ->where('content_hash', $hash)
                        ->exists();

                    if ($exists) {
                        $skipped++;

                        continue;
                    }

                    StagedInterpreterProfile::query()->create([
                        'source_key' => $key,
                        'source_record_id' => $profile['source_record_id'] ?? null,
                        'content_hash' => $hash,
                        'profile' => $profile,
                        'dry_run' => $dryRun,
                        'legal_status' => $source['legal_status'] ?? null,
                        'provenance' => [
                            'fetched_at' => now()->toIso8601String(),
                            'source_url' => $source['url'] ?? null,
                            'access_method' => $source['access_method'] ?? null,
                            'preflight' => $preflight['reason'],
                            'options' => $options,
                        ],
                        'suppressed' => false,
                        'fetched_at' => now(),
                    ]);

                    $stored++;
                }

                $summary[$stored > 0 ? 'processed' : 'skipped']++;
                $summary['details'][] = [
                    'source_key' => $key,
                    'status' => $stored > 0 ? 'processed' : 'skipped',
                    'stored' => $stored,
                    'skipped_duplicates' => $skipped,
                    'fetched' => count($rawProfiles),
                    'dry_run' => $dryRun,
                ];
            } catch (\Throwable $e) {
                $summary['failed']++;
                $summary['details'][] = [
                    'source_key' => $key,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ];

                Log::warning('PeopleSeek ingestion failed', [
                    'source_key' => $key,
                    'error' => $e->getMessage(),
                ]);
            }

            usleep((int) ($source['rate_limit_ms'] ?? config('peopleseek_sources.rate_limit_ms', 3000)) * 1000);
        }

        return $summary;
    }

    private function resolveAdapter(string $adapterKey): InterpreterSourceAdapter
    {
        return match ($adapterKey) {
            'mn_court_roster' => $this->mnCourtRosterAdapter,
            'federal_fcci_prr_pdf' => $this->federalFcciPrrPdfAdapter,
            default => throw new \InvalidArgumentException('Unknown adapter: ' . $adapterKey),
        };
    }

    public function syncRegistryFromConfig(): int
    {
        $count = 0;

        foreach ($this->registry->all() as $sourceKey => $source) {
            PeopleSeekSourceRegistry::query()->updateOrCreate(
                ['source_key' => $sourceKey],
                [
                    'source_name' => (string) ($source['name'] ?? $sourceKey),
                    'source_url' => $source['url'] ?? null,
                    'country_region' => $source['country_region'] ?? null,
                    'source_category' => $source['source_category'] ?? null,
                    'adapter' => (string) ($source['adapter'] ?? ''),
                    'access_method' => (string) ($source['access_method'] ?? 'public_directory'),
                    'legal_status' => (string) ($source['legal_status'] ?? 'dry_run_only'),
                    'compliance_risk' => (string) ($source['compliance_risk'] ?? 'medium'),
                    'rate_limit_ms' => (int) ($source['rate_limit_ms'] ?? config('peopleseek_sources.rate_limit_ms', 3000)),
                    'terms_notes' => $source['terms_notes'] ?? null,
                    'priority_tier' => (string) ($source['priority_tier'] ?? 'Tier 2'),
                    'is_active' => true,
                    'meta' => [
                        'target_languages' => $source['target_languages'] ?? [],
                    ],
                ]
            );
            $count++;
        }

        return $count;
    }
}
