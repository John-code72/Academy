<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice371LeadershipManagementReadinessSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 14: Test 3.7.1 - Leadership and Management Readiness Assessment';

        $lesson = Lesson::firstOrCreate(
            [
                'title' => $title,
                'lesson_type' => 'practice_test',
            ],
            [
                'user_id' => null,
                'course_id' => null,
                'section_id' => null,
                'duration' => '0:25:0',
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
Evaluates whether a management candidate can lead with clarity, accountability, data-driven decision-making, and people development discipline that Defrilex requires. Bad management is the single most expensive people failure.

Who Should Take It:
All candidates for team lead, manager, director, or leadership positions.

Skills / Competencies Assessed:
Accountability enforcement, feedback delivery, performance management, delegation, coaching, data-driven decision-making, conflict resolution, strategic prioritization, culture stewardship.

Format:
8 scenario-based leadership judgment questions + 2 short-answer reflections.
25 minutes.

Scoring Rubric:
- Scenario judgment (8 Q): 40 points (5 points each)
- Short-answer (2 Q): 20 points (10 points each; specificity, accountability demonstrated, management maturity, alignment with Defrilex standards)
- Pass Threshold: 42/60 (70%). Must score 12+ on short answers.
- Borderline / Review Needed: 36-41/60
- Fail: below 36/60 - strong concern for management readiness

Result Triggers:
- Pass (42+): Advance to culture interview with Fritz
- Borderline (36-41): Advance with management development plan
- Fail (<36): Do not advance for management roles; may be suitable for IC roles

Training / Remediation Link:
Defrilex Manager Operating Standards. Feedback delivery (SBI model). Performance management system. 1:1 framework. Escalation protocol.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'type' => 'mcq',
                'title' => 'Scenario: A team member has missed their targets for two consecutive months. They are well-liked by the team and have a positive attitude. What is the right management approach?',
                'options' => [
                    'Give them more time - they have a great attitude.',
                    'Have a direct, documented conversation about the performance gap. Use specific data. Ask what is causing the miss. Agree on a clear improvement plan with weekly check-ins and a defined timeline. Attitude does not substitute for results.',
                    'Quietly move their targets lower so they can hit them.',
                    'Wait for the quarterly review to address it.',
                ],
                'answer' => 'Have a direct, documented conversation about the performance gap. Use specific data. Ask what is causing the miss. Agree on a clear improvement plan with weekly check-ins and a defined timeline. Attitude does not substitute for results.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: Two of your direct reports have a personal conflict that is affecting team dynamics. How do you handle it?',
                'options' => [
                    'Stay out of it - they are adults.',
                    'Address it directly. Meet with each person separately to understand perspectives, then facilitate a conversation focused on professional behavior expectations and team impact. Make clear that personal conflict cannot affect team performance or client delivery.',
                    'Transfer one of them to another team.',
                    'Pretend you do not notice.',
                ],
                'answer' => 'Address it directly. Meet with each person separately to understand perspectives, then facilitate a conversation focused on professional behavior expectations and team impact. Make clear that personal conflict cannot affect team performance or client delivery.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: Your team is overloaded and morale is dropping. Leadership asks you to take on an additional project. What do you do?',
                'options' => [
                    'Accept it and push the team harder.',
                    'Present leadership with a clear picture of current capacity: active projects, deadlines, team utilization. Ask them to help prioritize: what gets deprioritized or delayed to make room? Protect your team from burnout while being transparent about trade-offs.',
                    'Reject the project without explaining why.',
                    'Accept it but do not tell your team about the additional work.',
                ],
                'answer' => 'Present leadership with a clear picture of current capacity: active projects, deadlines, team utilization. Ask them to help prioritize: what gets deprioritized or delayed to make room? Protect your team from burnout while being transparent about trade-offs.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A team member delivers excellent work but consistently does not document their process or update the CRM. How do you handle this?',
                'options' => [
                    'Let it slide - the work quality is what matters.',
                    'Acknowledge the excellent work quality, then address the documentation gap directly: explain why it matters (team continuity, client visibility, process scalability), set a clear expectation with a deadline, and follow up. Excellence includes discipline.',
                    'Do the documentation yourself.',
                    'Assign someone else to document their work.',
                ],
                'answer' => 'Acknowledge the excellent work quality, then address the documentation gap directly: explain why it matters (team continuity, client visibility, process scalability), set a clear expectation with a deadline, and follow up. Excellence includes discipline.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You need to deliver difficult feedback to a high-performing team member about their communication style, which others find dismissive. How do you approach the conversation?',
                'options' => [
                    'Skip it - they are a top performer and you do not want to demotivate them.',
                    'Schedule a private 1:1. Use specific behavioral examples (SBI model): describe the situation, the specific behavior observed, and its impact on the team. Frame it as development, not criticism. Set clear expectations for change.',
                    'Address it in a team meeting so everyone hears.',
                    'Send an anonymous message.',
                ],
                'answer' => 'Schedule a private 1:1. Use specific behavioral examples (SBI model): describe the situation, the specific behavior observed, and its impact on the team. Frame it as development, not criticism. Set clear expectations for change.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A project is failing and it is primarily due to decisions you made as the manager. How do you handle this with your team and with leadership?',
                'options' => [
                    'Blame external factors.',
                    'Own the decisions that led to the situation. Be transparent with both your team and leadership about what went wrong and what you are doing to correct course. Model the accountability you expect from your team.',
                    'Quietly fix it without drawing attention.',
                    'Shift responsibility to the team members who executed the plan.',
                ],
                'answer' => 'Own the decisions that led to the situation. Be transparent with both your team and leadership about what went wrong and what you are doing to correct course. Model the accountability you expect from your team.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You have a team member who has been at Defrilex for 6 months and is clearly in the wrong seat (values fit is strong, capability fit is poor for this role). What is the right approach?',
                'options' => [
                    'Keep them in the role and hope they improve.',
                    'Have an honest, respectful conversation: acknowledge their values and character, but address the capability gap directly. Explore whether a different seat at Defrilex would be a better fit. Set a 30-day evaluation period for the transition.',
                    'Fire them immediately.',
                    'Give them a promotion to a different role.',
                ],
                'answer' => 'Have an honest, respectful conversation: acknowledge their values and character, but address the capability gap directly. Explore whether a different seat at Defrilex would be a better fit. Set a 30-day evaluation period for the transition.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: Leadership asks you to implement a new process that you believe will not work. How do you handle the disagreement?',
                'options' => [
                    'Implement it without saying anything.',
                    'Raise your concerns directly with leadership: present specific reasons with data or evidence for why you believe the approach has risks. Propose alternatives. If leadership still decides to proceed, commit to implementing it well and tracking results honestly.',
                    'Refuse to implement it.',
                    'Implement it but tell your team it was not your idea.',
                ],
                'answer' => 'Raise your concerns directly with leadership: present specific reasons with data or evidence for why you believe the approach has risks. Propose alternatives. If leadership still decides to proceed, commit to implementing it well and tracking results honestly.',
            ],
            [
                'type' => 'long_answer',
                'title' => 'Describe a situation where you had to hold someone accountable for underperformance. What specific steps did you take, and what was the outcome?',
            ],
            [
                'type' => 'long_answer',
                'title' => 'What is your approach to running effective 1:1 meetings with direct reports? What does a typical 1:1 agenda look like for you?',
            ],
        ];
    }
}
