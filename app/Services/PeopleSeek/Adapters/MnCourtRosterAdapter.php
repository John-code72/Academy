<?php

namespace App\Services\PeopleSeek\Adapters;

use App\Services\PeopleSeek\Contracts\InterpreterSourceAdapter;
use App\Services\PeopleSeek\PeopleSeekConfigRegistry;
use App\Services\PeopleSeek\PeopleSeekHttpClient;

class MnCourtRosterAdapter implements InterpreterSourceAdapter
{
    private const BASE_URL = 'https://findinterpreters.courts.state.mn.us';

    public function __construct(
        private readonly PeopleSeekHttpClient $http,
        private readonly PeopleSeekConfigRegistry $registry,
    ) {}

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $options
     * @return array<int, array<string, mixed>>
     */
    public function fetchProfiles(array $source, array $options = []): array
    {
        $limit = max(1, (int) ($options['limit'] ?? 10));
        $languageFilter = $options['language'] ?? null;
        $languageMap = $this->fetchLanguageMap();

        if ($languageFilter) {
            $languageNames = [$this->resolveLanguageName((string) $languageFilter)];
        } else {
            $languageNames = [];
            foreach ((array) ($source['target_languages'] ?? []) as $slug) {
                $resolved = $this->registry->resolveLanguageName((string) $slug);
                if ($resolved && isset($languageMap[$resolved])) {
                    $languageNames[] = $resolved;
                }
            }
        }

        $profiles = [];

        foreach ($languageNames as $languageName) {
            $languageId = $languageMap[$languageName] ?? null;
            if ($languageId === null) {
                continue;
            }

            $html = $this->http->get(self::BASE_URL . '/Home/Search?LanguageId=' . $languageId);
            $parsed = $this->parseSearchHtml($html, (string) $languageId, $languageName);
            $profiles = array_merge($profiles, $parsed);

            if (count($profiles) >= $limit) {
                break;
            }

            usleep((int) ($source['rate_limit_ms'] ?? config('peopleseek_sources.rate_limit_ms', 3000)) * 1000);
        }

        return array_slice($profiles, 0, $limit);
    }

    private function resolveLanguageName(string $language): string
    {
        return $this->registry->resolveLanguageName($language)
            ?? strtoupper(str_replace('_', ' ', $language));
    }

    /**
     * @return array<string, string> languageName => languageId
     */
    private function fetchLanguageMap(): array
    {
        $html = $this->http->get(self::BASE_URL . '/');
        $map = [];

        if (preg_match_all('/value="(\d+)"[^>]*>([^<]+)<\/option>/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = html_entity_decode(trim($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $map[$name] = $match[1];
            }
        }

        return $map;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseSearchHtml(string $html, string $languageId, string $languageName): array
    {
        $parts = preg_split('/Interpreter name:\s*/i', $html) ?: [];
        $profiles = [];

        foreach ($parts as $index => $part) {
            if ($index === 0) {
                continue;
            }

            $profile = $this->parseInterpreterHtmlChunk($part, $languageId, $languageName);
            if ($profile !== null) {
                $profiles[] = $profile;
            }
        }

        return $profiles;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseInterpreterHtmlChunk(string $chunk, string $languageId, string $defaultLanguage): ?array
    {
        if (! preg_match('/^([^<]+)/', trim($chunk), $nameMatch)) {
            return null;
        }

        $fullName = trim(html_entity_decode($nameMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($fullName === '' || str_contains(strtolower($fullName), 'interpreter certification')) {
            return null;
        }

        $residence = $this->matchFirst($chunk, '/City, County of Residence:\s*([^<]+)/i');
        $phone = $this->matchFirst($chunk, '/Phone<\/label>\s*<div>[\s\S]*?(\(?\d{3}\)?[\s\-]*\d{3}[\s\-]*\d{4})/i');
        $email = $this->matchFirst($chunk, '/mailto:([^"\'>\s]+)/i');
        $languagesRaw = null;
        if (preg_match_all('/Languages<\/label>\s*<div>[\s\S]*?<div>\s*([^<]+)/i', $chunk, $langMatches)) {
            $languagesRaw = implode("\n", array_map('trim', $langMatches[1]));
        }

        [$city, $region] = $this->parseResidence($residence ? trim($residence) : null);
        $languages = $this->parseLanguages($languagesRaw, $defaultLanguage);
        $credentialStatus = $this->detectCredentialStatus($languagesRaw);
        $certifications = $credentialStatus === 'certified'
            ? ['Minnesota Court Certified Interpreter']
            : ['Minnesota Court Interpreter Roster'];

        $specialties = ['court', 'legal'];
        if (stripos($chunk, 'TRI</span> Yes') !== false || stripos($chunk, 'Telephone Remote') !== false && stripos($chunk, 'Yes') !== false) {
            $specialties[] = 'opi';
        }
        if (stripos($chunk, 'VRI</span> Yes') !== false || stripos($chunk, 'Video Remote') !== false && preg_match('/Video Remote\?[\s\S]*?Yes/i', $chunk)) {
            $specialties[] = 'vri';
        }

        $recordId = hash('crc32b', $fullName . '|' . $languageId . '|' . ($email ?? ''));

        return [
            'full_name' => $fullName,
            'interpreter_specialty' => array_values(array_unique($specialties)),
            'languages' => $languages,
            'certifications' => $certifications,
            'credential_status' => $credentialStatus,
            'city' => $city,
            'region' => $region,
            'country' => $residence && str_contains(strtolower($residence), 'out of state') ? null : 'US',
            'email' => $email,
            'phone' => $phone ? preg_replace('/\s+/', ' ', trim($phone)) : null,
            'public_profile_url' => self::BASE_URL . '/Home/Search?LanguageId=' . $languageId,
            'source_record_id' => 'mn-' . $languageId . '-' . $recordId,
            'confidence' => [
                'identity' => 0.92,
                'language' => 0.95,
                'certification' => $credentialStatus === 'certified' ? 0.95 : 0.65,
                'location' => $city ? 0.88 : 0.5,
                'contact' => ($email || $phone) ? 0.9 : 0.3,
            ],
        ];
    }

    private function matchFirst(string $haystack, string $pattern): ?string
    {
        if (preg_match($pattern, $haystack, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function parseResidence(?string $residence): array
    {
        if (! $residence) {
            return [null, null];
        }

        if (preg_match('/^([^,]+),\s*([A-Za-z\s]+)$/u', trim($residence), $m)) {
            return [trim($m[1]), trim($m[2])];
        }

        return [trim($residence), 'MN'];
    }

    /**
     * @return array<int, string>
     */
    private function parseLanguages(?string $languagesRaw, string $defaultLanguage): array
    {
        if (! $languagesRaw) {
            return [$this->titleLanguage($defaultLanguage)];
        }

        $lines = preg_split('/\R/', $languagesRaw) ?: [];
        $languages = [];

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s*-\s*Certified\s*$/i', '', $line) ?? $line);
            if ($line !== '') {
                $languages[] = $this->titleLanguage($line);
            }
        }

        return $languages !== [] ? array_values(array_unique($languages)) : [$this->titleLanguage($defaultLanguage)];
    }

    private function detectCredentialStatus(?string $languagesRaw): string
    {
        if ($languagesRaw && preg_match('/-\s*Certified/i', $languagesRaw)) {
            return 'certified';
        }

        return 'registered';
    }

    private function titleLanguage(string $language): string
    {
        return ucwords(strtolower(trim($language)));
    }
}
