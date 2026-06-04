<?php

/**
 * Defrilex Test Library — deployment priority (Q2–Q3 2026 and onward).
 * Used to sort Defrilex onboarding tests and optionally show tier badges (internal use).
 *
 * @see env DEFRILEX_SHOW_DEPLOYMENT_BADGES
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Show tier badges on the Defrilex onboarding test list
    |--------------------------------------------------------------------------
    | When false (default), tests are still sorted by tier priority but labels
    | stay hidden — suitable for candidate-facing pages.
    */
    'show_deployment_badges' => env('DEFRILEX_SHOW_DEPLOYMENT_BADGES', false),

    /*
    |--------------------------------------------------------------------------
    | Optional hero note (e.g. internal hiring portal). Empty = hidden.
    |--------------------------------------------------------------------------
    */
    'practice_list_hero_note' => env('DEFRILEX_PRACTICE_LIST_HERO_NOTE', ''),

    /*
    | Optional hero note (candidate-facing; preferred env key)
    */
    'onboarding_list_hero_note' => env('DEFRILEX_ONBOARDING_LIST_HERO_NOTE', env('DEFRILEX_PRACTICE_LIST_HERO_NOTE', '')),

    /*
    |--------------------------------------------------------------------------
    | Title substring → deployment group (first match wins; order matters)
    |--------------------------------------------------------------------------
    | sort: lower = listed first. kind: tier_1 | tier_2 | tier_3 | baseline
    */
    'patterns' => [
        // Tier 1 — deploy immediately (2.1 grammar: add seeder when ready)
        ['match' => 'Test 2.1 -', 'kind' => 'tier_1', 'label' => 'Tier 1', 'hint' => 'Immediate'],
        ['match' => 'Test 2.7 -', 'kind' => 'tier_1', 'label' => 'Tier 1', 'hint' => 'Immediate'],
        ['match' => 'Test 2.4 -', 'kind' => 'tier_1', 'label' => 'Tier 1', 'hint' => 'Immediate'],
        ['match' => 'Test 2.5 -', 'kind' => 'tier_1', 'label' => 'Tier 1', 'hint' => 'Immediate'],
        ['match' => 'Test 2.8 -', 'kind' => 'tier_1', 'label' => 'Tier 1', 'hint' => 'Immediate'],

        // Tier 2 — role-family hiring
        ['match' => 'Test 3.1.1 -', 'kind' => 'tier_2', 'label' => 'Tier 2', 'hint' => 'Role family'],
        ['match' => 'Test 3.4.1 -', 'kind' => 'tier_2', 'label' => 'Tier 2', 'hint' => 'Role family'],
        ['match' => 'Test 3.2.1 -', 'kind' => 'tier_2', 'label' => 'Tier 2', 'hint' => 'Role family'],
        ['match' => 'Test 3.6.1 -', 'kind' => 'tier_2', 'label' => 'Tier 2', 'hint' => 'Role family'],

        // Tier 3 — as role families scale
        ['match' => 'Test 3.3.1 -', 'kind' => 'tier_3', 'label' => 'Tier 3', 'hint' => 'Scale'],
        ['match' => 'Test 3.5.1 -', 'kind' => 'tier_3', 'label' => 'Tier 3', 'hint' => 'Scale'],
        ['match' => 'Test 3.7.1 -', 'kind' => 'tier_3', 'label' => 'Tier 3', 'hint' => 'Scale'],
        ['match' => 'Test 3.8.1 -', 'kind' => 'tier_3', 'label' => 'Tier 3', 'hint' => 'Scale'],
        ['match' => 'Test 3.9.1 -', 'kind' => 'tier_3', 'label' => 'Tier 3', 'hint' => 'Scale'],

        // Baseline — rolling (2.2 speaking not in DB yet)
        ['match' => 'Test 2.3 -', 'kind' => 'baseline', 'label' => 'Baseline', 'hint' => 'Rolling'],
        ['match' => 'Test 2.6 -', 'kind' => 'baseline', 'label' => 'Baseline', 'hint' => 'Rolling'],
        ['match' => 'Test 2.9 -', 'kind' => 'baseline', 'label' => 'Baseline', 'hint' => 'Rolling'],
        ['match' => 'Test 2.2 -', 'kind' => 'baseline', 'label' => 'Baseline', 'hint' => 'Rolling'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sort weight (lower = higher on list)
    |--------------------------------------------------------------------------
    */
    'sort_weights' => [
        'tier_1' => 10,
        'tier_2' => 20,
        'tier_3' => 30,
        'baseline' => 40,
        'unmapped' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tests referenced in roadmap but not yet in DB seeders (build / backlog)
    |--------------------------------------------------------------------------
    */
    'pending_in_library' => [
        'Test 2.1 — English Grammar and Writing Assessment (not seeded yet)',
        'Test 2.2 — Speaking Assessment (baseline; not seeded yet)',
        'Test 3.2.1 — Customer Support Readiness Assessment (not seeded yet)',
        'Test 3.3.1 — AI and Data Operations Readiness (not seeded yet)',
    ],
];
