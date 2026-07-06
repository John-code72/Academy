<?php

namespace App\Services\PeopleSeek;

class PeopleSeekConfigRegistry
{
    public function isEnabled(): bool
    {
        return (bool) config('peopleseek_sources.enabled', false);
    }

    public function isDryRun(): bool
    {
        if (config('peopleseek_sources.mode') === 'production') {
            return false;
        }

        return (bool) config('peopleseek_sources.dry_run', true);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return config('peopleseek_sources.sources', []);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $sourceKey): ?array
    {
        $source = $this->all()[$sourceKey] ?? null;

        if (! is_array($source)) {
            return null;
        }

        return array_merge($source, ['source_key' => $sourceKey]);
    }

    /**
     * @return array<int, string>
     */
    public function slugs(): array
    {
        return array_keys($this->all());
    }

    /**
     * @return array<int, string>
     */
    public function allowedHosts(): array
    {
        $hosts = config('peopleseek_sources.allowed_hosts', []);

        foreach ($this->all() as $source) {
            if (! empty($source['url'])) {
                $host = parse_url((string) $source['url'], PHP_URL_HOST);
                if (is_string($host) && $host !== '') {
                    $hosts[] = strtolower($host);
                }
            }
        }

        return array_values(array_unique(array_map('strtolower', $hosts)));
    }

    public function resolveLanguageName(string $language): ?string
    {
        $key = strtolower(str_replace([' ', '-'], '_', trim($language)));
        $aliases = config('peopleseek_sources.language_aliases', []);

        if (isset($aliases[$key])) {
            return $aliases[$key];
        }

        return strtoupper(str_replace('_', ' ', $key));
    }
}
