<?php

return [

    'enabled' => env('PEOPLESEEK_INGEST_ENABLED', false),

    // dry_run is the default safe mode; production requires legal_status=allowed on each source.
    'dry_run' => env('PEOPLESEEK_DRY_RUN', true),

    'mode' => env('PEOPLESEEK_INGEST_MODE', 'dry_run'),

    'verify_ssl' => env('PEOPLESEEK_VERIFY_SSL', true),

    'user_agent' => env('PEOPLESEEK_USER_AGENT', 'PeopleSeek-SourceDiscovery/1.0 (dry-run)'),

    'timeout' => (int) env('PEOPLESEEK_TIMEOUT', 45),

    'rate_limit_ms' => (int) env('PEOPLESEEK_RATE_LIMIT_MS', 3000),

    'storage_disk' => env('PEOPLESEEK_STORAGE_DISK', 'local'),

    'storage_path' => env('PEOPLESEEK_STORAGE_PATH', 'peopleseek-sources'),

    'allowed_hosts' => [
        'findinterpreters.courts.state.mn.us',
        'www.ilnd.uscourts.gov',
        'ilnd.uscourts.gov',
        'www.supremecourt.ohio.gov',
        'supremecourt.ohio.gov',
        'www.coloradojudicial.gov',
        'coloradojudicial.gov',
        'languageaccess.courts.ca.gov',
    ],

    'language_aliases' => [
        'haitian_creole' => 'HAITIAN CREOLE FRENCH',
        'kreyol' => 'HAITIAN CREOLE FRENCH',
        'somali' => 'SOMALI',
        'karen' => "KAREN S'GAW",
        'karen_sgaw' => "KAREN S'GAW",
        'dari' => 'AFGHAN PERSIAN/DARI',
        'pashto' => 'PASHTO, CENTRAL',
        'burmese' => 'BURMESE',
        'tigrinya' => 'TIGRINYA',
        'amharic' => 'AMHARIC',
        'nepali' => 'NEPALI',
        'khmer' => 'KHMER, CENTRAL',
        'spanish' => 'SPANISH',
        'mandarin' => 'CHINESE, MANDARIN',
        'arabic' => 'ARABIC - STANDARD',
        'french' => 'FRENCH',
        'vietnamese' => 'VIETNAMESE',
        'russian' => 'RUSSIAN',
        'korean' => 'KOREAN',
        'portuguese' => 'PORTUGUESE',
        'cantonese' => 'CHINESE, CANTONESE',
        'ukrainian' => 'UKRAINIAN',
        'tagalog' => 'TAGALOG',
        'farsi' => 'IRANIAN PERSIAN/FARSI',
        'punjabi' => 'PUNJABI, WESTERN',
        'hindi' => 'HINDI',
        'urdu' => 'URDU',
    ],

    'sources' => [

        'us_mn_court_roster' => [
            'name' => 'Minnesota Court Interpreter Roster',
            'url' => 'https://findinterpreters.courts.state.mn.us/',
            'country_region' => 'US-MN',
            'source_category' => 'state_court_roster',
            'adapter' => 'mn_court_roster',
            'access_method' => 'public_directory',
            'legal_status' => 'allowed',
            'compliance_risk' => 'low',
            'priority_tier' => 'Tier 1',
            'rate_limit_ms' => 3000,
            'terms_notes' => 'Public MN Judicial Branch interpreter roster for court lookup.',
            'target_languages' => [
                'haitian_creole', 'somali', 'karen', 'dari', 'pashto', 'burmese',
                'tigrinya', 'amharic', 'nepali', 'khmer', 'spanish', 'mandarin',
            ],
        ],

        'us_federal_fcci_prr' => [
            'name' => 'Federal FCCI Public Release Roster',
            'url' => 'https://www.ilnd.uscourts.gov/_assets/_documents/_forms/_public/National_Roster_ofCourt_Interpreters.pdf',
            'country_region' => 'US-Federal',
            'source_category' => 'federal_court_roster',
            'adapter' => 'federal_fcci_prr_pdf',
            'access_method' => 'bulk_download',
            'legal_status' => 'allowed',
            'compliance_risk' => 'low',
            'priority_tier' => 'Tier 1',
            'rate_limit_ms' => 5000,
            'terms_notes' => 'Released under 28 U.S.C. § 1827(c)(1). Haitian Creole, Spanish, Navajo FCCI lists.',
            'target_languages' => ['haitian_creole', 'spanish'],
        ],

    ],

];
