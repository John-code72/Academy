@extends('layouts.default')
@push('title', get_phrase('My personalized courses'))

@push('css')
<style>
    .pc-index {
        --pc-primary: #4f46e5;
        --pc-primary-dark: #3730a3;
        --pc-accent: #06b6d4;
        --pc-text: #0f172a;
        --pc-muted: #64748b;
        --pc-border: #e2e8f0;
        padding: 2.5rem 0 5rem;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        min-height: 80vh;
    }
    .pc-index-header {
        margin-bottom: 2.25rem;
    }
    .pc-index-header h2 {
        font-size: 2.4rem;
        font-weight: 800;
        color: var(--pc-text);
        letter-spacing: -.02em;
        margin-bottom: .45rem;
    }
    .pc-index-header p {
        color: var(--pc-muted);
        font-size: 1.15rem;
        max-width: 760px;
        margin: 0;
        line-height: 1.55;
    }

    .pc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.25rem;
    }
    .pc-card {
        background: #fff;
        border: 1px solid var(--pc-border);
        border-radius: 20px;
        padding: 1.5rem;
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        transition: all .25s cubic-bezier(.4, 0, .2, 1);
        position: relative;
        overflow: hidden;
    }
    .pc-card::before {
        content: '';
        position: absolute;
        left: 0; right: 0; top: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pc-primary), var(--pc-accent));
        opacity: 0;
        transition: opacity .25s ease;
    }
    .pc-card:hover {
        transform: translateY(-4px);
        border-color: rgba(79, 70, 229, .3);
        box-shadow: 0 20px 40px -16px rgba(79, 70, 229, .3);
        color: inherit;
        text-decoration: none;
    }
    .pc-card:hover::before { opacity: 1; }

    .pc-card-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        color: var(--pc-primary-dark);
        font-size: .85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        padding: .4rem .85rem;
        border-radius: 999px;
        margin-bottom: 1.1rem;
        align-self: flex-start;
        border: 1px solid rgba(79, 70, 229, .15);
    }
    .pc-card h3 {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.35;
        color: var(--pc-text);
        margin: 0 0 .85rem;
        letter-spacing: -.01em;
    }
    .pc-card-summary {
        color: var(--pc-muted);
        font-size: 1.05rem;
        line-height: 1.6;
        margin: 0 0 1.35rem;
        flex: 1;
    }
    .pc-card-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .65rem;
        padding-top: 1.1rem;
        border-top: 1px solid var(--pc-border);
        font-size: .98rem;
        color: var(--pc-muted);
    }
    .pc-card-meta .pc-pill {
        background: #f1f5f9;
        padding: .35rem .8rem;
        border-radius: 999px;
        font-weight: 600;
        color: var(--pc-text);
        font-size: .92rem;
    }
    .pc-card-meta .pc-fallback-tag {
        background: #fef3c7;
        color: #92400e;
    }

    .pc-empty {
        background: #fff;
        border: 1px dashed #c7d2fe;
        border-radius: 20px;
        padding: 3rem 2rem;
        text-align: center;
        color: var(--pc-muted);
    }
    .pc-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        color: var(--pc-primary);
        font-size: 1.6rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }
    .pc-empty h5 {
        color: var(--pc-text);
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: .6rem;
    }
    .pc-empty p {
        font-size: 1.05rem;
        line-height: 1.55;
    }

    @media (max-width: 576px) {
        .pc-index { padding: 1.5rem 0 3rem; }
        .pc-index-header h2 { font-size: 1.8rem; }
        .pc-index-header p { font-size: 1rem; }
        .pc-card { padding: 1.25rem; }
        .pc-card h3 { font-size: 1.2rem; }
        .pc-card-summary { font-size: .98rem; }
    }
</style>
@endpush

@section('content')
<div class="pc-index">
    <div class="container">

        <div class="pc-index-header">
            <h2>{{ get_phrase('My personalized courses') }}</h2>
            <p>{{ get_phrase('Courses generated automatically from your quiz results to help you improve faster.') }}</p>
        </div>

        @if ($courses->isEmpty())
            <div class="pc-empty">
                <div class="pc-empty-icon"><i class="fi fi-rr-bulb"></i></div>
                <h5>{{ get_phrase('No personalized course yet') }}</h5>
                <p class="mb-0">
                    {{ get_phrase('Take or review a quiz, then click "Generate a personalized course" on the result page.') }}
                </p>
            </div>
        @else
            <div class="pc-grid">
                @foreach ($courses as $course)
                    <a class="pc-card" href="{{ route('personalized.course.show', ['slug' => $course->slug]) }}">
                        <span class="pc-card-badge">
                            <i class="fi fi-rr-bulb"></i>
                            {{ get_phrase('AI-personalized') }}
                        </span>
                        <h3>{{ $course->title }}</h3>
                        @if ($course->summary)
                            <p class="pc-card-summary">{{ \Illuminate\Support\Str::limit($course->summary, 160) }}</p>
                        @endif
                        <div class="pc-card-meta">
                            <span class="pc-pill">{{ $course->lessons_count }} {{ get_phrase('lessons') }}</span>
                            <span class="pc-pill">{{ ucfirst($course->level ?? 'n/a') }}</span>
                            <span>{{ $course->created_at->diffForHumans() }}</span>
                            @if ($course->source === 'fallback')
                                <span class="pc-pill pc-fallback-tag">{{ get_phrase('Template') }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
