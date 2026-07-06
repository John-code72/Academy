<?php

namespace App\Services\PeopleSeek;

class InterpreterProfileNormalizer
{
    /**
     * @param  array<string, mixed>  $raw
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    public function normalize(array $raw, array $source): array
    {
        $fullName = trim((string) ($raw['full_name'] ?? ''));
        [$given, $family] = $this->splitName($fullName);

        return [
            'full_name' => $fullName !== '' ? $fullName : null,
            'given_name' => $given,
            'family_name' => $family,
            'role_title' => $raw['role_title'] ?? 'Court Interpreter',
            'canonical_role_key' => 'interpreter',
            'interpreter_specialty' => array_values((array) ($raw['interpreter_specialty'] ?? ['court', 'legal'])),
            'languages' => array_values(array_unique(array_filter((array) ($raw['languages'] ?? [])))),
            'dialects' => array_values(array_filter((array) ($raw['dialects'] ?? []))),
            'certifications' => array_values(array_filter((array) ($raw['certifications'] ?? []))),
            'credential_status' => $raw['credential_status'] ?? null,
            'location' => [
                'city' => $raw['city'] ?? data_get($raw, 'location.city'),
                'region' => $raw['region'] ?? data_get($raw, 'location.region'),
                'country' => $raw['country'] ?? data_get($raw, 'location.country', 'US'),
            ],
            'email' => $raw['email'] ?? null,
            'phone' => $raw['phone'] ?? null,
            'website' => $raw['website'] ?? null,
            'public_profile_url' => $raw['public_profile_url'] ?? ($source['url'] ?? null),
            'source_record_id' => $raw['source_record_id'] ?? null,
            'source_name' => $source['name'] ?? null,
            'source_url' => $source['url'] ?? null,
            'source_access_method' => $source['access_method'] ?? null,
            'legal_status' => $source['legal_status'] ?? null,
            'confidence' => array_merge([
                'identity' => 0.0,
                'language' => 0.0,
                'dialect' => 0.0,
                'certification' => 0.0,
                'location' => 0.0,
                'contact' => 0.0,
            ], (array) ($raw['confidence'] ?? [])),
        ];
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function splitName(string $fullName): array
    {
        if ($fullName === '') {
            return [null, null];
        }

        if (str_contains($fullName, ',')) {
            [$family, $given] = array_map('trim', explode(',', $fullName, 2));

            return [$given ?: null, $family ?: null];
        }

        $parts = preg_split('/\s+/', $fullName) ?: [];
        if (count($parts) === 1) {
            return [$parts[0], null];
        }

        $family = array_pop($parts);
        $given = implode(' ', $parts);

        return [$given ?: null, $family ?: null];
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    public function contentHash(array $profile): string
    {
        $payload = json_encode([
            'full_name' => $profile['full_name'] ?? null,
            'languages' => $profile['languages'] ?? [],
            'email' => $profile['email'] ?? null,
            'phone' => $profile['phone'] ?? null,
            'source_record_id' => $profile['source_record_id'] ?? null,
            'source_name' => $profile['source_name'] ?? null,
        ], JSON_UNESCAPED_UNICODE);

        return hash('sha256', (string) $payload);
    }
}
