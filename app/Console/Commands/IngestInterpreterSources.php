<?php

namespace App\Console\Commands;

use App\Models\StagedInterpreterProfile;
use App\Services\PeopleSeek\LegalPreflightService;
use App\Services\PeopleSeek\PeopleSeekConfigRegistry;
use App\Services\PeopleSeek\PeopleSeekIngestionService;
use Illuminate\Console\Command;

class IngestInterpreterSources extends Command
{
    protected $signature = 'peopleseek:ingest
                            {source? : Source key from config/peopleseek_sources.php}
                            {--dry-run : Stage profiles without production merge (default)}
                            {--production : Allow production mode if PEOPLESEEK_INGEST_MODE=production}
                            {--language= : Language slug filter, e.g. somali, haitian_creole, karen}
                            {--limit=10 : Max profiles per source run}
                            {--preflight : Run legal preflight only}
                            {--seed-registry : Sync config sources into peopleseek_source_registry}
                            {--list : List configured sources}
                            {--show : Show staged profiles for a source}';

    protected $description = 'Dry-run ingest of public court interpreter rosters into staged_interpreter_profiles';

    public function handle(
        PeopleSeekIngestionService $ingestion,
        PeopleSeekConfigRegistry $registry,
        LegalPreflightService $preflight,
    ): int {
        if ($this->option('seed-registry')) {
            $count = $ingestion->syncRegistryFromConfig();
            $this->info("Synced {$count} source(s) into peopleseek_source_registry.");

            return self::SUCCESS;
        }

        if ($this->option('list')) {
            $rows = [];
            foreach ($registry->all() as $key => $source) {
                $rows[] = [
                    $key,
                    $source['adapter'] ?? '-',
                    $source['legal_status'] ?? '-',
                    $source['priority_tier'] ?? '-',
                ];
            }
            $this->table(['Source Key', 'Adapter', 'Legal Status', 'Tier'], $rows);

            return self::SUCCESS;
        }

        $sourceKey = $this->argument('source');

        if ($this->option('show')) {
            if (! $sourceKey) {
                $this->error('Provide a source key to --show.');

                return self::FAILURE;
            }

            $profiles = StagedInterpreterProfile::query()
                ->where('source_key', $sourceKey)
                ->latest('id')
                ->limit((int) $this->option('limit'))
                ->get();

            foreach ($profiles as $row) {
                $p = $row->profile;
                $this->line(sprintf(
                    '- %s | %s | %s | %s | %s',
                    $p['full_name'] ?? '—',
                    implode(', ', $p['languages'] ?? []),
                    $p['credential_status'] ?? '—',
                    trim(($p['location']['city'] ?? '') . ', ' . ($p['location']['region'] ?? '')),
                    $p['email'] ?? $p['phone'] ?? '—'
                ));
            }

            return self::SUCCESS;
        }

        if ($this->option('preflight')) {
            if (! $sourceKey) {
                $this->error('Provide a source key for --preflight.');

                return self::FAILURE;
            }

            $source = $registry->get($sourceKey);
            if (! $source) {
                $this->error('Unknown source: ' . $sourceKey);

                return self::FAILURE;
            }

            $result = $preflight->run($source, $this->option('production'));
            $this->line($result['passed'] ? '<info>PASSED</info>' : '<error>BLOCKED</error>');
            $this->line($result['reason']);

            return $result['passed'] ? self::SUCCESS : self::FAILURE;
        }

        if ($sourceKey && ! $registry->get($sourceKey)) {
            $this->error('Unknown source: ' . $sourceKey);
            $this->line('Run: php artisan peopleseek:ingest --list');

            return self::FAILURE;
        }

        $options = [
            'limit' => (int) $this->option('limit'),
            'dry_run' => ! $this->option('production'),
        ];

        if ($language = $this->option('language')) {
            $options['language'] = $language;
        }

        $ingestion->syncRegistryFromConfig();
        $summary = $ingestion->ingest($sourceKey, $options);

        foreach ($summary['details'] as $detail) {
            $status = strtoupper((string) ($detail['status'] ?? ''));
            $message = $detail['message'] ?? null;
            $extra = isset($detail['stored'])
                ? "stored={$detail['stored']} fetched={$detail['fetched']} dry_run=" . ($detail['dry_run'] ? 'yes' : 'no')
                : null;

            $this->line(trim("{$detail['source_key']}: {$status}" . ($extra ? " ({$extra})" : '') . ($message ? " — {$message}" : '')));
        }

        $this->newLine();
        $this->info("Processed: {$summary['processed']} | Skipped: {$summary['skipped']} | Failed: {$summary['failed']}");

        if ($sourceKey && $summary['processed'] > 0) {
            $this->newLine();
            $this->comment('Staged profiles:');
            $profiles = StagedInterpreterProfile::query()
                ->where('source_key', $sourceKey)
                ->latest('id')
                ->limit((int) $this->option('limit'))
                ->get();

            foreach ($profiles as $row) {
                $p = $row->profile;
                $this->line(sprintf(
                    '- %s | %s | %s | %s | %s',
                    $p['full_name'] ?? '—',
                    implode(', ', $p['languages'] ?? []),
                    $p['credential_status'] ?? '—',
                    trim(($p['location']['city'] ?? '') . ', ' . ($p['location']['region'] ?? '')),
                    $p['email'] ?? $p['phone'] ?? '—'
                ));
            }
        }

        return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
