@extends('layouts.admin')
@push('title', get_phrase('Open Ai Settings'))
@push('meta')@endpush
@push('css')@endpush
@section('content')
    <!-- Mani section header and breadcrumb -->
    <div class="ol-card radius-8px">
        <div class="ol-card-body my-3 py-4 px-20px">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <h4 class="title fs-16px">
                    <i class="fi-rr-settings-sliders me-2"></i>
                    {{ get_phrase('Open AI Settings') }}
                </h4>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-8">
            <div class="ol-card p-4">
                <h3 class="title text-14px mb-3">{{ get_phrase('Manage your open ai settings') }}</h3>
                <div class="ol-card-body">
                    <form action="{{ route('admin.open.ai.settings.update') }}" method="post">
                        @csrf

                        <h5 class="mb-2">{{ get_phrase('AI provider for personalized courses') }}</h5>
                        <p class="text-muted small mb-3">
                            {{ get_phrase('Choose which AI is used to generate personalized course content and video scripts. OpenAI uses the key below. DeepSeek uses the dedicated DeepSeek key. Both expose the same chat API.') }}
                        </p>

                        <div class="mb-3">
                            <label class="form-label ol-form-label" for="ai_provider">{{ get_phrase('Provider') }}</label>
                            <select class="ol-form-control ol-select2" name="ai_provider" id="ai_provider">
                                <option value="openai" @if (get_settings('ai_provider') !== 'deepseek') selected @endif>OpenAI</option>
                                <option value="deepseek" @if (get_settings('ai_provider') === 'deepseek') selected @endif>DeepSeek</option>
                            </select>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">{{ get_phrase('OpenAI') }}</h5>

                        <div class="mb-3">
                            <label class="form-label ol-form-label" for="">{{ get_phrase('Select ai model') }}</label>
                            <select class="ol-form-control ol-select2" name="open_ai_model">
                                <option value="gpt-3.5-turbo-0125" @if (get_settings('open_ai_model') == 'gpt-3.5-turbo-0125') selected @endif>gpt-3.5-turbo-0125</option>
                                <option value="gpt-4-0125-preview" @if (get_settings('open_ai_model') == 'gpt-4-0125-preview') selected @endif>gpt-4-0125-preview ({{ get_phrase('Required premium account') }})
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label ol-form-label" for="open_ai_max_token">{{ get_phrase('Max tokens') }}</label>
                            <input class="form-control ol-form-control" type="number" id="open_ai_max_token" value="{{ get_settings('open_ai_max_token') }}" name="open_ai_max_token" min="20"
                                max="2048" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label ol-form-label" for="ai_secret_key">{{ get_phrase('Secret key') }}</label>
                            <input class="form-control ol-form-control" type="text" id="open_ai_secret_key" value="{{ get_settings('open_ai_secret_key') }}" name="open_ai_secret_key"
                                required="">
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-2">{{ get_phrase('DeepSeek') }}</h5>
                        <p class="text-muted small mb-3">
                            {{ get_phrase('DeepSeek is OpenAI-compatible and usually much cheaper. Used only when the provider above is set to DeepSeek.') }}
                            <a href="https://platform.deepseek.com/api_keys" target="_blank" rel="noopener">{{ get_phrase('Get your key') }}</a>
                        </p>

                        <div class="mb-3">
                            <label class="form-label ol-form-label" for="deepseek_api_key">{{ get_phrase('DeepSeek API key') }}</label>
                            <input class="form-control ol-form-control" type="text" id="deepseek_api_key" name="deepseek_api_key"
                                value="{{ get_settings('deepseek_api_key') }}" placeholder="sk-...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label ol-form-label" for="deepseek_model">{{ get_phrase('DeepSeek model') }}</label>
                            <select class="ol-form-control ol-select2" name="deepseek_model" id="deepseek_model">
                                <option value="deepseek-chat" @if (get_settings('deepseek_model') !== 'deepseek-reasoner') selected @endif>deepseek-chat ({{ get_phrase('fast, recommended') }})</option>
                                <option value="deepseek-reasoner" @if (get_settings('deepseek_model') === 'deepseek-reasoner') selected @endif>deepseek-reasoner ({{ get_phrase('slower, deeper reasoning') }})</option>
                            </select>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-2">{{ get_phrase('YouTube (video search for personalized lessons)') }}</h5>
                        <p class="text-muted small mb-3">
                            {{ get_phrase('Used to find a teaching video on YouTube for each AI-generated lesson. Leave empty to fallback to a YouTube search link.') }}
                        </p>

                        <div class="mb-3">
                            <label class="form-label ol-form-label" for="youtube_api_key">{{ get_phrase('YouTube Data API v3 key') }}</label>
                            <input class="form-control ol-form-control" type="text" id="youtube_api_key" name="youtube_api_key"
                                value="{{ get_settings('youtube_api_key') }}" placeholder="AIza...">
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-2">{{ get_phrase('HeyGen (AI presenter video)') }}</h5>
                        <p class="text-muted small mb-3">
                            {{ get_phrase('Used for personalized courses and main courses (Admin → Curriculum → Generate with AI). Takes priority over YouTube.') }}
                            <a href="https://app.heygen.com/settings?nav=API" target="_blank" rel="noopener">{{ get_phrase('Get your key') }}</a>
                        </p>

                        <div class="mb-3">
                            <label class="form-label ol-form-label" for="heygen_api_key">{{ get_phrase('HeyGen API key') }}</label>
                            <input class="form-control ol-form-control" type="text" id="heygen_api_key" name="heygen_api_key"
                                value="{{ get_settings('heygen_api_key') }}" placeholder="...">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label ol-form-label" for="heygen_avatar_id">{{ get_phrase('HeyGen avatar ID') }}</label>
                                <input class="form-control ol-form-control" type="text" id="heygen_avatar_id" name="heygen_avatar_id"
                                    value="{{ get_settings('heygen_avatar_id') ?: 'Daisy-inskirt-20220818' }}">
                                <small class="text-muted">{{ get_phrase('Default: Daisy-inskirt-20220818') }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label ol-form-label" for="heygen_voice_id">{{ get_phrase('HeyGen voice ID') }}</label>
                                <input class="form-control ol-form-control" type="text" id="heygen_voice_id" name="heygen_voice_id"
                                    value="{{ get_settings('heygen_voice_id') ?: '2d5b0e6cf36f460aa7fc47e3eee4ba54' }}">
                                <small class="text-muted">{{ get_phrase('Default English female voice') }}</small>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">
                            {{ get_phrase('You can also set HEYGEN_API_KEY in .env. When HeyGen is configured, video lessons use the AI presenter instead of YouTube.') }}
                        </p>

                        <div class="mb-3">
                            <button type="submit" class="btn ol-btn-primary">{{ get_phrase('Save changes') }}</button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
@endsection
