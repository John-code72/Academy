<div id="gemini-coach-panel" class="gemini-coach-panel">
    <div class="gemini-coach-compact-bar">
        <label class="gemini-coach-field-label" for="gemini-coach-track">{{ get_phrase('Learning path') }}</label>
        <div class="gemini-coach-compact-row">
            <select id="gemini-coach-track" class="gemini-coach-select gemini-coach-select-compact" aria-label="{{ get_phrase('Learning path') }}">
                @foreach ($coachingTracks ?? [] as $track)
                    <option value="{{ $track['slug'] }}" @selected(($defaultTrack ?? '') === $track['slug'])>{{ $track['name'] }} · {{ $track['total_steps'] }} {{ get_phrase('steps') }}</option>
                @endforeach
            </select>
            <span id="gemini-coach-step-label" class="gemini-coach-badge gemini-coach-badge-step">{{ get_phrase('Step') }} 0/0</span>
            <span id="gemini-coach-percent" class="gemini-coach-badge gemini-coach-badge-percent">0%</span>
            <button type="button" id="gemini-coach-panel-toggle" class="gemini-coach-panel-toggle" aria-expanded="false" title="{{ get_phrase('Path details') }}">
                <i class="fi-rr-angle-small-down"></i>
            </button>
        </div>
    </div>

    <div id="gemini-coach-progress-wrap" class="gemini-coach-progress-wrap">
        <div class="gemini-coach-progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            <div id="gemini-coach-progress-fill" class="gemini-coach-progress-fill" style="width:0%"></div>
        </div>
    </div>

    <div id="gemini-coach-panel-body" class="gemini-coach-panel-body">
        <p id="gemini-coach-current-step" class="gemini-coach-current-step"></p>
        <p id="gemini-coach-module-label" class="gemini-coach-module-label"></p>

        <div class="gemini-coach-actions">
            <button type="button" id="gemini-coach-start" class="gemini-coach-btn gemini-coach-btn-primary">{{ get_phrase('Start path') }}</button>
            <button type="button" id="gemini-coach-continue" class="gemini-coach-btn" hidden>{{ get_phrase('Continue') }}</button>
            <button type="button" id="gemini-coach-next" class="gemini-coach-btn" hidden>{{ get_phrase('Next step') }}</button>
            <button type="button" id="gemini-coach-restart" class="gemini-coach-btn gemini-coach-btn-muted" hidden>{{ get_phrase('Restart') }}</button>
        </div>

        <div class="gemini-coach-curriculum-wrap">
            <button type="button" id="gemini-coach-curriculum-toggle" class="gemini-coach-curriculum-toggle" aria-expanded="false">
                <span>{{ get_phrase('Curriculum') }}</span>
                <i class="fi-rr-angle-small-down gemini-coach-curriculum-chevron"></i>
            </button>
            <div id="gemini-coach-curriculum" class="gemini-coach-curriculum">
                <p id="gemini-coach-curriculum-desc" class="gemini-coach-curriculum-desc"></p>
                <ul id="gemini-coach-curriculum-list" class="gemini-coach-curriculum-list"></ul>
            </div>
        </div>
    </div>
</div>
