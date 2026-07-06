<?php

namespace App\Console\Commands;

use App\Services\KnowledgeIngestion\KnowledgeIngestionService;
use App\Services\KnowledgeIngestion\KnowledgeSourceRegistry;
use Illuminate\Console\Command;

class IngestKnowledgeSources extends Command
{
    protected $signature = 'knowledge:ingest
                            {source? : Optional source slug from config/knowledge_sources.php}
                            {--force : Re-fetch even if content hash is unchanged}
                            {--list : List available source slugs}
                            {--repair : Fix documents that have chunks but wrong status}';

    protected $description = 'Fetch approved external knowledge sources, extract text, and chunk for RAG';

    public function handle(
        KnowledgeIngestionService $ingestion,
        KnowledgeSourceRegistry $registry,
    ): int {
        if ($this->option('repair')) {
            $fixed = $ingestion->repairStatuses();
            $this->info("Repaired {$fixed} document(s) with existing chunks.");

            return self::SUCCESS;
        }

        if ($this->option('list')) {
            $rows = [];
            foreach ($registry->all() as $slug => $source) {
                $rows[] = [
                    $slug,
                    $source['type'] ?? '-',
                    $source['priority'] ?? 'medium',
                    implode(', ', $source['departments'] ?? []),
                ];
            }

            $this->table(['Slug', 'Type', 'Priority', 'Departments'], $rows);

            if (! $registry->apiSourcesEnabled()) {
                $apiSlugs = $registry->apiSlugs();
                if ($apiSlugs !== []) {
                    $this->newLine();
                    $this->comment('API sources disabled (KNOWLEDGE_API_SOURCES_ENABLED=false):');
                    $this->line(implode(', ', $apiSlugs));
                }
            }

            return self::SUCCESS;
        }

        $source = $this->argument('source');
        if ($source && ! $registry->get($source)) {
            $this->error('Unknown source slug: ' . $source);
            $this->line('Run: php artisan knowledge:ingest --list');

            return self::FAILURE;
        }

        $this->info($source ? 'Ingesting source: ' . $source : 'Ingesting all approved sources...');

        $result = $ingestion->ingest($source, (bool) $this->option('force'));

        foreach ($result['details'] as $detail) {
            $status = strtoupper((string) ($detail['status'] ?? 'unknown'));
            $slug = (string) ($detail['slug'] ?? '-');
            $message = (string) ($detail['message'] ?? '');
            $extra = isset($detail['chunks']) ? ' chunks=' . $detail['chunks'] : '';

            $this->line("[{$status}] {$slug} - {$message}{$extra}");
        }

        $this->newLine();
        $this->info(sprintf(
            'Done. processed=%d skipped=%d failed=%d',
            $result['processed'],
            $result['skipped'],
            $result['failed']
        ));

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
