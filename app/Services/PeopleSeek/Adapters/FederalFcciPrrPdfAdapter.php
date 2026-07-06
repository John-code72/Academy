<?php

namespace App\Services\PeopleSeek\Adapters;

use App\Services\PeopleSeek\Contracts\InterpreterSourceAdapter;
use App\Services\PeopleSeek\PeopleSeekHttpClient;
use Smalot\PdfParser\Parser as PdfParser;

class FederalFcciPrrPdfAdapter implements InterpreterSourceAdapter
{
    public function __construct(
        private readonly PeopleSeekHttpClient $http,
    ) {}

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $options
     * @return array<int, array<string, mixed>>
     */
    public function fetchProfiles(array $source, array $options = []): array
    {
        $limit = max(1, (int) ($options['limit'] ?? 10));
        $languageFilter = isset($options['language'])
            ? $this->normalizeLanguageKey((string) $options['language'])
            : null;

        $url = (string) ($source['url'] ?? '');
        $binary = $this->http->get($url, ['Accept' => 'application/pdf,*/*']);
        $this->http->storeRaw((string) ($source['source_key'] ?? 'fcci_prr'), 'pdf', $binary);

        $parser = new PdfParser();
        $text = $parser->parseContent($binary)->getText();
        $sections = $this->splitLanguageSections($text);

        $profiles = [];
        foreach ($sections as $languageKey => $sectionText) {
            if ($languageFilter && $languageKey !== $languageFilter) {
                continue;
            }

            foreach ($this->parseEntries($sectionText, $languageKey) as $entry) {
                $profiles[] = $entry;
                if (count($profiles) >= $limit) {
                    return $profiles;
                }
            }
        }

        return array_slice($profiles, 0, $limit);
    }

    /**
     * @return array<string, string>
     */
    private function splitLanguageSections(string $text): array
    {
        $sections = [];
        $parts = preg_split('/Federally Certified Interpreters of\s+/i', $text) ?: [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || str_starts_with(strtolower($part), 'name')) {
                continue;
            }

            if (! preg_match('/^([A-Za-z\s]+?)(?:\R|\s{2,}|Page\s+\d)/s', $part, $header)) {
                continue;
            }

            $languageKey = $this->normalizeLanguageKey(trim($header[1]));
            $body = preg_replace('/^' . preg_quote(trim($header[1]), '/') . '\s*/i', '', $part) ?? $part;
            $sections[$languageKey] = $body;
        }

        return $sections;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseEntries(string $sectionText, string $languageKey): array
    {
        $profiles = [];
        $pattern = '/([A-Za-z][A-Za-z\s\.\'-]*?)\s+([A-Z]{2})([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})[\s\t]*([\d\-\(\)\.\s]+?)([A-Za-z][A-Za-z\-\'\.]*),\s*([A-Za-z][A-Za-z\s\-\'.]*?)(?=[A-Z][a-z][A-Za-z\s\.\'-]*\s+[A-Z]{2}[a-zA-Z0-9._%+-]+@|Page\s+\d+\s+of|\z)/s';

        if (preg_match_all($pattern, $sectionText, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $city = trim($match[1]);
                $region = trim($match[2]);
                $email = trim($match[3]);
                $phone = trim(preg_replace('/\s+/', ' ', $match[4]) ?? $match[4]);
                $family = trim($match[5]);
                $given = trim($match[6]);
                $fullName = $family . ', ' . $given;

                $profiles[] = [
                    'full_name' => $fullName,
                    'interpreter_specialty' => ['court', 'legal', 'federal'],
                    'languages' => [$this->displayLanguage($languageKey)],
                    'certifications' => ['FCCI - Federally Certified Court Interpreter'],
                    'credential_status' => 'certified',
                    'city' => $city,
                    'region' => $region,
                    'country' => 'US',
                    'email' => $email,
                    'phone' => $phone,
                    'public_profile_url' => null,
                    'source_record_id' => 'fcci-' . $languageKey . '-' . hash('crc32b', $fullName . $email),
                    'confidence' => [
                        'identity' => 0.95,
                        'language' => 0.98,
                        'certification' => 0.98,
                        'location' => 0.9,
                        'contact' => 0.92,
                    ],
                ];
            }
        }

        return $profiles;
    }

    private function normalizeLanguageKey(string $language): string
    {
        $key = strtolower(trim($language));
        $key = str_replace(['-', ' '], '_', $key);

        return match (true) {
            str_contains($key, 'haitian') || str_contains($key, 'kreyol') => 'haitian_creole',
            str_contains($key, 'spanish') => 'spanish',
            str_contains($key, 'navajo') => 'navajo',
            default => $key,
        };
    }

    private function displayLanguage(string $languageKey): string
    {
        return match ($languageKey) {
            'haitian_creole' => 'Haitian Creole',
            'spanish' => 'Spanish',
            'navajo' => 'Navajo',
            default => ucwords(str_replace('_', ' ', $languageKey)),
        };
    }
}
