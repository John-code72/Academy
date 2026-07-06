<?php

namespace App\Services\KnowledgeIngestion;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class KnowledgeSourceFetcher
{
    public function __construct(
        private readonly KnowledgeSourceRegistry $registry,
    ) {}

    /**
     * @return array{text: string, raw_path: ?string, meta: array<string, mixed>}
     */
    public function fetch(array $source): array
    {
        $type = $source['type'] ?? 'html';

        return match ($type) {
            'pdf' => $this->fetchPdf($source),
            'html' => $this->fetchHtml($source),
            'onet' => $this->fetchOnet($source),
            'oer_commons' => $this->fetchOerCommons($source),
            default => throw new \InvalidArgumentException('Unsupported source type: ' . $type),
        };
    }

    /**
     * @return array{text: string, raw_path: ?string, meta: array<string, mixed>}
     */
    private function fetchPdf(array $source): array
    {
        $url = (string) ($source['url'] ?? '');
        $binary = $this->httpGet($url, ['Accept' => 'application/pdf,*/*']);
        $slug = (string) ($source['slug'] ?? 'document');
        $path = $this->storeRaw($slug, 'pdf', $binary);

        $parser = new PdfParser();
        $pdf = $parser->parseContent($binary);
        $text = trim($pdf->getText());

        return [
            'text' => $text,
            'raw_path' => $path,
            'meta' => [
                'pages' => count($pdf->getPages()),
                'fetched_url' => $url,
                'content_type' => 'application/pdf',
            ],
        ];
    }

    /**
     * @return array{text: string, raw_path: ?string, meta: array<string, mixed>}
     */
    private function fetchHtml(array $source): array
    {
        $url = (string) ($source['url'] ?? '');
        $html = $this->httpGet($url, ['Accept' => 'text/html,application/xhtml+xml']);
        $slug = (string) ($source['slug'] ?? 'document');
        $path = $this->storeRaw($slug, 'html', $html);

        $text = $this->htmlToText($html);

        return [
            'text' => $text,
            'raw_path' => $path,
            'meta' => [
                'fetched_url' => $url,
                'content_type' => 'text/html',
            ],
        ];
    }

    /**
     * @return array{text: string, raw_path: ?string, meta: array<string, mixed>}
     */
    private function fetchOnet(array $source): array
    {
        $soc = (string) ($source['onet_soc'] ?? '');
        $client = config('knowledge_sources.onet.client');

        if (! $client) {
            throw new \RuntimeException('ONET_WS_CLIENT is not configured.');
        }

        $base = rtrim((string) config('knowledge_sources.onet.base_url'), '/');
        $summaryUrl = $base . '/online/occupations/' . rawurlencode($soc) . '/summary?client=' . rawurlencode($client);
        $skillsUrl = $base . '/online/occupations/' . rawurlencode($soc) . '/details/skills?client=' . rawurlencode($client);
        $knowledgeUrl = $base . '/online/occupations/' . rawurlencode($soc) . '/details/knowledge?client=' . rawurlencode($client);
        $tasksUrl = $base . '/online/occupations/' . rawurlencode($soc) . '/details/tasks?client=' . rawurlencode($client);

        $summary = $this->httpGetJson($summaryUrl);
        $skills = $this->httpGetJson($skillsUrl);
        $knowledge = $this->httpGetJson($knowledgeUrl);
        $tasks = $this->httpGetJson($tasksUrl);

        $text = $this->formatOnetDocument($soc, $summary, $skills, $knowledge, $tasks);
        $slug = (string) ($source['slug'] ?? 'onet_' . str_replace('.', '_', $soc));
        $path = $this->storeRaw($slug, 'json', json_encode([
            'summary' => $summary,
            'skills' => $skills,
            'knowledge' => $knowledge,
            'tasks' => $tasks,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [
            'text' => $text,
            'raw_path' => $path,
            'meta' => [
                'onet_soc' => $soc,
                'attribution' => 'Data from O*NET Web Services. O*NET is a trademark of the U.S. Department of Labor.',
            ],
        ];
    }

    /**
     * @return array{text: string, raw_path: ?string, meta: array<string, mixed>}
     */
    private function fetchOerCommons(array $source): array
    {
        $token = config('knowledge_sources.oer_commons.token');
        if (! $token) {
            throw new \RuntimeException('OER_COMMONS_API_TOKEN is not configured.');
        }

        $query = (string) ($source['query'] ?? 'training');
        $url = (string) config('knowledge_sources.oer_commons.base_url');

        $response = Http::timeout((int) config('knowledge_sources.request_timeout', 45))
            ->get($url, [
                'token' => $token,
                'f.search' => $query,
                'f.license' => 'cc-by,cc-by-sa,public-domain',
                'limit' => 25,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('OER Commons API failed: HTTP ' . $response->status());
        }

        $payload = $response->json();
        $items = $payload['results'] ?? $payload['items'] ?? [];
        $text = $this->formatOerCommonsDocument($query, is_array($items) ? $items : []);
        $slug = (string) ($source['slug'] ?? 'oer_commons');
        $path = $this->storeRaw($slug, 'json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [
            'text' => $text,
            'raw_path' => $path,
            'meta' => [
                'query' => $query,
                'result_count' => is_array($items) ? count($items) : 0,
            ],
        ];
    }

    private function httpGet(string $url, array $headers = []): string
    {
        $this->assertAllowedUrl($url);

        $request = Http::timeout((int) config('knowledge_sources.request_timeout', 45))
            ->withHeaders(array_merge([
                'User-Agent' => (string) config('knowledge_sources.user_agent'),
            ], $headers));

        if (! config('knowledge_sources.verify_ssl', true)) {
            $request = $request->withoutVerifying();
        }

        $response = $request->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('HTTP fetch failed for ' . $url . ' (status ' . $response->status() . ')');
        }

        return (string) $response->body();
    }

    /**
     * @return array<string, mixed>
     */
    private function httpGetJson(string $url): array
    {
        $body = $this->httpGet($url, ['Accept' => 'application/json']);
        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function assertAllowedUrl(string $url): void
    {
        $allowedHosts = $this->allowedHosts();

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || ! in_array(strtolower($host), $allowedHosts, true)) {
            throw new \RuntimeException('URL host is not in the approved knowledge source allowlist: ' . $url);
        }
    }

    /**
     * @return array<int, string>
     */
    private function allowedHosts(): array
    {
        $hosts = [
            'www.ncihc.org',
            'ncihc.org',
            'najit.org',
            'www.chiaonline.org',
            'chiaonline.org',
            'www.imiaweb.org',
            'imiaweb.org',
            'assets.td.org',
            'www.kirkpatrickpartners.com',
            'kirkpatrickpartners.com',
            'nvlpubs.nist.gov',
            'www.osha.gov',
            'osha.gov',
            'services.onetcenter.org',
            'www.oercommons.org',
            'oercommons.org',
        ];

        foreach ($this->registry->all() as $source) {
            if (! empty($source['url'])) {
                $host = parse_url((string) $source['url'], PHP_URL_HOST);
                if (is_string($host) && $host !== '') {
                    $hosts[] = strtolower($host);
                }
            }
        }

        return array_values(array_unique($hosts));
    }

    private function storeRaw(string $slug, string $extension, string $contents): string
    {
        $disk = (string) config('knowledge_sources.storage_disk', 'local');
        $base = trim((string) config('knowledge_sources.storage_path', 'knowledge-sources'), '/');
        $path = $base . '/' . $slug . '.' . $extension;

        Storage::disk($disk)->put($path, $contents);

        return $path;
    }

    private function htmlToText(string $html): string
    {
        $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $html) ?? $html;

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        if (! $loaded) {
            return trim(strip_tags($html));
        }

        return trim($dom->textContent);
    }

    /**
     * @param  array<string, mixed>  $summary
     * @param  array<string, mixed>  $skills
     * @param  array<string, mixed>  $knowledge
     * @param  array<string, mixed>  $tasks
     */
    private function formatOnetDocument(string $soc, array $summary, array $skills, array $knowledge, array $tasks): string
    {
        $title = data_get($summary, 'occupation.title', $soc);
        $description = data_get($summary, 'occupation.description', '');

        $lines = [
            'Occupation: ' . $title,
            'O*NET-SOC: ' . $soc,
            '',
            'Description:',
            (string) $description,
            '',
            'Tasks:',
        ];

        foreach ((array) data_get($tasks, 'task', []) as $task) {
            $statement = is_array($task) ? ($task['statement'] ?? null) : null;
            if ($statement) {
                $lines[] = '- ' . $statement;
            }
        }

        $lines[] = '';
        $lines[] = 'Skills:';
        foreach ((array) data_get($skills, 'skill', []) as $skill) {
            $name = is_array($skill) ? ($skill['name'] ?? null) : null;
            if ($name) {
                $lines[] = '- ' . $name;
            }
        }

        $lines[] = '';
        $lines[] = 'Knowledge areas:';
        foreach ((array) data_get($knowledge, 'element', []) as $element) {
            $name = is_array($element) ? ($element['name'] ?? null) : null;
            if ($name) {
                $lines[] = '- ' . $name;
            }
        }

        return trim(implode("\n", $lines));
    }

    /**
     * @param  array<int, mixed>  $items
     */
    private function formatOerCommonsDocument(string $query, array $items): string
    {
        $lines = [
            'OER Commons search results',
            'Query: ' . $query,
            'License filter: cc-by, cc-by-sa, public-domain',
            '',
        ];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = $item['title'] ?? $item['name'] ?? 'Untitled';
            $description = $item['description'] ?? $item['abstract'] ?? '';
            $url = $item['url'] ?? $item['landing_page'] ?? '';
            $license = $item['license'] ?? $item['usage_rights'] ?? '';

            $lines[] = 'Title: ' . $title;
            if ($description) {
                $lines[] = 'Description: ' . $description;
            }
            if ($url) {
                $lines[] = 'URL: ' . $url;
            }
            if ($license) {
                $lines[] = 'License: ' . (is_array($license) ? json_encode($license) : $license);
            }
            $lines[] = '';
        }

        return trim(implode("\n", $lines));
    }
}
