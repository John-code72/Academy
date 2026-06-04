@extends('layouts.default')
@push('title', get_phrase('Defrilex onboarding Test'))
@push('meta')@endpush
@push('css')
    <style>
        /* Tailles fortes + #id pour gagner sur le CSS du thème (souvent .entry-title, .text-muted, etc.) */
        #defrilex-onboarding-test-page {
            font-size: 1.35rem !important;
            padding-top: 2.5rem;
            padding-bottom: 4rem;
            min-height: 60vh;
        }

        #defrilex-onboarding-test-page .defrilex-onboarding-hero {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            padding: 3.25rem 2.5rem;
            margin-bottom: 2.5rem;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 45%, #6366f1 100%);
            color: #fff;
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.35);
        }

        #defrilex-onboarding-test-page .defrilex-onboarding-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 45%),
                radial-gradient(circle at 80% 60%, rgba(255, 255, 255, 0.1) 0%, transparent 40%);
            pointer-events: none;
        }

        #defrilex-onboarding-test-page .defrilex-onboarding-hero h1,
        #defrilex-onboarding-test-page .defrilex-onboarding-hero p {
            position: relative;
            z-index: 1;
        }

        #defrilex-onboarding-test-page .hero-badge {
            display: inline-block;
            padding: 0.6rem 1.25rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.2);
            font-size: 1.3rem !important;
            font-weight: 600;
            letter-spacing: 0.04em;
            margin-bottom: 1.1rem;
        }

        #defrilex-onboarding-test-page .pt-hero-title {
            font-size: clamp(2.65rem, 5.5vw, 3.85rem) !important;
            line-height: 1.15 !important;
        }

        #defrilex-onboarding-test-page .pt-hero-lead {
            font-size: 1.65rem !important;
            line-height: 1.55 !important;
        }

        @media (min-width: 768px) {
            #defrilex-onboarding-test-page .pt-hero-lead {
                font-size: 1.85rem !important;
            }
        }

        #defrilex-onboarding-test-page .pt-breadcrumb {
            font-size: 1.45rem !important;
            margin-bottom: 1.5rem;
        }

        #defrilex-onboarding-test-page .pt-breadcrumb a,
        #defrilex-onboarding-test-page .pt-breadcrumb span {
            font-size: inherit !important;
        }

        #defrilex-onboarding-test-page .pt-breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }

        #defrilex-onboarding-test-page .pt-breadcrumb a:hover {
            color: #2563eb;
        }

        #defrilex-onboarding-test-page .pt-card {
            border: 1px solid #e8edf5;
            border-radius: 16px;
            background: #fff;
            height: 100%;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            overflow: hidden;
        }

        #defrilex-onboarding-test-page .pt-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
        }

        #defrilex-onboarding-test-page .pt-card-head {
            height: 165px;
            background: linear-gradient(145deg, #eff6ff 0%, #e0e7ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #e8edf5;
        }

        #defrilex-onboarding-test-page .pt-card-head i {
            font-size: 4.5rem !important;
            color: #2563eb;
            opacity: 0.9;
        }

        #defrilex-onboarding-test-page .pt-card-body {
            padding: 1.85rem 1.65rem 2rem;
        }

        #defrilex-onboarding-test-page h3.pt-card-title.entry-title {
            font-size: 1.95rem !important;
            line-height: 1.28 !important;
            font-weight: 700 !important;
        }

        #defrilex-onboarding-test-page p.pt-card-desc {
            font-size: 1.4rem !important;
            line-height: 1.55 !important;
        }

        #defrilex-onboarding-test-page .pt-badge-onboarding {
            font-size: 1.2rem !important;
            padding: 0.5em 0.95em !important;
        }

        #defrilex-onboarding-test-page a.pt-btn-start.eBtn {
            font-size: 1.5rem !important;
            font-weight: 600 !important;
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }

        #defrilex-onboarding-test-page a.pt-btn-start.eBtn span {
            font-size: inherit !important;
        }

        #defrilex-onboarding-test-page .pt-btn-start .fi {
            font-size: 1.65rem !important;
        }

        #defrilex-onboarding-test-page .pt-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-top: 1rem;
        }

        #defrilex-onboarding-test-page .pt-chip {
            font-size: 1.25rem !important;
            padding: 0.5rem 0.95rem;
            border-radius: 8px;
            background: #f1f5f9;
            color: #475569;
            font-weight: 500;
        }

        #defrilex-onboarding-test-page .pt-chip .fi {
            font-size: 1.3rem !important;
            vertical-align: -0.1em;
        }

        #defrilex-onboarding-test-page .pt-empty {
            text-align: center;
            padding: 4rem 2rem;
            border-radius: 16px;
            border: 2px dashed #cbd5e1;
            background: #f8fafc;
            color: #64748b;
            font-size: 1.45rem !important;
        }

        #defrilex-onboarding-test-page .pt-empty .pt-empty-title {
            font-size: 2.15rem !important;
            font-weight: 700;
            color: #475569;
        }

        #defrilex-onboarding-test-page .pt-empty p {
            font-size: 1.45rem !important;
        }

        #defrilex-onboarding-test-page .pt-empty i {
            font-size: 4.75rem !important;
            color: #94a3b8;
            margin-bottom: 1.35rem;
        }
    </style>
@endpush

@section('content')
    <section id="defrilex-onboarding-test-page" class="defrilex-onboarding-dedicated">
        <div class="container">
            <nav class="pt-breadcrumb" aria-label="breadcrumb">
                <a href="{{ url('/') }}">{{ get_phrase('Home') }}</a>
                <span class="text-muted mx-1">/</span>
                <span class="text-muted">{{ get_phrase('Defrilex onboarding Test') }}</span>
            </nav>

            <div class="defrilex-onboarding-hero">
                <span class="hero-badge">{{ get_phrase('Assessment center') }}</span>
                <h1 class="fw-bold mb-3 pt-hero-title">{{ get_phrase('Defrilex onboarding Test') }}</h1>
                <p class="mb-0 opacity-90 pt-hero-lead" style="max-width: 40rem;">
                    {{ get_phrase('Choose a test to train and track your progress. Unlimited attempts on Defrilex onboarding Test.') }}
                </p>
                @php
                    $heroNote = config('defrilex_test_deployment.onboarding_list_hero_note');
                @endphp
                @if ($heroNote)
                    <p class="mb-0 mt-3 opacity-85 pt-hero-lead" style="max-width: 48rem; font-size: 1.25rem !important;">
                        {{ $heroNote }}
                    </p>
                @endif
            </div>

            @if ($tests->isEmpty())
                <div class="pt-empty">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <h4 class="g-title mb-3 pt-empty-title">{{ get_phrase('No Defrilex onboarding Test yet') }}</h4>
                    <p class="mb-0">{{ get_phrase('Defrilex onboarding Test will appear here when they are available.') }}</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($tests as $test)
                        @php
                            $desc = $test->description ? strip_tags($test->description) : '';
                            $desc = \Illuminate\Support\Str::limit($desc, 140);
                            $duration = $test->duration ? explode(':', $test->duration) : [0, 0, 0];
                        @endphp
                        <div class="col-lg-6 col-xl-4">
                            <div class="pt-card">
                                <div class="pt-card-head">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                                <div class="pt-card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <h3 class="entry-title mb-0 ellipsis-line-2 pt-card-title">
                                            {{ $test->title }}
                                        </h3>
                                        <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                            <span class="badge bg-secondary pt-badge-onboarding">{{ get_phrase('Defrilex onboarding Test') }}</span>
                                            @if (config('defrilex_test_deployment.show_deployment_badges') && ! empty($test->deployment_meta))
                                                @php
                                                    $dm = $test->deployment_meta;
                                                    $badgeClass = \App\Support\DefrilexTestDeployment::badgeClass($dm['kind'] ?? null);
                                                @endphp
                                                <span class="badge {{ $badgeClass }} pt-badge-onboarding" title="{{ trim(($dm['label'] ?? '') . ' — ' . ($dm['hint'] ?? '')) }}">{{ $dm['label'] ?? '' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($desc)
                                        <p class="text-muted mb-0 pt-card-desc">{{ $desc }}</p>
                                    @endif
                                    <div class="pt-meta">
                                        <span class="pt-chip">
                                            <i class="fi fi-rr-clock-five me-1"></i>
                                            {{ ($duration[0] ?? 0) }}h {{ ($duration[1] ?? 0) }}m
                                        </span>
                                        <span class="pt-chip">{{ get_phrase('Questions') }}: {{ $test->questions_count ?? 0 }}</span>
                                        @if ($test->total_mark)
                                            <span class="pt-chip">{{ get_phrase('Points') }}: {{ $test->total_mark }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('practice.test.show', $test->id) }}"
                                            class="eBtn gradient border-0 w-100 d-inline-flex align-items-center justify-content-center gap-2 pt-btn-start">
                                            <span>{{ get_phrase('Start') }}</span>
                                            <i class="fi fi-rr-arrow-small-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
