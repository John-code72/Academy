<?php



namespace App\Services;



use App\Services\KnowledgeIngestion\KnowledgeIngestionService;

use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;



class GeminiLiveService

{

    private const LIVE_MODELS = [

        'gemini-2.5-flash-native-audio-preview-12-2025',

        'gemini-2.5-flash-native-audio-preview-09-2025',

        'gemini-2.0-flash-live-001',

    ];



    public function __construct(

        private GeminiVisionChatService $gemini,

        private KnowledgeIngestionService $knowledge,

    ) {}



    public function isConfigured(): bool

    {

        return $this->gemini->isConfigured();

    }



    /**

     * @return array{ok: bool, token?: string, model?: string, voice?: string, systemInstruction?: string, error?: string}

     */

    public function createSessionToken(?string $topic = null, ?string $curriculumPrompt = null, ?string $track = null, ?string $coachOpener = null): array

    {

        $apiKey = $this->gemini->apiKey();

        if (! $apiKey) {

            return ['ok' => false, 'error' => 'AI assistant API key is not configured.'];

        }



        $now = Carbon::now('UTC');

        $models = array_values(array_unique(array_filter([

            $this->liveModel(),

            ...self::LIVE_MODELS,

        ])));



        $lastError = 'Could not start live voice session.';



        foreach ($models as $model) {

            $modelPath = 'models/' . ltrim($model, 'models/');



            $payload = [

                'uses'                 => 1,

                'expireTime'           => $now->copy()->addMinutes(30)->toIso8601String(),

                'newSessionExpireTime' => $now->copy()->addMinute()->toIso8601String(),

            ];



            $response = Http::timeout(30)

                ->withHeaders(['x-goog-api-key' => $apiKey])

                ->post('https://generativelanguage.googleapis.com/v1alpha/auth_tokens', $payload);



            if (! $response->successful()) {

                $lastError = (string) ($response->json('error.message') ?? $lastError);

                Log::warning('Gemini live token failed', [

                    'model'  => $model,

                    'status' => $response->status(),

                    'body'   => Str::limit($response->body(), 500),

                ]);

                continue;

            }



            $token = (string) ($response->json('name') ?? '');

            if ($token === '') {

                $lastError = 'Empty live voice session token.';

                continue;

            }



            return [

                'ok'                => true,

                'token'             => $token,

                'model'             => $modelPath,

                'voice'             => $this->voiceName(),

                'systemInstruction' => $this->systemInstruction($topic, $curriculumPrompt, $track, $coachOpener),

            ];

        }



        return ['ok' => false, 'error' => $lastError];

    }



    public function liveModel(): string

    {

        return config('gemini.live_model', 'gemini-2.5-flash-native-audio-preview-12-2025');

    }



    private function voiceName(): string

    {

        $lang = strtolower((string) app()->getLocale());



        if (str_starts_with($lang, 'fr')) {

            return 'Aoede';

        }



        return config('gemini.live_voice', 'Kore');

    }



    private function systemInstruction(?string $topic = null, ?string $curriculumPrompt = null, ?string $track = null, ?string $coachOpener = null): string

    {

        $name = ai_assistant_name();

        $instruction = str_replace('{name}', $name, (string) config('coach_prompts.identity_voice', ''));

        if ($curriculumPrompt !== null && trim($curriculumPrompt) !== '') {
            $instruction .= trim($curriculumPrompt);
        } else {
            $instruction .= ' You know the learner\'s path. Start by announcing the modules to work on. '
                . 'FORBIDDEN: asking how you can help or what they need.';
        }

        if ($coachOpener !== null && trim($coachOpener) !== '') {
            $instruction .= ' REQUIRED FIRST MESSAGE (say this first, word for word if needed): ' . trim($coachOpener);
        }

        $grounding = ($topic !== null && trim($topic) !== '')
            ? $this->knowledge->buildGrounding($topic, $track, 4)
            : '';

        if ($grounding !== '') {
            $instruction .= $this->knowledge->groundingPromptSuffix($grounding);
        }

        return $instruction;

    }

}

