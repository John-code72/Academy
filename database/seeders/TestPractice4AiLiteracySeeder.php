<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice4AiLiteracySeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 4: Test 2.4 - AI Literacy Assessment';

        $lesson = Lesson::firstOrCreate(
            [
                'title' => $title,
                'lesson_type' => 'practice_test',
            ],
            [
                'user_id' => null,
                'course_id' => null,
                'section_id' => null,
                'duration' => '0:20:0',
                'total_mark' => 60,
                'pass_mark' => 42,
                'retake' => 9999,
                'description' => $this->description(),
                'status' => 1,
            ]
        );

        $questions = $this->questions();
        foreach ($questions as $index => $question) {
            $type = $question['type'];
            $payload = [
                'type' => $type,
                'sort' => $index + 1,
            ];

            if ($type === 'mcq') {
                $payload['answer'] = json_encode([$question['answer']]);
                $payload['options'] = json_encode($question['options']);
            } elseif ($type === 'long_answer') {
                $payload['answer'] = json_encode([]);
                $payload['options'] = null;
            }

            Question::updateOrCreate(
                [
                    'quiz_id' => $lesson->id,
                    'title' => $question['title'],
                ],
                $payload
            );
        }
    }

    private function description(): string
    {
        return <<<TEXT
Purpose:
Verifies that the candidate understands what AI tools are, how they work at a practical level, when to use them appropriately, what their limitations are, and how AI fits into a professional work environment. Defrilex is an AI-native company where every team member is expected to use AI as a daily productivity tool.

Who Should Take It:
All candidates. All roles. All levels.

Skills / Competencies Assessed:
Understanding of what AI is and is not, practical AI tool usage (prompt writing, output evaluation), awareness of AI limitations and risks, understanding of when AI is appropriate vs. when human judgment is required, AI ethics awareness.

Format:
15 questions: 10 multiple-choice, 3 scenario-based judgment, 2 short-answer.
20 minutes.

Scoring Rubric:
- Multiple-choice (10 Q): 30 points (3 points per correct answer)
- Scenario judgment (3 Q): 18 points (6 points each)
- Short-answer (2 Q): 12 points (6 points each)
- Pass Threshold: 42/60 (70%)
- Borderline / Review Needed: 36-41/60
- Fail: below 36/60

Result Triggers:
- Pass (42+): Advance
- Borderline (36-41): Advance with mandatory AI literacy onboarding module
- Fail (<36): Do not advance

Training / Remediation Link:
Defrilex AI Tool Training module. Prompt engineering basics. AI-assisted workflow training for role-specific applications.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'type' => 'mcq',
                'title' => 'What is the most accurate description of what a large language model (like Claude or ChatGPT) does?',
                'options' => [
                    'It understands human language the same way humans do.',
                    'It generates text by predicting the most likely next words based on patterns learned from training data.',
                    'It searches the internet in real time to find answers.',
                    'It stores a database of all correct answers to all questions.',
                ],
                'answer' => 'It generates text by predicting the most likely next words based on patterns learned from training data.',
            ],
            [
                'type' => 'mcq',
                'title' => 'You use an AI tool to draft a client proposal. What should you do before sending it?',
                'options' => [
                    'Send it immediately - AI output is always professional and accurate.',
                    'Review every claim, verify any data or statistics, edit for tone, and ensure it reflects Defrilex standards.',
                    'Add your name to it and send it without reading.',
                    'Ask the AI to verify its own work.',
                ],
                'answer' => 'Review every claim, verify any data or statistics, edit for tone, and ensure it reflects Defrilex standards.',
            ],
            [
                'type' => 'mcq',
                'title' => "What is 'AI hallucination'?",
                'options' => [
                    'When the AI tool crashes.',
                    'When the AI generates information that sounds plausible but is factually incorrect.',
                    'When the AI runs too slowly.',
                    'When the AI tool asks too many questions.',
                ],
                'answer' => 'When the AI generates information that sounds plausible but is factually incorrect.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Which of the following is the BEST use of AI in a professional setting?',
                'options' => [
                    'Using AI to make the final hiring decision on a candidate.',
                    'Using AI to draft a first version of an email that you then review and edit.',
                    'Using AI to generate your weekly performance numbers.',
                    'Using AI to sign contracts on your behalf.',
                ],
                'answer' => 'Using AI to draft a first version of an email that you then review and edit.',
            ],
            [
                'type' => 'mcq',
                'title' => 'When writing a prompt for an AI tool, which approach will produce the best results?',
                'options' => [
                    "Keep it as short as possible: 'Write email.'",
                    'Be specific about context, audience, tone, length, and desired output.',
                    'Write in all capitals so the AI pays more attention.',
                    "Copy and paste your entire inbox and say 'Handle this.'",
                ],
                'answer' => 'Be specific about context, audience, tone, length, and desired output.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Which of the following should you NEVER put into a public AI tool without proper data agreements?',
                'options' => [
                    'A request to summarize a public news article.',
                    'Client personal health information, social security numbers, or confidential financial data.',
                    'A request to explain a grammar rule.',
                    'A request to brainstorm marketing ideas.',
                ],
                'answer' => 'Client personal health information, social security numbers, or confidential financial data.',
            ],
            [
                'type' => 'mcq',
                'title' => 'AI is best at which type of task?',
                'options' => [
                    'Making ethical judgments about people.',
                    'Drafting, summarizing, analyzing patterns, and generating first versions of content.',
                    'Replacing the need for any human review.',
                    'Guaranteeing 100% accuracy on all factual claims.',
                ],
                'answer' => 'Drafting, summarizing, analyzing patterns, and generating first versions of content.',
            ],
            [
                'type' => 'mcq',
                'title' => "An AI tool gives you a statistic: 'Healthcare interpretation errors cause 47% of malpractice claims.' What should you do?",
                'options' => [
                    'Include it in your report - the AI found it so it must be real.',
                    'Verify the statistic independently before using it in any professional document.',
                    'Ignore it - AI statistics are always wrong.',
                    'Ask the AI where it got the number and accept whatever source it provides.',
                ],
                'answer' => 'Verify the statistic independently before using it in any professional document.',
            ],
            [
                'type' => 'mcq',
                'title' => 'What is the most important limitation to remember when using AI tools for work?',
                'options' => [
                    'They are too expensive to use regularly.',
                    'They can generate confident-sounding but incorrect information and they do not truly understand context the way humans do.',
                    'They only work in English.',
                    'They cannot generate text longer than one paragraph.',
                ],
                'answer' => 'They can generate confident-sounding but incorrect information and they do not truly understand context the way humans do.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Defrilex uses AI to assist with CRM data entry, proposal drafting, and meeting summaries. Who is ultimately responsible for the accuracy of these outputs?',
                'options' => [
                    'The AI tool provider.',
                    'The person who uses the AI output in their work.',
                    'The IT department.',
                    'No one - AI is responsible for its own outputs.',
                ],
                'answer' => 'The person who uses the AI output in their work.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You are preparing a compliance document for a healthcare client. You use AI to draft the document. The AI produces a detailed draft that references specific HIPAA regulations by number. You are not sure if the regulation numbers are correct. What is the best course of action?',
                'options' => [
                    'Submit the draft as-is since the AI seems confident.',
                    'Verify every regulation number against official HIPAA documentation before submitting.',
                    'Delete the regulation numbers and submit without references.',
                    'Ask a colleague who also does not know HIPAA to review it.',
                ],
                'answer' => 'Verify every regulation number against official HIPAA documentation before submitting.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: Your manager asks you to use AI to summarize 50 pages of client feedback notes. The AI summary misses a critical complaint that appeared in the notes. What does this situation teach you about AI?',
                'options' => [
                    'AI summaries are useless and should never be used.',
                    'AI summaries are helpful starting points but must be reviewed against the source material, especially for critical details.',
                    'The AI must be broken and should be reported to IT.',
                    'You should use a different AI tool that never makes mistakes.',
                ],
                'answer' => 'AI summaries are helpful starting points but must be reviewed against the source material, especially for critical details.',
            ],
            [
                'type' => 'mcq',
                'title' => "Scenario: A colleague says: 'I just paste all my client emails into ChatGPT and have it write all my replies. I do not even read the responses before sending.' How would you evaluate this practice?",
                'options' => [
                    'This is efficient and should be encouraged across the team.',
                    'This is risky and unprofessional. AI responses need human review for accuracy, tone, and appropriateness before being sent to clients.',
                    'This is fine as long as the client does not know.',
                    'This is acceptable for internal emails but not external ones.',
                ],
                'answer' => 'This is risky and unprofessional. AI responses need human review for accuracy, tone, and appropriateness before being sent to clients.',
            ],
            [
                'type' => 'long_answer',
                'title' => 'Describe one specific way you could use an AI tool to improve your productivity in a work setting. Be specific about the task, the tool, and how you would ensure the output meets professional standards.',
            ],
            [
                'type' => 'long_answer',
                'title' => 'In your own words, explain why it is important for humans to review AI-generated content before using it in professional communications. Give one example of what could go wrong if they do not.',
            ],
        ];
    }
}
