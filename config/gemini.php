<?php

return [
    'assistant_name' => env('AI_ASSISTANT_NAME', 'Léa'),

    'api_key' => env('GEMINI_API_KEY'),
    'tts_model' => env('GEMINI_TTS_MODEL', 'gemini-2.5-flash-preview-tts'),
    'audio_model' => env('GEMINI_AUDIO_MODEL', 'gemini-2.5-flash'),
    'vision_model' => env('GEMINI_VISION_MODEL', 'gemini-2.5-flash'),
    'live_model' => env('GEMINI_LIVE_MODEL', 'gemini-2.5-flash-native-audio-preview-12-2025'),
    'live_voice' => env('GEMINI_LIVE_VOICE', 'Aoede'),
    'tts_sample_rate' => (int) env('GEMINI_TTS_SAMPLE_RATE', 24000),
];
