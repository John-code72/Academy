<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice7ProfessionalismJudgmentSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 7: Test 2.7 - Professionalism, Judgment, and Accountability Assessment';

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
Evaluates whether the candidate demonstrates the values and judgment Defrilex requires: accountability, integrity, ownership, dignity, and excellence. This test reveals character and decision-making patterns that predict cultural fit at Defrilex.

Who Should Take It:
All candidates. All roles. All levels. This test should carry significant weight in the overall hiring decision.

Skills / Competencies Assessed:
Accountability under pressure, ethical judgment, ownership behavior, professional decision-making, conflict handling, truthfulness, client-first orientation, ability to admit mistakes.

Format:
12 scenario-based judgment questions + 2 short-answer reflections.
20 minutes.

Scoring Rubric:
- Scenario judgment (12 Q): 48 points (4 points each; full credit for best answer, 2 for acceptable, 0 for poor)
- Short-answer (2 Q): 12 points (6 points each; honesty, self-awareness, specificity, alignment with accountability/ownership values)
- Pass Threshold: 42/60 (70%). Must score at least 7/12 on short-answer section.
- Borderline / Review Needed: 36-41/60
- Fail: below 36/60

Result Triggers:
- Pass (42+): Strong culture signal. Advance.
- Borderline (36-41): Proceed with culture interview focus on accountability and ownership.
- Fail (<36): Serious concern. Do not advance unless other signals are overwhelmingly strong.

Training / Remediation Link:
Defrilex Values and Culture onboarding module. Accountability and ownership standards. Professional judgment development.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'type' => 'mcq',
                'title' => 'Scenario: You made a mistake in a client report that was already sent. Your manager has not noticed yet. What do you do?',
                'options' => [
                    'Hope no one notices and fix it quietly if asked.',
                    'Immediately tell your manager, explain the error, propose how to fix it, and contact the client with a corrected version.',
                    'Blame the data source and say the report was based on bad input.',
                    'Wait to see if the client notices before doing anything.',
                ],
                'answer' => 'Immediately tell your manager, explain the error, propose how to fix it, and contact the client with a corrected version.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A colleague is struggling with their workload and asks you to cover a task that is not your responsibility. You are also busy. What do you do?',
                'options' => [
                    'Say no immediately - it is not your job.',
                    'Assess whether you can realistically help without missing your own deadlines. If yes, help. If no, explain honestly and suggest they escalate to the manager.',
                    'Say yes and then quietly not do it.',
                    'Help them but complain about it to other colleagues.',
                ],
                'answer' => 'Assess whether you can realistically help without missing your own deadlines. If yes, help. If no, explain honestly and suggest they escalate to the manager.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: During a team meeting, your manager publicly credits you for work that a colleague actually did most of. What do you do?',
                'options' => [
                    'Accept the credit - you were involved.',
                    'Speak up during or after the meeting and give credit to the colleague who did the majority of the work.',
                    'Say nothing but feel guilty.',
                    'Tell the colleague privately that you will give them credit next time.',
                ],
                'answer' => 'Speak up during or after the meeting and give credit to the colleague who did the majority of the work.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A client asks you to do something that technically violates a company process but would make the client happy. What is the right approach?',
                'options' => [
                    'Do what the client wants - client satisfaction is the priority.',
                    'Explain to the client that you want to help them but need to do it within the proper process. Offer an alternative that meets their need while staying within guidelines. Escalate if needed.',
                    'Ignore the request and hope they forget.',
                    'Do it but do not tell your manager.',
                ],
                'answer' => 'Explain to the client that you want to help them but need to do it within the proper process. Offer an alternative that meets their need while staying within guidelines. Escalate if needed.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You overhear a colleague exaggerating Defrilex\'s capabilities to a potential client during a call. What do you do?',
                'options' => [
                    'It is not your call - stay out of it.',
                    'After the call, privately speak with the colleague and explain that misrepresenting capabilities creates trust risk and potential delivery problems. If the behavior continues, escalate to your manager.',
                    'Join the call and contradict them in front of the client.',
                    'Tell other colleagues about it but take no action.',
                ],
                'answer' => 'After the call, privately speak with the colleague and explain that misrepresenting capabilities creates trust risk and potential delivery problems. If the behavior continues, escalate to your manager.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You are behind on your weekly targets. Your manager asks for your status report. What do you do?',
                'options' => [
                    'Inflate the numbers slightly to look better.',
                    'Report the actual numbers honestly, explain the gap, what caused it, and what you are doing to get back on track.',
                    'Avoid the conversation and submit the report late.',
                    'Blame external factors beyond your control.',
                ],
                'answer' => 'Report the actual numbers honestly, explain the gap, what caused it, and what you are doing to get back on track.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You receive feedback from your manager that your work quality on a recent project was below expectations. How do you respond?',
                'options' => [
                    'Defend your work and explain why the feedback is unfair.',
                    'Listen carefully, ask for specific examples so you understand the gap, acknowledge the feedback, and create a plan to improve.',
                    'Agree outwardly but internally dismiss the feedback.',
                    'Get upset and consider looking for another job.',
                ],
                'answer' => 'Listen carefully, ask for specific examples so you understand the gap, acknowledge the feedback, and create a plan to improve.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You discover that a team process is inefficient and is wasting 3 hours per week of your time. What do you do?',
                'options' => [
                    'Follow the process without complaint - it is not your place to change things.',
                    'Document the inefficiency, propose a specific improvement with expected time savings, and present it to your manager.',
                    'Stop following the process and do it your own way.',
                    'Complain about it to colleagues in chat.',
                ],
                'answer' => 'Document the inefficiency, propose a specific improvement with expected time savings, and present it to your manager.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A critical deadline is approaching and you realize you cannot meet it alone. Asking for help might make you look weak. What do you do?',
                'options' => [
                    'Work until 3 AM and deliver something substandard.',
                    'Proactively ask for help early, clearly explaining what you need and why. Meeting the team\'s deadline is more important than looking like you can handle everything alone.',
                    'Miss the deadline and explain afterward.',
                    'Deliver on time but cut quality significantly.',
                ],
                'answer' => 'Proactively ask for help early, clearly explaining what you need and why. Meeting the team\'s deadline is more important than looking like you can handle everything alone.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A client complains about something that was genuinely not Defrilex\'s fault. How do you handle it?',
                'options' => [
                    'Tell the client directly that it is not your fault.',
                    'Listen empathetically, acknowledge the client\'s frustration, investigate the root cause, and work to resolve the situation regardless of fault attribution. Protecting the relationship matters more than being right.',
                    'Ignore the complaint.',
                    'Escalate it immediately without attempting resolution.',
                ],
                'answer' => 'Listen empathetically, acknowledge the client\'s frustration, investigate the root cause, and work to resolve the situation regardless of fault attribution. Protecting the relationship matters more than being right.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You are given a task you have never done before and do not fully understand. What do you do?',
                'options' => [
                    'Pretend you understand and figure it out as you go.',
                    'Ask clarifying questions upfront, research what you do not know, attempt the task, and check in with your manager before finalizing to ensure you are on the right track.',
                    'Wait for someone to explain it step by step before starting.',
                    'Delegate it to someone else.',
                ],
                'answer' => 'Ask clarifying questions upfront, research what you do not know, attempt the task, and check in with your manager before finalizing to ensure you are on the right track.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A new team member seems to be struggling and appears hesitant to ask for help. What do you do?',
                'options' => [
                    'That is their manager\'s problem.',
                    'Reach out privately and offer support. Share resources or context that might help them. Let them know it is safe to ask questions.',
                    'Mention their struggles to the team in a meeting.',
                    'Do nothing - they will figure it out.',
                ],
                'answer' => 'Reach out privately and offer support. Share resources or context that might help them. Let them know it is safe to ask questions.',
            ],
            [
                'type' => 'long_answer',
                'title' => 'Describe a time in your professional life when you made a significant mistake. What happened, what did you do about it, and what did you learn?',
            ],
            [
                'type' => 'long_answer',
                'title' => 'What does \'accountability\' mean to you in a professional context? Give a specific example of what accountability looks like in daily work.',
            ],
        ];
    }
}
