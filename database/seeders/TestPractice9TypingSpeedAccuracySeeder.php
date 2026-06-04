<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice9TypingSpeedAccuracySeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 9: Test 2.9 - Typing Speed and Accuracy Assessment';

        $lesson = Lesson::firstOrCreate(
            [
                'title' => $title,
                'lesson_type' => 'practice_test',
            ],
            [
                'user_id' => null,
                'course_id' => null,
                'section_id' => null,
                'duration' => '0:5:0',
                'total_mark' => 100,
                'pass_mark' => 1,
                'retake' => 9999,
                'description' => $this->description(),
                'status' => 1,
            ]
        );

        $questions = $this->questions();
        foreach ($questions as $index => $question) {
            Question::updateOrCreate(
                [
                    'quiz_id' => $lesson->id,
                    'title' => $question['title'],
                ],
                [
                    'type' => 'long_answer',
                    'answer' => json_encode([]),
                    'options' => null,
                    'sort' => $index + 1,
                ]
            );
        }
    }

    private function description(): string
    {
        return <<<TEXT
Purpose:
Verifies that the candidate can type at the speed and accuracy level required for productive remote work, CRM documentation, real-time note-taking, and communication. Slow or inaccurate typing creates operational drag in every role.

Who Should Take It:
All candidates. All roles. Higher thresholds for BPO/customer support, operations, and data entry roles.

Skills / Competencies Assessed:
Typing speed (words per minute), typing accuracy (error rate), ability to type from dictation or source material.

Format:
Timed typing test: 3-minute passage transcription + 2-minute dictation typing exercise.
Total: 5 minutes active typing.

Scoring Method (manual evaluator scoring):
- Measure WPM and accuracy for both exercises.
- Record final decision as Pass / Borderline / Fail according to thresholds below.

Thresholds:
- Standard roles (pass): 40+ WPM and 95%+ accuracy
- BPO/support/data roles (pass): 55+ WPM and 97%+ accuracy
- Borderline: 35-39 WPM or 92-94% accuracy
- Fail: below 35 WPM or below 92% accuracy

Result Triggers:
- Pass: Advance
- Borderline: Advance with improvement expectation; not eligible for typing-intensive roles
- Fail: Do not advance for BPO/support/data roles; may advance for non-typing-intensive roles if close to threshold

Evaluator Notes:
- What Success Looks Like: Candidate types fluently at or above threshold speed with minimal errors in both transcription and dictation.
- What Failure Looks Like: Candidate types below threshold, makes frequent errors, or cannot keep pace with moderate dictation.
- Red Flags: below 25 WPM, below 90% accuracy, inability to type from dictation.

Training / Remediation Link:
Typing improvement resources. Daily typing practice recommendation. Speed and accuracy benchmarking.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'title' => <<<'HTML'
<h5>Exercise 1: Passage Transcription (3 minutes)</h5>
<p>Type the following passage as accurately as possible within 3 minutes.</p>
<blockquote style="background:#f8fafc;border-left:4px solid #cbd5e1;padding:12px 14px;">
"Defrilex provides multilingual services to organizations in healthcare, government, financial services, and technology. Our interpreters and agents are professionally vetted, QA-scored, and deployed through both managed service engagements and our talent marketplace platform. Every session is monitored for quality. Every client relationship is managed by a dedicated account team. Our commitment to compliance, accuracy, and cultural competency differentiates us from commodity language service providers. We believe that language access is not a convenience but a fundamental requirement for equitable service delivery in regulated industries."
</blockquote>
<p><strong>Evaluator:</strong> score WPM and accuracy for this exercise.</p>
HTML
            ],
            [
                'title' => <<<'HTML'
<h5>Exercise 2: Dictation Typing (2 minutes)</h5>
<p>The evaluator reads the following text aloud at moderate pace. Candidate types what they hear within 2 minutes.</p>
<blockquote style="background:#f8fafc;border-left:4px solid #cbd5e1;padding:12px 14px;">
"The client onboarding process at Defrilex follows a structured timeline. Within the first three business days, the account manager conducts a kickoff call, confirms language requirements, and establishes quality benchmarks. By day seven, the first interpreter sessions are scheduled and the client has access to the reporting dashboard."
</blockquote>
<p><strong>Evaluator:</strong> score WPM and accuracy for dictation performance.</p>
HTML
            ],
        ];
    }
}
