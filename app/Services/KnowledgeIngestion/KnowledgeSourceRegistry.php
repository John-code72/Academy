<?php

namespace App\Services\KnowledgeIngestion;

class KnowledgeSourceRegistry
{
    public function all(): array
    {
        $sources = config('knowledge_sources.sources', []);

        if (! $this->apiSourcesEnabled()) {
            $sources = array_filter(
                $sources,
                fn (array $source) => empty($source['requires_api'])
            );
        }

        return $sources;
    }

    public function allIncludingApi(): array
    {
        return config('knowledge_sources.sources', []);
    }

    public function get(string $slug): ?array
    {
        $source = $this->all()[$slug] ?? null;

        if (! is_array($source)) {
            return null;
        }

        return array_merge($source, ['slug' => $slug]);
    }

    public function slugs(): array
    {
        return array_keys($this->all());
    }

    public function apiSlugs(): array
    {
        return array_keys(array_filter(
            $this->allIncludingApi(),
            fn (array $source) => ! empty($source['requires_api'])
        ));
    }

    public function isEnabled(): bool
    {
        return (bool) config('knowledge_sources.enabled', true);
    }

    public function apiSourcesEnabled(): bool
    {
        return (bool) config('knowledge_sources.api_sources_enabled', false);
    }
}
