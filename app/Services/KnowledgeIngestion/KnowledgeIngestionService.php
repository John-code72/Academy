<?php

namespace App\Services\KnowledgeIngestion;

use App\Models\KnowledgeSourceChunk;
use App\Models\KnowledgeSourceDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KnowledgeIngestionService
{
    public function __construct(
        private readonly KnowledgeSourceRegistry $registry,
        private readonly KnowledgeSourceFetcher $fetcher,
        private readonly KnowledgeDocumentChunker $chunker,
    ) {}

    /**
     * @return array{processed: int, skipped: int, failed: int, details: array<int, array<string, mixed>>}
     */
    public function ingest(?string $slug = null, bool $force = false): array
    {
        if (! $this->registry->isEnabled()) {
            throw new \RuntimeException('Knowledge ingestion is disabled. Set KNOWLEDGE_INGEST_ENABLED=true.');
        }

        $slugs = $slug ? [$slug] : $this->registry->slugs();
        $summary = ['processed' => 0, 'skipped' => 0, 'failed' => 0, 'details' => []];

        foreach ($slugs as $currentSlug) {
            $source = $this->registry->get($currentSlug);
            if (! $source) {
                $summary['failed']++;
                $summary['details'][] = [
                    'slug' => $currentSlug,
                    'status' => 'failed',
                    'message' => 'Unknown source slug.',
                ];
                continue;
            }

            try {
                $result = $this->ingestOne($source, $force);
                $summary[$result['status'] === 'processed' ? 'processed' : 'skipped']++;
                $summary['details'][] = $result;
            } catch (\Throwable $e) {
                $summary['failed']++;
                $this->markFailed($currentSlug, $source, $e->getMessage());
                $summary['details'][] = [
                    'slug' => $currentSlug,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ];

                Log::warning('Knowledge source ingestion failed', [
                    'slug' => $currentSlug,
                    'error' => $e->getMessage(),
                ]);
            }

            usleep((int) config('knowledge_sources.rate_limit_ms', 2000) * 1000);
        }

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    private function ingestOne(array $source, bool $force): array
    {
        $slug = (string) $source['slug'];
        $document = KnowledgeSourceDocument::firstOrNew(['slug' => $slug]);
        $document->fill([
            'name' => (string) ($source['name'] ?? $slug),
            'source_url' => $source['url'] ?? null,
            'source_type' => (string) ($source['type'] ?? 'html'),
            'license' => $source['license'] ?? null,
            'commercial_use' => (bool) ($source['commercial_use'] ?? false),
            'departments' => $source['departments'] ?? [],
            'priority' => (string) ($source['priority'] ?? 'medium'),
            'citation' => $source['citation'] ?? null,
        ]);

        if (! $force && $document->exists && $document->chunk_count > 0) {
            $this->ensureChunkedStatus($document);

            return [
                'slug' => $slug,
                'status' => 'skipped',
                'message' => 'Already ingested. Use --force to refresh.',
                'chunks' => $document->chunk_count,
            ];
        }

        $skipReason = $this->missingCredentialsReason($source);
        if ($skipReason !== null) {
            $document->save();
            $this->markSkipped($document, $skipReason);

            return [
                'slug' => $slug,
                'status' => 'skipped',
                'message' => $skipReason,
            ];
        }

        $payload = $this->fetcher->fetch($source);
        $text = $this->chunker->sanitize((string) ($payload['text'] ?? ''));

        if ($text === '') {
            throw new \RuntimeException('Fetched document returned empty text.');
        }

        $hash = hash('sha256', $text);

        if (! $force && $document->exists && $document->content_hash === $hash && $document->chunk_count > 0) {
            $document->update([
                'status' => 'chunked',
                'last_fetched_at' => now(),
            ]);

            return [
                'slug' => $slug,
                'status' => 'skipped',
                'message' => 'Content unchanged.',
                'chunks' => $document->chunk_count,
            ];
        }

        $chunks = $this->chunker->chunk($text);

        if ($chunks === []) {
            throw new \RuntimeException('Chunker produced no chunks.');
        }

        if (! $document->exists) {
            $document->save();
        }

        DB::transaction(function () use ($document, $payload, $hash, $chunks, $source, $text) {
            KnowledgeSourceChunk::where('document_id', $document->id)->delete();

            $saved = 0;
            foreach ($chunks as $chunkText) {
                $chunkText = $this->chunker->sanitize($chunkText);
                if ($chunkText === '') {
                    continue;
                }

                KnowledgeSourceChunk::create([
                    'document_id' => $document->id,
                    'chunk_index' => $saved,
                    'content' => $chunkText,
                    'char_count' => strlen($chunkText),
                    'meta' => [
                        'citation' => $source['citation'] ?? $document->citation,
                        'departments' => $source['departments'] ?? [],
                        'priority' => $source['priority'] ?? 'medium',
                    ],
                ]);
                $saved++;
            }

            if ($saved === 0) {
                throw new \RuntimeException('No valid chunks after sanitization.');
            }

            $document->fill([
                'raw_path' => $payload['raw_path'] ?? null,
                'content_hash' => $hash,
                'status' => 'chunked',
                'chunk_count' => $saved,
                'char_count' => strlen($text),
                'error_message' => null,
                'meta' => array_merge((array) ($document->meta ?? []), (array) ($payload['meta'] ?? []), [
                    'source_config' => [
                        'type' => $source['type'] ?? null,
                        'license' => $source['license'] ?? null,
                    ],
                ]),
                'last_fetched_at' => now(),
            ]);
            $document->save();
        });

        return [
            'slug' => $slug,
            'status' => 'processed',
            'message' => 'Ingested successfully.',
            'chunks' => count($chunks),
            'chars' => strlen($text),
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function missingCredentialsReason(array $source): ?string
    {
        $type = (string) ($source['type'] ?? '');

        if ($type === 'onet' && ! config('knowledge_sources.onet.client')) {
            return 'Skipped: set ONET_WS_CLIENT in .env (https://services.onetcenter.org/).';
        }

        if ($type === 'oer_commons' && ! config('knowledge_sources.oer_commons.token')) {
            return 'Skipped: set OER_COMMONS_API_TOKEN in .env (contact info@oercommons.org).';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function markFailed(string $slug, array $source, string $message): void
    {
        $document = KnowledgeSourceDocument::firstOrNew(['slug' => $slug]);
        $safeMessage = Str::limit($this->chunker->sanitize($message), 500);

        $document->fill([
            'name' => (string) ($source['name'] ?? $slug),
            'source_url' => $source['url'] ?? null,
            'source_type' => (string) ($source['type'] ?? 'html'),
            'license' => $source['license'] ?? null,
            'commercial_use' => (bool) ($source['commercial_use'] ?? false),
            'departments' => $source['departments'] ?? [],
            'priority' => (string) ($source['priority'] ?? 'medium'),
            'citation' => $source['citation'] ?? null,
            'error_message' => $safeMessage,
            'last_fetched_at' => now(),
        ]);

        if ($document->chunk_count > 0 || $document->chunks()->exists()) {
            $document->chunk_count = $document->chunks()->count();
            $document->status = 'chunked';
        } else {
            $document->status = 'failed';
        }

        $document->save();
    }

    private function markSkipped(KnowledgeSourceDocument $document, string $message): void
    {
        $document->update([
            'status' => $document->chunk_count > 0 ? 'chunked' : 'skipped',
            'error_message' => Str::limit($this->chunker->sanitize($message), 500),
        ]);
    }

    private function ensureChunkedStatus(KnowledgeSourceDocument $document): void
    {
        if ($document->status === 'chunked' && $document->chunk_count > 0) {
            return;
        }

        $count = $document->chunks()->count();
        if ($count > 0) {
            $document->update([
                'status' => 'chunked',
                'chunk_count' => $count,
                'error_message' => null,
            ]);
        }
    }

    public function repairStatuses(): int
    {
        $fixed = 0;

        KnowledgeSourceDocument::query()
            ->where('chunk_count', '>', 0)
            ->where('status', '!=', 'chunked')
            ->each(function (KnowledgeSourceDocument $document) use (&$fixed) {
                $this->ensureChunkedStatus($document);
                $fixed++;
            });

        KnowledgeSourceDocument::query()
            ->where('chunk_count', 0)
            ->whereHas('chunks')
            ->each(function (KnowledgeSourceDocument $document) use (&$fixed) {
                $this->ensureChunkedStatus($document);
                $fixed++;
            });

        return $fixed;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, ?string $department = null, int $limit = 8): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $terms = $this->extractSearchTerms($query);
        if ($terms === []) {
            return [];
        }

        $builder = KnowledgeSourceChunk::query()
            ->with('document')
            ->whereHas('document', fn ($q) => $q->where('status', 'chunked'));

        if ($department) {
            $builder->whereHas('document', function ($q) use ($department) {
                $q->whereJsonContains('departments', $department);
            });
        }

        $builder->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->orWhere('content', 'like', '%' . $this->escapeLike($term) . '%');
            }
        });

        $chunks = $builder->limit(150)->get();
        $scored = [];

        foreach ($chunks as $chunk) {
            $haystack = mb_strtolower($chunk->content);
            $score = 0;

            foreach ($terms as $term) {
                if (str_contains($haystack, $term)) {
                    $score += substr_count($haystack, $term);
                }
            }

            if ($score <= 0) {
                continue;
            }

            $scored[] = $this->formatMatch($chunk, $score);
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, max(1, $limit));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function bootstrapMatches(?string $topic = null, int $limit = 10): array
    {
        if ($topic !== null && trim($topic) !== '') {
            $topicMatches = $this->search($topic, null, $limit);
            if ($topicMatches !== []) {
                return $topicMatches;
            }
        }

        $results = [];
        $documents = KnowledgeSourceDocument::query()
            ->where('status', 'chunked')
            ->where('chunk_count', '>', 0)
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('slug')
            ->get();

        foreach ($documents as $document) {
            $chunk = $document->chunks()->orderBy('chunk_index')->first();
            if (! $chunk) {
                continue;
            }

            $results[] = $this->formatMatch($chunk, 1);

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    public function buildGrounding(string $query, ?string $department = null, int $limit = 8, int $minScore = 2): string
    {
        if (! config('knowledge_sources.enabled', true)) {
            return '';
        }

        if (! KnowledgeSourceDocument::query()->where('status', 'chunked')->exists()) {
            return '';
        }

        try {
            $matches = $this->search($query, $department, $limit);
            $matches = array_values(array_filter(
                $matches,
                fn (array $match) => (int) ($match['score'] ?? 0) >= $minScore
            ));

            if ($matches === []) {
                return '';
            }

            return $this->formatGroundingBlocks(array_slice($matches, 0, $limit));
        } catch (\Throwable $e) {
            Log::debug('Knowledge grounding skipped', ['error' => $e->getMessage()]);

            return '';
        }
    }

    public function groundingPromptSuffix(string $blocks): string
    {
        return ' Use the internal reference notes below ONLY when they directly help answer the question. '
            . 'Weave useful facts into your reply naturally — do not cite sources, document titles, or mention a knowledge base. '
            . 'For casual chat, platform navigation, or screen help, ignore the notes entirely.'
            . "\n\nInternal reference (only if relevant):\n" . $blocks;
    }

    public function buildBootstrapGrounding(?string $topic = null, int $limit = 12): string
    {
        if (! config('knowledge_sources.enabled', true)) {
            return '';
        }

        if (! KnowledgeSourceDocument::query()->where('status', 'chunked')->exists()) {
            return '';
        }

        try {
            return $this->formatGroundingBlocks($this->bootstrapMatches($topic, $limit));
        } catch (\Throwable $e) {
            Log::debug('Knowledge bootstrap grounding skipped', ['error' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $matches
     */
    public function formatGroundingBlocks(array $matches, int $perChunkLimit = 900): string
    {
        if ($matches === []) {
            return '';
        }

        $blocks = [];
        foreach ($matches as $match) {
            $content = trim((string) ($match['content'] ?? ''));
            if ($content === '') {
                continue;
            }
            $blocks[] = Str::limit($content, $perChunkLimit);
        }

        return implode("\n\n---\n\n", $blocks);
    }

    /**
     * @return array<int, string>
     */
    private function extractSearchTerms(string $query): array
    {
        $terms = preg_split('/\s+/u', mb_strtolower($query)) ?: [];
        $stopwords = [
            'the', 'and', 'for', 'with', 'that', 'this', 'what', 'how', 'you', 'your',
            'les', 'des', 'une', 'pour', 'dans', 'est', 'sur', 'que', 'qui', 'pas',
        ];

        return array_values(array_filter($terms, fn ($term) => strlen($term) >= 2 && ! in_array($term, $stopwords, true)));
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMatch(KnowledgeSourceChunk $chunk, int $score): array
    {
        return [
            'score' => $score,
            'content' => $chunk->content,
            'chunk_index' => $chunk->chunk_index,
            'document' => [
                'slug' => $chunk->document?->slug,
                'name' => $chunk->document?->name,
                'citation' => $chunk->document?->citation,
                'source_url' => $chunk->document?->source_url,
                'departments' => $chunk->document?->departments,
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $primary
     * @param  array<int, array<string, mixed>>  $secondary
     * @return array<int, array<string, mixed>>
     */
    private function mergeUniqueMatches(array $primary, array $secondary, int $limit): array
    {
        $merged = $primary;
        $seen = [];

        foreach ($merged as $match) {
            $slug = (string) ($match['document']['slug'] ?? '');
            if ($slug !== '') {
                $seen[$slug] = true;
            }
        }

        foreach ($secondary as $match) {
            $slug = (string) ($match['document']['slug'] ?? '');
            if ($slug !== '' && isset($seen[$slug])) {
                continue;
            }

            $merged[] = $match;
            if ($slug !== '') {
                $seen[$slug] = true;
            }

            if (count($merged) >= $limit) {
                break;
            }
        }

        return array_slice($merged, 0, $limit);
    }
}
