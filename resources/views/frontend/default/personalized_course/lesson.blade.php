@extends('layouts.default')
@push('title', $lesson->title)

@push('css')
<style>
    .pc-lesson-page {
        --pc-primary: #4f46e5;
        --pc-primary-dark: #3730a3;
        --pc-accent: #06b6d4;
        --pc-text: #0f172a;
        --pc-muted: #64748b;
        --pc-border: #e2e8f0;
        --pc-bg-soft: #f8fafc;
        padding: 2.5rem 0 5rem;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        min-height: 90vh;
    }

    /* ============ HEADER ============ */
    .pc-back {
        margin-bottom: 1.25rem;
    }
    .pc-back a {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        color: var(--pc-muted);
        text-decoration: none;
        font-weight: 500;
        font-size: 1.02rem;
        padding: .6rem 1rem;
        border-radius: 10px;
        background: #fff;
        border: 1px solid var(--pc-border);
        transition: all .2s ease;
    }
    .pc-back a:hover {
        color: var(--pc-primary);
        border-color: rgba(79, 70, 229, .25);
        transform: translateX(-2px);
    }

    /* ============ LAYOUT ============ */
    .pc-lesson-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 280px;
        gap: 1.75rem;
        align-items: start;
    }
    @media (max-width: 991px) {
        .pc-lesson-grid {
            grid-template-columns: 1fr;
        }
        .pc-lesson-sidebar { order: -1; }
    }

    /* ============ MAIN CARD ============ */
    .pc-lesson-card {
        background: #fff;
        border: 1px solid var(--pc-border);
        border-radius: 22px;
        padding: 2.25rem 2.25rem 2rem;
        box-shadow: 0 6px 28px rgba(15, 23, 42, .05);
    }
    .pc-section-name {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: var(--pc-primary);
        background: #eef2ff;
        border: 1px solid rgba(79, 70, 229, .15);
        font-size: .9rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        font-weight: 700;
        padding: .45rem 1rem;
        border-radius: 999px;
        margin-bottom: 1.15rem;
    }
    .pc-lesson-card h1 {
        font-size: 2.25rem;
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: 1.35rem;
        color: var(--pc-text);
        letter-spacing: -.02em;
    }
    .pc-lesson-card h1 .pc-num {
        color: var(--pc-primary);
        margin-right: .45rem;
    }

    /* ============ VIDEO ============ */
    .pc-video {
        margin: 0 0 1rem;
        border-radius: 18px;
        overflow: hidden;
        background: #000;
        position: relative;
        aspect-ratio: 16 / 9;
        box-shadow: 0 18px 40px -12px rgba(15, 23, 42, .35);
    }
    .pc-video iframe, .pc-video video {
        width: 100%;
        height: 100%;
        border: 0;
        display: block;
    }
    .pc-video video { background: #000; }

    .pc-video-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        font-size: 1rem;
        color: var(--pc-muted);
        margin-bottom: 1.5rem;
    }
    .pc-video-meta span { display: inline-flex; align-items: center; gap: .4rem; }
    .pc-video-meta .btn { font-size: .92rem; }

    .pc-video-pending {
        margin: 0 0 1.5rem;
        padding: 1.65rem 1.85rem;
        background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 60%, #06b6d4 100%);
        color: #fff;
        border-radius: 18px;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: 0 20px 40px -12px rgba(79, 102, 241, .35);
    }
    .pc-spinner {
        width: 48px;
        height: 48px;
        border: 4px solid rgba(255,255,255,.25);
        border-top-color: #fff;
        border-radius: 50%;
        animation: pcSpin 1s linear infinite;
        flex-shrink: 0;
    }
    @keyframes pcSpin {
        to { transform: rotate(360deg); }
    }
    .pc-video-pending strong {
        display: block;
        margin-bottom: .25rem;
        font-size: 1.18rem;
    }
    .pc-pending-meta {
        font-size: 1rem;
        opacity: .9;
    }
    .pc-video-failed {
        margin: 0 0 1.5rem;
        padding: 1.25rem 1.5rem;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 16px;
        color: #991b1b;
    }
    .pc-video-fallback {
        margin: 0 0 1.5rem;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #fff7ed 0%, #fef3c7 100%);
        border: 1px solid #fed7aa;
        border-radius: 16px;
    }

    /* ============ CONTENT ============ */
    .pc-content {
        font-size: 1.18rem;
        line-height: 1.75;
        color: #1e293b;
    }
    .pc-content p { margin-bottom: 1.1rem; }
    .pc-content strong { color: var(--pc-text); }
    .pc-content ul, .pc-content ol {
        padding-left: 1.5rem;
        margin-bottom: 1.2rem;
    }
    .pc-content li { margin-bottom: .5rem; }
    .pc-content h2, .pc-content h3 { font-size: 1.5rem; margin: 1.5rem 0 .85rem; font-weight: 700; }
    .pc-content h4, .pc-content h5 { font-size: 1.25rem; margin: 1.25rem 0 .75rem; font-weight: 700; }

    /* ============ KEY POINTS ============ */
    .pc-callout {
        margin-top: 1.75rem;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .pc-callout::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
    }
    .pc-callout-header {
        display: flex;
        align-items: center;
        gap: .65rem;
        font-weight: 700;
        font-size: 1.18rem;
        margin-bottom: .75rem;
    }
    .pc-callout-icon {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .pc-keypoints {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 1px solid #bae6fd;
    }
    .pc-keypoints::before { background: #0ea5e9; }
    .pc-keypoints .pc-callout-header { color: #0369a1; }
    .pc-keypoints .pc-callout-icon { background: #0ea5e9; color: #fff; }
    .pc-keypoints ul {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }
    .pc-keypoints li {
        position: relative;
        padding-left: 1.7rem;
        margin-bottom: .55rem;
        color: #0c4a6e;
        font-size: 1.08rem;
        line-height: 1.6;
    }
    .pc-keypoints li::before {
        content: '\2713';
        position: absolute;
        left: 0;
        top: 0;
        color: #0ea5e9;
        font-weight: 700;
        font-size: 1.15rem;
    }

    .pc-practice {
        background: linear-gradient(135deg, #fef9c3 0%, #fde68a 100%);
        border: 1px solid #fde68a;
    }
    .pc-practice::before { background: #d97706; }
    .pc-practice .pc-callout-header { color: #92400e; }
    .pc-practice .pc-callout-icon { background: #d97706; color: #fff; }
    .pc-practice p { color: #78350f; margin: 0; font-size: 1.08rem; line-height: 1.6; }

    /* ============ NAV ============ */
    .pc-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--pc-border);
    }
    .pc-nav-btn {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .8rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1.05rem;
        text-decoration: none;
        transition: all .2s ease;
        border: 1px solid var(--pc-border);
        background: #fff;
        color: var(--pc-text);
    }
    .pc-nav-btn:hover {
        border-color: rgba(79, 70, 229, .3);
        color: var(--pc-primary);
        transform: translateY(-1px);
        text-decoration: none;
    }
    .pc-nav-btn.pc-nav-primary {
        background: linear-gradient(135deg, var(--pc-primary), var(--pc-accent));
        color: #fff;
        border-color: transparent;
        box-shadow: 0 8px 20px -6px rgba(79, 70, 229, .45);
    }
    .pc-nav-btn.pc-nav-primary:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -6px rgba(79, 70, 229, .6);
    }
    .pc-nav-btn.pc-nav-success {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 8px 20px -6px rgba(16, 185, 129, .45);
    }
    .pc-nav-btn.pc-nav-success:hover { color: #fff; }

    /* ============ SIDEBAR ============ */
    .pc-lesson-sidebar {
        position: sticky;
        top: 1.5rem;
    }
    .pc-sidebar-card {
        background: #fff;
        border: 1px solid var(--pc-border);
        border-radius: 18px;
        padding: 1.25rem;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
    }
    .pc-sidebar-title {
        font-size: .92rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--pc-muted);
        font-weight: 700;
        margin-bottom: .85rem;
        padding-bottom: .75rem;
        border-bottom: 1px solid var(--pc-border);
    }
    .pc-sidebar-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .pc-sidebar-list li { margin-bottom: .4rem; }
    .pc-sidebar-list a {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .65rem .75rem;
        border-radius: 10px;
        color: var(--pc-text);
        text-decoration: none;
        font-size: 1.02rem;
        line-height: 1.4;
        transition: all .15s ease;
    }
    .pc-sidebar-list a:hover {
        background: #f8fafc;
        color: var(--pc-primary);
        text-decoration: none;
    }
    .pc-sidebar-list a.is-active {
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        color: var(--pc-primary-dark);
        font-weight: 600;
    }
    .pc-sidebar-num {
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        border-radius: 9px;
        background: #f1f5f9;
        color: var(--pc-muted);
        font-size: .92rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .pc-sidebar-list a.is-active .pc-sidebar-num {
        background: var(--pc-primary);
        color: #fff;
    }

    .pc-format-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .92rem;
        font-weight: 600;
        padding: .35rem .85rem;
        border-radius: 999px;
        margin-bottom: 1.25rem;
    }
    .pc-format-video { background: #eef2ff; color: #4338ca; }
    .pc-format-audio { background: #ecfeff; color: #0e7490; }
    .pc-format-text { background: #f0fdf4; color: #15803d; }

    .pc-audio-block {
        margin-bottom: 1.75rem;
    }
    .pc-listen-btn { border-radius: 999px; }

    /* ============ CUSTOM AUDIO PLAYER ============ */
    .pc-player {
        --pc-player-accent: #06b6d4;
        --pc-player-accent-2: #4f46e5;
        background: linear-gradient(145deg, #0f172a 0%, #1e293b 45%, #312e81 100%);
        border-radius: 22px;
        padding: 1.35rem 1.5rem 1.25rem;
        box-shadow: 0 20px 50px -12px rgba(79, 70, 229, .45), 0 0 0 1px rgba(255, 255, 255, .06) inset;
        position: relative;
        overflow: hidden;
        color: #fff;
    }
    .pc-player::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -15%;
        width: 55%;
        height: 120%;
        background: radial-gradient(circle, rgba(6, 182, 212, .35) 0%, transparent 70%);
        pointer-events: none;
    }
    .pc-player.is-playing::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(6, 182, 212, .08), transparent);
        animation: pc-player-shimmer 2.5s ease-in-out infinite;
        pointer-events: none;
    }
    @keyframes pc-player-shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    .pc-player-top {
        display: flex;
        align-items: flex-start;
        gap: 1.15rem;
        position: relative;
        z-index: 1;
        margin-bottom: 1.1rem;
    }
    .pc-player-play {
        flex-shrink: 0;
        width: 58px;
        height: 58px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, var(--pc-player-accent), var(--pc-player-accent-2));
        color: #fff;
        font-size: 1.35rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 8px 24px rgba(6, 182, 212, .45);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .pc-player-play:hover {
        transform: scale(1.06);
        box-shadow: 0 12px 28px rgba(6, 182, 212, .55);
    }
    .pc-player.is-playing .pc-player-play {
        animation: pc-player-pulse 2s ease-in-out infinite;
    }
    @keyframes pc-player-pulse {
        0%, 100% { box-shadow: 0 8px 24px rgba(6, 182, 212, .45); }
        50% { box-shadow: 0 8px 32px rgba(79, 70, 229, .65); }
    }
    .pc-player-info { flex: 1; min-width: 0; }
    .pc-player-label {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: rgba(255, 255, 255, .55);
        margin-bottom: .35rem;
    }
    .pc-player-label .pc-ai-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #22d3ee;
        box-shadow: 0 0 8px #22d3ee;
    }
    .pc-player.is-playing .pc-ai-dot {
        animation: pc-dot-blink 1.2s ease-in-out infinite;
    }
    @keyframes pc-dot-blink {
        0%, 100% { opacity: 1; }
        50% { opacity: .35; }
    }
    .pc-player-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #fff;
        margin: 0 0 .65rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pc-player-wave {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 3px;
        height: 28px;
    }
    .pc-player-wave span {
        width: 4px;
        border-radius: 4px;
        background: linear-gradient(180deg, #67e8f9, #818cf8);
        height: 8px;
        opacity: .45;
        transition: height .15s ease;
    }
    .pc-player.is-playing .pc-player-wave span {
        animation: pc-wave 1s ease-in-out infinite;
    }
    .pc-player-wave span:nth-child(1) { animation-delay: 0s; }
    .pc-player-wave span:nth-child(2) { animation-delay: .1s; }
    .pc-player-wave span:nth-child(3) { animation-delay: .2s; }
    .pc-player-wave span:nth-child(4) { animation-delay: .15s; }
    .pc-player-wave span:nth-child(5) { animation-delay: .25s; }
    .pc-player-wave span:nth-child(6) { animation-delay: .05s; }
    .pc-player-wave span:nth-child(7) { animation-delay: .18s; }
    .pc-player-wave span:nth-child(8) { animation-delay: .12s; }
    .pc-player-wave span:nth-child(9) { animation-delay: .22s; }
    .pc-player-wave span:nth-child(10) { animation-delay: .08s; }
    .pc-player-wave span:nth-child(11) { animation-delay: .16s; }
    .pc-player-wave span:nth-child(12) { animation-delay: .28s; }
    @keyframes pc-wave {
        0%, 100% { height: 8px; opacity: .45; }
        50% { height: 26px; opacity: 1; }
    }
    .pc-player-progress-wrap {
        position: relative;
        z-index: 1;
        margin-bottom: .85rem;
    }
    .pc-player-progress {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 6px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .12);
        outline: none;
        cursor: pointer;
    }
    .pc-player-progress::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(6, 182, 212, .35);
        cursor: pointer;
    }
    .pc-player-progress::-moz-range-thumb {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #fff;
        border: none;
        box-shadow: 0 0 0 4px rgba(6, 182, 212, .35);
        cursor: pointer;
    }
    .pc-player-bottom {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        position: relative;
        z-index: 1;
    }
    .pc-player-time {
        font-size: .82rem;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        color: rgba(255, 255, 255, .7);
        letter-spacing: .03em;
    }
    .pc-player-controls {
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .pc-player-btn-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, .15);
        background: rgba(255, 255, 255, .08);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: .95rem;
        transition: background .2s, border-color .2s;
    }
    .pc-player-btn-icon:hover {
        background: rgba(255, 255, 255, .16);
        border-color: rgba(255, 255, 255, .28);
    }
    .pc-player-speed {
        font-size: .78rem;
        font-weight: 700;
        min-width: 2.5rem;
        padding: 0 .25rem;
    }
    .pc-player-volume-wrap {
        display: flex;
        align-items: center;
        gap: .35rem;
    }
    .pc-player-volume {
        -webkit-appearance: none;
        appearance: none;
        width: 72px;
        height: 4px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .15);
        outline: none;
        cursor: pointer;
    }
    .pc-player-volume::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #67e8f9;
        cursor: pointer;
    }
    .pc-player-actions {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-top: .85rem;
        position: relative;
        z-index: 1;
    }
    .pc-player-actions .pc-player-regen {
        font-size: .85rem;
        font-weight: 600;
        color: rgba(255, 255, 255, .75);
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 999px;
        padding: .4rem .95rem;
        cursor: pointer;
        transition: all .2s;
    }
    .pc-player-actions .pc-player-regen:hover {
        color: #fff;
        background: rgba(255, 255, 255, .14);
    }
    .pc-player audio.pc-audio-native {
        position: absolute;
        width: 0;
        height: 0;
        opacity: 0;
        pointer-events: none;
    }
    @media (max-width: 576px) {
        .pc-player { padding: 1.1rem 1.15rem; }
        .pc-player-play { width: 50px; height: 50px; font-size: 1.15rem; }
        .pc-player-volume { width: 56px; }
    }

    /* ============ MOBILE ============ */
    @media (max-width: 576px) {
        .pc-lesson-page { padding: 1.5rem 0 3rem; }
        .pc-lesson-card { padding: 1.5rem 1.25rem; }
        .pc-lesson-card h1 { font-size: 1.65rem; }
        .pc-content { font-size: 1.05rem; line-height: 1.7; }
        .pc-nav-btn { padding: .65rem 1.15rem; font-size: .98rem; }
        .pc-video-pending { padding: 1.25rem; gap: 1rem; }
        .pc-spinner { width: 40px; height: 40px; }
        .pc-keypoints li, .pc-practice p { font-size: 1rem; }
        .pc-callout-header { font-size: 1.08rem; }
    }
</style>
@endpush

@section('content')
<div class="pc-lesson-page">
    <div class="container">

        <div class="pc-back">
            <a href="{{ route('personalized.course.show', ['slug' => $course->slug]) }}">
                <i class="fi fi-rr-angle-small-left"></i>
                {{ get_phrase('Back to course') }}
            </a>
        </div>

        <div class="pc-lesson-grid">
            <div class="pc-lesson-main">
                <div class="pc-lesson-card">
                    @if ($lesson->section_title)
                        <div class="pc-section-name">
                            <i class="fi fi-rr-folder"></i>
                            {{ $lesson->section_title }}
                        </div>
                    @endif
                    <h1><span class="pc-num">{{ $lesson->sort }}.</span>{{ $lesson->title }}</h1>

                    @php
                        $lessonFormat = $lesson->lesson_format
                            ?: ($lesson->video_provider ? 'video' : (($lesson->audio_url || $lesson->audio_status === 'browser') ? 'audio' : 'text'));
                    @endphp

                    <div class="pc-format-badge pc-format-{{ $lessonFormat }}">
                        @if ($lessonFormat === 'video')
                            <i class="fi fi-rr-video-camera"></i> {{ get_phrase('Video lesson') }}
                        @elseif ($lessonFormat === 'audio')
                            <i class="fi fi-rr-volume"></i> {{ get_phrase('Audio lesson') }}
                        @else
                            <i class="fi fi-rr-document"></i> {{ get_phrase('Reading lesson') }}
                        @endif
                    </div>

                    @if ($lessonFormat === 'audio')
                        <div class="pc-audio-block">
                            @if ($lesson->audio_status === 'pending' && !$lesson->audio_url)
                                <div class="pc-video-pending">
                                    <div class="pc-spinner"></div>
                                    <div>
                                        <strong>{{ get_phrase('Generating AI narration...') }}</strong>
                                        <span class="pc-pending-meta">{{ get_phrase('Please wait, this may take up to a minute.') }}</span>
                                    </div>
                                </div>
                            @elseif ($lesson->audio_url)
                                @php
                                    $pcAudioSrc = $lesson->audio_url;
                                    if (preg_match('#/storage/.+#i', $pcAudioSrc, $storageMatch)) {
                                        $pcAudioSrc = url(ltrim($storageMatch[0], '/'));
                                    } elseif ($pcAudioSrc && !preg_match('#^https?://#i', $pcAudioSrc)) {
                                        $pcAudioSrc = url(ltrim($pcAudioSrc, '/'));
                                    }
                                    $pcAudioMime = str_contains(strtolower($lesson->audio_url), '.wav')
                                        ? 'audio/wav'
                                        : 'audio/mpeg';
                                    $pcProviderLabel = match ($lesson->audio_provider) {
                                        'gemini_tts' => 'Gemini AI',
                                        'openai_tts' => 'OpenAI',
                                        default => get_phrase('AI Narration'),
                                    };
                                @endphp
                                <div class="pc-player" id="pc-custom-player">
                                    <audio preload="metadata" class="pc-audio-native" id="pc-audio-element">
                                        <source src="{{ $pcAudioSrc }}" type="{{ $pcAudioMime }}">
                                    </audio>
                                    <div class="pc-player-top">
                                        <button type="button" class="pc-player-play" id="pc-player-play" aria-label="{{ get_phrase('Play') }}">
                                            <i class="fi fi-rr-play" id="pc-player-play-icon"></i>
                                        </button>
                                        <div class="pc-player-info">
                                            <div class="pc-player-label">
                                                <span class="pc-ai-dot"></span>
                                                {{ $pcProviderLabel }}
                                            </div>
                                            <p class="pc-player-title">{{ $lesson->title }}</p>
                                            <div class="pc-player-wave" aria-hidden="true">
                                                @for ($w = 0; $w < 12; $w++)<span></span>@endfor
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pc-player-progress-wrap">
                                        <input type="range" class="pc-player-progress" id="pc-player-seek" value="0" min="0" max="100" step="0.1">
                                    </div>
                                    <div class="pc-player-bottom">
                                        <span class="pc-player-time">
                                            <span id="pc-player-current">0:00</span>
                                            <span style="opacity:.5"> / </span>
                                            <span id="pc-player-duration">0:00</span>
                                        </span>
                                        <div class="pc-player-controls">
                                            <button type="button" class="pc-player-btn-icon pc-player-speed" id="pc-player-speed" title="{{ get_phrase('Playback speed') }}">1×</button>
                                            <div class="pc-player-volume-wrap">
                                                <button type="button" class="pc-player-btn-icon" id="pc-player-mute">
                                                    <i class="fi fi-rr-volume" id="pc-player-volume-icon"></i>
                                                </button>
                                                <input type="range" class="pc-player-volume" id="pc-player-volume" value="100" min="0" max="100">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pc-player-actions">
                                        <span style="font-size:.85rem;color:rgba(255,255,255,.5);">
                                            <i class="fi fi-rr-headphones"></i> {{ get_phrase('Listen while you read below') }}
                                        </span>
                                        <form action="{{ route('personalized.course.lesson.video.regenerate', ['slug' => $course->slug, 'lesson_id' => $lesson->id]) }}" method="POST" class="mb-0 ms-auto">
                                            @csrf
                                            <button type="submit" class="pc-player-regen">
                                                <i class="fi fi-rr-refresh"></i> {{ get_phrase('Regenerate') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @elseif ($lesson->video_script)
                                <div class="pc-audio-browser" id="pc-audio-browser"
                                     data-lang="{{ $course->language ?? app()->getLocale() }}">
                                    <script type="application/json" id="pc-audio-script-json">@json($lesson->video_script)</script>
                                    <button type="button" class="btn btn-primary pc-listen-btn" id="pc-listen-btn">
                                        <i class="fi fi-rr-play"></i>
                                        {{ get_phrase('Listen to this lesson') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary pc-listen-stop d-none" id="pc-listen-stop">
                                        <i class="fi fi-rr-pause"></i>
                                        {{ get_phrase('Stop') }}
                                    </button>
                                    <form action="{{ route('personalized.course.lesson.video.regenerate', ['slug' => $course->slug, 'lesson_id' => $lesson->id]) }}" method="POST" class="mt-2 mb-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="fi fi-rr-magic-wand"></i> {{ get_phrase('Generate AI audio (Gemini)') }}
                                        </button>
                                    </form>
                                    <p class="small text-muted mb-0 mt-2">{{ get_phrase('Or use browser voice with the play button above.') }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($lessonFormat === 'video' && $lesson->video_provider === 'heygen' && $lesson->video_status === 'ready' && $lesson->video_url)
                        @php
                            $pcVideoSrc = $lesson->video_url;
                            if (preg_match('#/storage/.+#i', $pcVideoSrc, $storageMatch)) {
                                $pcVideoSrc = url(ltrim($storageMatch[0], '/'));
                            } elseif ($pcVideoSrc && !preg_match('#^https?://#i', $pcVideoSrc)) {
                                $pcVideoSrc = url(ltrim($pcVideoSrc, '/'));
                            }
                        @endphp
                        <div class="pc-video">
                            <video controls preload="metadata"
                                   @if ($lesson->video_thumbnail) poster="{{ $lesson->video_thumbnail }}" @endif>
                                <source src="{{ $pcVideoSrc }}" type="video/mp4">
                                {{ get_phrase('Your browser does not support the video tag.') }}
                            </video>
                        </div>
                        <div class="pc-video-meta">
                            <span><i class="fi fi-rr-magic-wand"></i> {{ get_phrase('AI presenter video generated for you') }}</span>
                            <form action="{{ route('personalized.course.lesson.video.regenerate', ['slug' => $course->slug, 'lesson_id' => $lesson->id]) }}"
                                  method="POST" class="mb-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="fi fi-rr-refresh"></i>
                                    {{ get_phrase('Regenerate video') }}
                                </button>
                            </form>
                        </div>
                    @elseif ($lessonFormat === 'video' && $lesson->video_provider === 'heygen' && $lesson->video_status === 'pending')
                        <div class="pc-video-pending"
                             id="pc-video-pending"
                             data-status-url="{{ route('personalized.course.lesson.video.status', ['slug' => $course->slug, 'lesson_id' => $lesson->id]) }}">
                            <div class="pc-spinner"></div>
                            <div>
                                <strong>{{ get_phrase('Your AI presenter video is being generated...') }}</strong>
                                <span class="pc-pending-meta">
                                    {{ get_phrase('This usually takes 1 to 3 minutes. The page will refresh automatically.') }}
                                </span>
                            </div>
                        </div>
                    @elseif ($lessonFormat === 'video' && $lesson->video_provider === 'heygen' && $lesson->video_status === 'failed')
                        <div class="pc-video-failed">
                            <strong>{{ get_phrase('AI video generation failed.') }}</strong>
                            @if ($lesson->video_error)
                                <div class="small mt-1">{{ $lesson->video_error }}</div>
                            @endif
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <form action="{{ route('personalized.course.lesson.video.regenerate', ['slug' => $course->slug, 'lesson_id' => $lesson->id]) }}"
                                      method="POST" class="mb-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fi fi-rr-refresh"></i>
                                        {{ get_phrase('Try again') }}
                                    </button>
                                </form>
                                @if ($lesson->video_query)
                                    <a class="btn btn-sm btn-outline-danger"
                                       href="https://www.youtube.com/results?search_query={{ urlencode($lesson->video_query) }}"
                                       target="_blank" rel="noopener">
                                        {{ get_phrase('Search on YouTube instead') }} &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>
                    @elseif ($lessonFormat === 'video' && $lesson->video_embed)
                        <div class="pc-video">
                            <iframe
                                src="{{ $lesson->video_embed }}"
                                title="{{ $lesson->video_title ?? $lesson->title }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                loading="lazy">
                            </iframe>
                        </div>
                        @if ($lesson->video_title)
                            <div class="pc-video-meta">
                                <span><i class="fi fi-brands-youtube"></i> {{ $lesson->video_title }}</span>
                                @if ($lesson->video_provider === 'youtube')
                                    <a href="{{ $lesson->video_url }}" target="_blank" rel="noopener" class="small">
                                        {{ get_phrase('Open on YouTube') }} &rarr;
                                    </a>
                                @endif
                            </div>
                        @endif
                        @if (heygen_is_configured())
                            <div class="pc-video-meta">
                                <form action="{{ route('personalized.course.lesson.video.regenerate', ['slug' => $course->slug, 'lesson_id' => $lesson->id]) }}"
                                      method="POST" class="mb-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fi fi-rr-magic-wand"></i>
                                        {{ get_phrase('Generate AI presenter video instead') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    @elseif ($lessonFormat === 'video' && $lesson->video_url)
                        <div class="pc-video-fallback">
                            <strong>{{ get_phrase('Suggested video') }}</strong>
                            <p class="mb-2 small text-muted">
                                {{ get_phrase('Open a quick search on this topic, or generate an AI presenter video below.') }}
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ $lesson->video_url }}" target="_blank" rel="noopener">
                                    <i class="fi fi-brands-youtube"></i>
                                    {{ get_phrase('Watch on YouTube') }}
                                </a>
                                @if (heygen_is_configured())
                                    <form action="{{ route('personalized.course.lesson.video.regenerate', ['slug' => $course->slug, 'lesson_id' => $lesson->id]) }}"
                                          method="POST" class="mb-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fi fi-rr-magic-wand"></i>
                                            {{ get_phrase('Generate AI presenter video') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @elseif ($lessonFormat === 'video' && heygen_is_configured())
                        <div class="pc-video-fallback">
                            <strong>{{ get_phrase('No video yet') }}</strong>
                            <p class="mb-2 small text-muted">
                                {{ get_phrase('Generate an AI presenter video that will read this lesson aloud.') }}
                            </p>
                            <form action="{{ route('personalized.course.lesson.video.regenerate', ['slug' => $course->slug, 'lesson_id' => $lesson->id]) }}"
                                  method="POST" class="mb-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fi fi-rr-magic-wand"></i>
                                    {{ get_phrase('Generate AI presenter video') }}
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="pc-content">
                        {!! $lesson->content !!}
                    </div>

                    @if (!empty($lesson->key_points))
                        <div class="pc-callout pc-keypoints">
                            <div class="pc-callout-header">
                                <span class="pc-callout-icon"><i class="fi fi-rr-bulb"></i></span>
                                {{ get_phrase('Key takeaways') }}
                            </div>
                            <ul>
                                @foreach ($lesson->key_points as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (!empty($lesson->practice_prompt))
                        <div class="pc-callout pc-practice">
                            <div class="pc-callout-header">
                                <span class="pc-callout-icon"><i class="fi fi-rr-pencil"></i></span>
                                {{ get_phrase('Practice now') }}
                            </div>
                            <p>{{ $lesson->practice_prompt }}</p>
                        </div>
                    @endif

                    <div class="pc-nav">
                        <div>
                            @if ($previous)
                                <a class="pc-nav-btn"
                                   href="{{ route('personalized.course.lesson', ['slug' => $course->slug, 'lesson_id' => $previous->id]) }}">
                                    <i class="fi fi-rr-angle-small-left"></i>
                                    {{ get_phrase('Previous') }}
                                </a>
                            @endif
                        </div>
                        <div>
                            @if ($next)
                                <a class="pc-nav-btn pc-nav-primary"
                                   href="{{ route('personalized.course.lesson', ['slug' => $course->slug, 'lesson_id' => $next->id]) }}">
                                    {{ get_phrase('Next lesson') }}
                                    <i class="fi fi-rr-angle-small-right"></i>
                                </a>
                            @else
                                <a class="pc-nav-btn pc-nav-success"
                                   href="{{ route('personalized.course.show', ['slug' => $course->slug]) }}">
                                    <i class="fi fi-rr-check"></i>
                                    {{ get_phrase('Finish course') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <aside class="pc-lesson-sidebar">
                <div class="pc-sidebar-card">
                    <div class="pc-sidebar-title">{{ get_phrase('Course lessons') }}</div>
                    <ul class="pc-sidebar-list">
                        @foreach ($course->lessons as $sidebarLesson)
                            <li>
                                <a href="{{ route('personalized.course.lesson', ['slug' => $course->slug, 'lesson_id' => $sidebarLesson->id]) }}"
                                   class="{{ $sidebarLesson->id === $lesson->id ? 'is-active' : '' }}">
                                    <span class="pc-sidebar-num">{{ $sidebarLesson->sort }}</span>
                                    <span>{{ $sidebarLesson->title }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>

    </div>
</div>
@endsection

@push('js')
<script>
(function () {
    const node = document.getElementById('pc-video-pending');
    if (!node) return;
    const url = node.getAttribute('data-status-url');
    if (!url) return;

    let attempts = 0;
    const maxAttempts = 60;

    function poll() {
        attempts++;
        fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data) return;
                if (data.status === 'ready' || data.status === 'failed') {
                    window.location.reload();
                    return;
                }
                if (attempts < maxAttempts) {
                    setTimeout(poll, 8000);
                }
            })
            .catch(() => {
                if (attempts < maxAttempts) {
                    setTimeout(poll, 12000);
                }
            });
    }

    setTimeout(poll, 8000);
})();

(function () {
    const player = document.getElementById('pc-custom-player');
    const audio = document.getElementById('pc-audio-element');
    if (!player || !audio) return;

    const playBtn = document.getElementById('pc-player-play');
    const playIcon = document.getElementById('pc-player-play-icon');
    const seek = document.getElementById('pc-player-seek');
    const volume = document.getElementById('pc-player-volume');
    const muteBtn = document.getElementById('pc-player-mute');
    const volumeIcon = document.getElementById('pc-player-volume-icon');
    const speedBtn = document.getElementById('pc-player-speed');
    const currentEl = document.getElementById('pc-player-current');
    const durationEl = document.getElementById('pc-player-duration');

    const speeds = [1, 1.25, 1.5, 1.75, 2];
    const ariaPlay = @json(get_phrase('Play'));
    const ariaPause = @json(get_phrase('Pause'));
    let speedIndex = 0;
    let seeking = false;

    function formatTime(sec) {
        if (!isFinite(sec) || sec < 0) return '0:00';
        const m = Math.floor(sec / 60);
        const s = Math.floor(sec % 60);
        return m + ':' + String(s).padStart(2, '0');
    }

    function setPlaying(playing) {
        player.classList.toggle('is-playing', playing);
        playIcon.className = playing ? 'fi fi-rr-pause' : 'fi fi-rr-play';
        playBtn.setAttribute('aria-label', playing ? ariaPause : ariaPlay);
    }

    function updateProgress() {
        if (seeking || !audio.duration) return;
        seek.value = (audio.currentTime / audio.duration) * 100;
        currentEl.textContent = formatTime(audio.currentTime);
    }

    playBtn.addEventListener('click', function () {
        if (audio.paused) {
            audio.play().then(function () { setPlaying(true); }).catch(function () {});
        } else {
            audio.pause();
            setPlaying(false);
        }
    });

    audio.addEventListener('loadedmetadata', function () {
        durationEl.textContent = formatTime(audio.duration);
        seek.max = 100;
    });

    audio.addEventListener('timeupdate', updateProgress);

    audio.addEventListener('ended', function () {
        setPlaying(false);
        seek.value = 0;
        currentEl.textContent = '0:00';
    });

    seek.addEventListener('input', function () {
        seeking = true;
        if (audio.duration) {
            currentEl.textContent = formatTime((seek.value / 100) * audio.duration);
        }
    });

    seek.addEventListener('change', function () {
        if (audio.duration) {
            audio.currentTime = (seek.value / 100) * audio.duration;
        }
        seeking = false;
    });

    volume.addEventListener('input', function () {
        audio.volume = volume.value / 100;
        audio.muted = volume.value === '0';
        volumeIcon.className = audio.muted || audio.volume === 0 ? 'fi fi-rr-volume-mute' : 'fi fi-rr-volume';
    });

    muteBtn.addEventListener('click', function () {
        audio.muted = !audio.muted;
        if (!audio.muted && audio.volume === 0) {
            audio.volume = 1;
            volume.value = 100;
        }
        volumeIcon.className = audio.muted ? 'fi fi-rr-volume-mute' : 'fi fi-rr-volume';
    });

    speedBtn.addEventListener('click', function () {
        speedIndex = (speedIndex + 1) % speeds.length;
        audio.playbackRate = speeds[speedIndex];
        speedBtn.textContent = speeds[speedIndex] + '×';
    });

    audio.volume = 1;
})();

(function () {
    const block = document.getElementById('pc-audio-browser');
    if (!block || !window.speechSynthesis) return;

    const jsonEl = document.getElementById('pc-audio-script-json');
    let script = '';
    if (jsonEl) {
        try { script = JSON.parse(jsonEl.textContent || '""'); } catch (e) { script = ''; }
    }
    if (!script) return;

    const playBtn = document.getElementById('pc-listen-btn');
    const stopBtn = document.getElementById('pc-listen-stop');
    let utterance = null;

    function speechLang() {
        const raw = (block.getAttribute('data-lang') || document.documentElement.lang || 'en').toLowerCase();
        if (raw.includes('french') || raw === 'fr' || raw.startsWith('fr')) return 'fr-FR';
        if (raw.includes('spanish') || raw === 'es' || raw.startsWith('es')) return 'es-ES';
        return 'en-US';
    }

    function speak() {
        window.speechSynthesis.cancel();
        utterance = new SpeechSynthesisUtterance(script);
        utterance.lang = speechLang();
        utterance.rate = 0.95;
        const voices = window.speechSynthesis.getVoices();
        const match = voices.find(v => v.lang && v.lang.toLowerCase().startsWith(utterance.lang.slice(0, 2)));
        if (match) utterance.voice = match;
        utterance.onend = function () {
            playBtn.classList.remove('d-none');
            stopBtn.classList.add('d-none');
        };
        window.speechSynthesis.speak(utterance);
        playBtn.classList.add('d-none');
        stopBtn.classList.remove('d-none');
    }

    if (window.speechSynthesis.getVoices().length === 0) {
        window.speechSynthesis.addEventListener('voiceschanged', function () {}, { once: true });
    }

    playBtn.addEventListener('click', speak);
    stopBtn.addEventListener('click', function () {
        window.speechSynthesis.cancel();
        playBtn.classList.remove('d-none');
        stopBtn.classList.add('d-none');
    });
})();
</script>
@endpush
