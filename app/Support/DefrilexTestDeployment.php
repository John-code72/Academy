<?php

namespace App\Support;

class DefrilexTestDeployment
{
    /**
     * @return array{kind: string, label: string, hint: string}|null
     */
    public static function resolve(?string $title): ?array
    {
        if ($title === null || $title === '') {
            return null;
        }

        foreach (config('defrilex_test_deployment.patterns', []) as $row) {
            if (! empty($row['match']) && str_contains($title, (string) $row['match'])) {
                return [
                    'kind' => (string) ($row['kind'] ?? 'unmapped'),
                    'label' => (string) ($row['label'] ?? ''),
                    'hint' => (string) ($row['hint'] ?? ''),
                ];
            }
        }

        return null;
    }

    public static function sortPriority(?string $title): int
    {
        $resolved = self::resolve($title);
        $kind = $resolved['kind'] ?? 'unmapped';
        $weights = config('defrilex_test_deployment.sort_weights', []);

        return (int) ($weights[$kind] ?? $weights['unmapped'] ?? 100);
    }

    public static function badgeClass(?string $kind): string
    {
        return match ($kind) {
            'tier_1' => 'bg-danger',
            'tier_2' => 'bg-warning text-dark',
            'tier_3' => 'bg-info text-dark',
            'baseline' => 'bg-secondary',
            default => 'bg-light text-dark border',
        };
    }
}
