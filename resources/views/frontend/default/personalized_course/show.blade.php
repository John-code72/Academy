@extends('layouts.default')
@push('title', $course->title)

@push('css')
<style>
    .pc-show {
        --pc-primary: #4f46e5;
        --pc-primary-dark: #3730a3;
        --pc-accent: #06b6d4;
        --pc-surface: #ffffff;
        --pc-text: #0f172a;
        --pc-muted: #64748b;
        --pc-border: #e2e8f0;
        --pc-bg-soft: #f8fafc;
        padding: 2.5rem 0 5rem;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        min-height: 80vh;
    }

    /* ============ HERO ============ */
    .pc-hero {
        position: relative;
        background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 45%, #06b6d4 100%);
        color: #fff;
        border-radius: 28px;
        padding: 3rem 2.5rem;
        margin-bottom: 2.5rem;
        box-shadow: 0 30px 60px -20px rgba(79, 70, 229, .45);
        overflow: hidden;
    }
    .pc-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 15% 20%, rgba(255,255,255,.18) 0%, transparent 45%),
            radial-gradient(circle at 85% 80%, rgba(6,182,212,.35) 0%, transparent 50%);
        pointer-events: none;
    }
    .pc-hero::after {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        background: rgba(255, 255, 255, .08);
        border-radius: 50%;
        pointer-events: none;
    }
    .pc-hero > * { position: relative; z-index: 1; }

    .pc-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,.25);
        padding: .45rem 1rem;
        border-radius: 999px;
        font-size: .95rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: 1.1rem;
    }
    .pc-hero h1 {
        font-size: 2.65rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 1rem;
        letter-spacing: -.02em;
    }
    .pc-hero-summary {
        font-size: 1.22rem;
        opacity: .94;
        max-width: 760px;
        line-height: 1.65;
        margin-bottom: 1.85rem;
    }
    .pc-hero-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .pc-stat {
        background: rgba(255,255,255,.13);
        border: 1px solid rgba(255,255,255,.2);
        backdrop-filter: blur(10px);
        padding: 1rem 1.4rem;
        border-radius: 14px;
        min-width: 130px;
    }
    .pc-stat .pc-stat-value {
        font-size: 1.85rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: .25rem;
    }
    .pc-stat .pc-stat-label {
        font-size: .92rem;
        opacity: .88;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    /* ============ TOPICS ============ */
    .pc-topics-card {
        background: #fff;
        border: 1px solid var(--pc-border);
        border-radius: 18px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 2.25rem;
        box-shadow: 0 4px 20px rgba(15, 23, 42, .04);
    }
    .pc-topics-label {
        font-size: .95rem;
        color: var(--pc-muted);
        text-transform: uppercase;
        letter-spacing: .07em;
        font-weight: 700;
        margin-bottom: .75rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    .pc-topic-chip {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #eef2ff 0%, #ddd6fe 100%);
        color: var(--pc-primary-dark);
        padding: .5rem 1rem;
        border-radius: 999px;
        font-size: 1rem;
        font-weight: 600;
        margin: 0 .4rem .4rem 0;
        border: 1px solid rgba(79, 70, 229, .15);
    }

    /* ============ SECTION ============ */
    .pc-section {
        margin-bottom: 2rem;
    }
    .pc-section-header {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: 1rem;
    }
    .pc-section-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--pc-primary), var(--pc-accent));
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 8px 20px -6px rgba(79, 70, 229, .5);
    }
    .pc-section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--pc-text);
        margin: 0;
        letter-spacing: -.01em;
    }
    .pc-section-subtitle {
        color: var(--pc-muted);
        font-size: .98rem;
        margin-top: .15rem;
    }

    /* ============ LESSON CARD ============ */
    .pc-lessons-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: .85rem;
    }
    .pc-lesson-card {
        position: relative;
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        border-radius: 16px;
        padding: 1.1rem 1.35rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        text-decoration: none;
        color: inherit;
        transition: all .22s cubic-bezier(.4, 0, .2, 1);
        overflow: hidden;
    }
    .pc-lesson-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--pc-primary), var(--pc-accent));
        opacity: 0;
        transition: opacity .22s ease;
    }
    .pc-lesson-card:hover {
        transform: translateY(-3px);
        border-color: rgba(79, 70, 229, .3);
        box-shadow: 0 18px 36px -12px rgba(79, 70, 229, .25);
        color: inherit;
        text-decoration: none;
    }
    .pc-lesson-card:hover::before {
        opacity: 1;
    }
    .pc-lesson-num {
        flex-shrink: 0;
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        color: var(--pc-primary-dark);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.25rem;
        border: 1px solid rgba(79, 70, 229, .15);
        transition: all .22s ease;
    }
    .pc-lesson-card:hover .pc-lesson-num {
        background: linear-gradient(135deg, var(--pc-primary), var(--pc-accent));
        color: #fff;
        border-color: transparent;
    }
    .pc-lesson-body {
        flex: 1;
        min-width: 0;
    }
    .pc-lesson-title {
        font-weight: 600;
        font-size: 1.18rem;
        color: var(--pc-text);
        margin: 0 0 .25rem;
        line-height: 1.4;
    }
    .pc-lesson-hint {
        font-size: .95rem;
        color: var(--pc-muted);
        display: flex;
        align-items: center;
        gap: .45rem;
    }
    .pc-lesson-arrow {
        flex-shrink: 0;
        color: var(--pc-muted);
        font-size: 1.4rem;
        transition: all .22s ease;
    }
    .pc-lesson-card:hover .pc-lesson-arrow {
        color: var(--pc-primary);
        transform: translateX(4px);
    }

    /* ============ MOBILE ============ */
    @media (max-width: 576px) {
        .pc-show { padding: 1.5rem 0 3rem; }
        .pc-hero {
            padding: 2rem 1.5rem;
            border-radius: 22px;
        }
        .pc-hero h1 { font-size: 1.9rem; }
        .pc-hero-summary { font-size: 1.05rem; }
        .pc-stat { min-width: 100px; padding: .75rem 1rem; }
        .pc-stat .pc-stat-value { font-size: 1.4rem; }
        .pc-stat .pc-stat-label { font-size: .82rem; }
        .pc-lesson-card { padding: 1rem 1.1rem; }
        .pc-lesson-num { width: 44px; height: 44px; font-size: 1.05rem; }
        .pc-lesson-title { font-size: 1.05rem; }
        .pc-lesson-hint { font-size: .88rem; }
        .pc-section-title { font-size: 1.15rem; }
    }
</style>
@endpush

@section('content')
<div class="pc-show">
    <div class="container">

        <div class="pc-hero">
            <span class="pc-hero-badge">
                <i class="fi fi-rr-bulb"></i>
                {{ get_phrase('AI-personalized course') }}
            </span>
            <h1>{{ $course->title }}</h1>
            @if ($course->summary)
                <p class="pc-hero-summary">{{ $course->summary }}</p>
            @endif
            <div class="pc-hero-stats">
                <div class="pc-stat">
                    <div class="pc-stat-value">{{ $course->lessons->count() }}</div>
                    <div class="pc-stat-label">{{ get_phrase('Lessons') }}</div>
                </div>
                <div class="pc-stat">
                    <div class="pc-stat-value">{{ ucfirst($course->level ?? 'n/a') }}</div>
                    <div class="pc-stat-label">{{ get_phrase('Level') }}</div>
                </div>
                @if (!empty($course->weak_topics))
                    <div class="pc-stat">
                        <div class="pc-stat-value">{{ count($course->weak_topics) }}</div>
                        <div class="pc-stat-label">{{ get_phrase('Focus topics') }}</div>
                    </div>
                @endif
                <div class="pc-stat">
                    <div class="pc-stat-value">{{ strtoupper($course->language ?? 'EN') }}</div>
                    <div class="pc-stat-label">{{ get_phrase('Language') }}</div>
                </div>
            </div>
        </div>

        @if (!empty($course->weak_topics))
            <div class="pc-topics-card">
                <div class="pc-topics-label">
                    <i class="fi fi-rr-target"></i>
                    {{ get_phrase('Focus topics from your last attempt') }}
                </div>
                <div>
                    @foreach ($course->weak_topics as $topic)
                        <span class="pc-topic-chip">{{ $topic }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @php
            $grouped = $course->lessons->groupBy(fn ($l) => $l->section_title ?: get_phrase('Lessons'));
            $sectionIndex = 0;
        @endphp

        @foreach ($grouped as $sectionTitle => $lessons)
            @php $sectionIndex++; @endphp
            <div class="pc-section">
                <div class="pc-section-header">
                    <div class="pc-section-icon">{{ $sectionIndex }}</div>
                    <div>
                        <h4 class="pc-section-title">{{ $sectionTitle }}</h4>
                        <div class="pc-section-subtitle">
                            {{ $lessons->count() }} {{ $lessons->count() > 1 ? get_phrase('lessons') : get_phrase('lesson') }}
                        </div>
                    </div>
                </div>

                <div class="pc-lessons-grid">
                    @foreach ($lessons as $lesson)
                        <a class="pc-lesson-card"
                           href="{{ route('personalized.course.lesson', ['slug' => $course->slug, 'lesson_id' => $lesson->id]) }}">
                            <div class="pc-lesson-num">{{ $lesson->sort }}</div>
                            <div class="pc-lesson-body">
                                <div class="pc-lesson-title">{{ $lesson->title }}</div>
                                <div class="pc-lesson-hint">
                                    @php
                                        $fmt = $lesson->lesson_format ?: ($lesson->video_provider ? 'video' : (($lesson->audio_url || $lesson->audio_status === 'browser') ? 'audio' : 'text'));
                                    @endphp
                                    @if ($fmt === 'video')
                                        <i class="fi fi-rr-video-camera"></i>
                                        @if ($lesson->video_provider === 'heygen' && $lesson->video_status === 'ready')
                                            {{ get_phrase('Video ready') }}
                                        @elseif ($lesson->video_provider === 'heygen' && $lesson->video_status === 'pending')
                                            {{ get_phrase('Video generating...') }}
                                        @elseif ($lesson->video_embed)
                                            {{ get_phrase('Video + text') }}
                                        @else
                                            {{ get_phrase('Video lesson') }}
                                        @endif
                                    @elseif ($fmt === 'audio')
                                        <i class="fi fi-rr-volume"></i>
                                        {{ $lesson->audio_url ? get_phrase('Audio + text') : get_phrase('Listen + read') }}
                                    @else
                                        <i class="fi fi-rr-document"></i>
                                        {{ get_phrase('Reading lesson') }}
                                    @endif
                                </div>
                            </div>
                            <i class="fi fi-rr-angle-small-right pc-lesson-arrow"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

    </div>
</div>
@endsection
