<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice31InterpreterReadinessSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 10: Test 3.1.1 - Interpreter Readiness Assessment';

        $lesson = Lesson::firstOrCreate(
            [
                'title' => $title,
                'lesson_type' => 'practice_test',
            ],
            [
                'user_id' => null,
                'course_id' => null,
                'section_id' => null,
                'duration' => '0:30:0',
                'total_mark' => 70,
                'pass_mark' => 49,
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
Evaluates whether an interpreter candidate has the professional judgment, ethical awareness, terminology handling ability, and real-time decision-making skills required for regulated-industry interpretation. This is not a language proficiency test - it tests interpreter competency.

Who Should Take It:
All interpreter candidates (OPI, VRI, on-site). All language pairs. All tiers.

Skills / Competencies Assessed:
Interpreter ethics and code of conduct, medical/legal/financial terminology handling, impartiality and boundary management, accuracy under pressure, cultural competency, professional behavior in sensitive settings.

Format:
10 scenario-based judgment questions + 1 practical terminology exercise + 1 role-play description.
30 minutes.

Scoring Rubric:
- Scenario judgment (10 Q): 50 points (5 points each; best: 5, acceptable: 3, poor: 0)
- Terminology exercise: 10 points (2 points per relevant term with valid rationale, up to 10; deduct if generic or not cardiology-relevant)
- Role-play introduction script: 10 points (self-intro 2, role clarification 3, confidentiality 3, instructions to participants 2)
- Pass Threshold: 49/70 (70%). Must score at least 35/50 on scenario section.
- Borderline / Review Needed: 42-48/70
- Fail: below 42/70

Result Triggers:
- Pass (49+): Advance to language proficiency testing and QA scoring
- Borderline (42-48): Advance with interpreter ethics/standards coaching flag
- Fail (<42): Do not advance - interpreter readiness fundamentals not met

Training / Remediation Link:
Defrilex Interpreter Standards and Ethics module. Medical/Legal terminology preparation training. Session management and boundary training.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'type' => 'mcq',
                'title' => 'Scenario: During a medical interpretation session, the patient tells you (the interpreter) privately that they are not taking their prescribed medication but asks you not to tell the doctor. What should you do?',
                'options' => [
                    "Keep the patient's secret - they trusted you.",
                    'Explain to the patient that as an interpreter, you are required to interpret everything said during the session. Encourage the patient to share this with the doctor, but make clear that you cannot withhold medically relevant information.',
                    'Tell the doctor immediately without informing the patient.',
                    'Ignore it - it is not your medical concern.',
                ],
                'answer' => 'Explain to the patient that as an interpreter, you are required to interpret everything said during the session. Encourage the patient to share this with the doctor, but make clear that you cannot withhold medically relevant information.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A doctor uses a highly technical medical term you do not know. What is the correct course of action?',
                'options' => [
                    'Guess the meaning based on context and interpret your best guess.',
                    'Pause the conversation, inform both parties that you need clarification on the term, ask the doctor to explain or use simpler language, and then interpret accurately.',
                    'Skip the term and continue interpreting.',
                    'Make up a similar-sounding word in the target language.',
                ],
                'answer' => 'Pause the conversation, inform both parties that you need clarification on the term, ask the doctor to explain or use simpler language, and then interpret accurately.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: The patient becomes emotional and starts crying during a session about a terminal diagnosis. The doctor pauses. What is your role?',
                'options' => [
                    'Comfort the patient and offer your personal advice.',
                    'Remain professional and composed. Allow the pause. Do not add personal commentary. When the conversation resumes, interpret accurately and with appropriate emotional tone without inserting your own emotions.',
                    'Tell the patient to calm down so the session can continue.',
                    'Start a side conversation with the patient in their language.',
                ],
                'answer' => 'Remain professional and composed. Allow the pause. Do not add personal commentary. When the conversation resumes, interpret accurately and with appropriate emotional tone without inserting your own emotions.',
            ],
            [
                'type' => 'mcq',
                'title' => "Scenario: A client's family member in the room starts interpreting for the patient instead of allowing you to interpret. What should you do?",
                'options' => [
                    'Let them interpret - they know the patient better.',
                    'Politely but firmly explain that for accuracy and compliance purposes, you are the designated professional interpreter and all communication should go through you. Address this to the provider so they can reinforce the protocol.',
                    'Argue with the family member.',
                    'Leave the session.',
                ],
                'answer' => 'Politely but firmly explain that for accuracy and compliance purposes, you are the designated professional interpreter and all communication should go through you. Address this to the provider so they can reinforce the protocol.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You are interpreting a legal proceeding and the attorney asks you to explain a legal concept to the client, not just interpret. What should you do?',
                'options' => [
                    'Explain the concept since you understand it.',
                    'Clarify your role: you are there to interpret, not to provide legal advice or explanations. Suggest the attorney explain the concept in simpler terms, which you will then interpret.',
                    'Refuse and say nothing.',
                    'Give a brief explanation but note it is not legal advice.',
                ],
                'answer' => 'Clarify your role: you are there to interpret, not to provide legal advice or explanations. Suggest the attorney explain the concept in simpler terms, which you will then interpret.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You are assigned to interpret for a patient who speaks a dialect of your language that you do not fully understand. What is the appropriate action?',
                'options' => [
                    'Proceed and do your best.',
                    'Inform the scheduler or supervisor immediately that you may not be fully proficient in this dialect. Request a more qualified interpreter or confirm with the patient that communication is clear before proceeding.',
                    'Cancel the session without explanation.',
                    'Use a translation app to help.',
                ],
                'answer' => 'Inform the scheduler or supervisor immediately that you may not be fully proficient in this dialect. Request a more qualified interpreter or confirm with the patient that communication is clear before proceeding.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: After a session, the client (healthcare provider) asks you for your personal opinion about whether the patient seemed honest. How should you respond?',
                'options' => [
                    'Share your honest opinion - you are trying to help.',
                    'Explain that providing personal assessments of patients is outside your role as an interpreter. Your job is to convey what was said accurately, not to evaluate the speakers.',
                    'Give a vague, non-committal answer.',
                    'Report the provider for asking inappropriate questions.',
                ],
                'answer' => 'Explain that providing personal assessments of patients is outside your role as an interpreter. Your job is to convey what was said accurately, not to evaluate the speakers.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You recognize the patient in an interpretation session as someone from your personal community. What should you do?',
                'options' => [
                    'Proceed normally - you can be professional.',
                    'Disclose the potential conflict of interest to the scheduler. If the patient is comfortable proceeding and confidentiality can be maintained, continue with extra care. If either party is uncomfortable, request reassignment.',
                    'Refuse the session immediately.',
                    'Chat with them personally before the session starts.',
                ],
                'answer' => 'Disclose the potential conflict of interest to the scheduler. If the patient is comfortable proceeding and confidentiality can be maintained, continue with extra care. If either party is uncomfortable, request reassignment.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: The provider is speaking very quickly and you are falling behind in interpretation. What is the correct action?',
                'options' => [
                    'Summarize instead of interpreting word-for-word to keep up.',
                    'Politely interrupt and ask the provider to slow down or pause periodically so you can provide complete and accurate interpretation.',
                    'Just interpret the parts you caught.',
                    'Stop interpreting and wait for them to finish.',
                ],
                'answer' => 'Politely interrupt and ask the provider to slow down or pause periodically so you can provide complete and accurate interpretation.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: During a government benefits interview, the client says something in their language that you believe may be inaccurate based on what you know about their situation. What should you do?',
                'options' => [
                    "Correct the client's statement to be accurate.",
                    'Interpret exactly what the client said, without modification, addition, or omission. It is not your role to evaluate the accuracy of what speakers say - only to convey it faithfully.',
                    'Add a note that you think the client may be mistaken.',
                    'Refuse to interpret a statement you believe is false.',
                ],
                'answer' => 'Interpret exactly what the client said, without modification, addition, or omission. It is not your role to evaluate the accuracy of what speakers say - only to convey it faithfully.',
            ],
            [
                'type' => 'long_answer',
                'title' => <<<'HTML'
<h5>Practical Exercise: Terminology Preparation (10 points)</h5>
<p>You are assigned to interpret a <strong>cardiology</strong> appointment tomorrow. The patient speaks <strong>[your target language]</strong>.</p>
<p><strong>List 10 medical terms</strong> you would prepare in advance for this session, and for <strong>each</strong>, explain briefly why an interpreter would need to know this term for a cardiology appointment.</p>
HTML
            ],
            [
                'type' => 'long_answer',
                'title' => <<<'HTML'
<h5>Role-Play Preparation: Session Introduction (10 points)</h5>
<p>Write out the <strong>exact introduction script</strong> you would deliver at the beginning of an <strong>OPI (over-the-phone interpretation)</strong> session for a healthcare appointment.</p>
<p>Include: your introduction, role clarification, confidentiality statement, and instructions for the provider and patient.</p>
HTML
            ],
        ];
    }
}
