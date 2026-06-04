<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice361OperationsReadinessSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 13: Test 3.6.1 - Operations Readiness Assessment';

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
Evaluates whether an operations candidate has the process discipline, problem-solving ability, SLA awareness, and operational judgment required to manage service delivery at Defrilex. Operations is where promises become reality.

Who Should Take It:
All operations candidates: operations coordinators, operations managers, service delivery specialists, scheduling coordinators.

Skills / Competencies Assessed:
Process discipline, SLA management, capacity planning awareness, incident handling, cross-functional coordination, root-cause analysis, documentation rigor, prioritization under pressure.

Format:
8 scenario-based judgment questions + 1 practical exercise (incident response).
25 minutes.

Scoring Rubric:
- Scenario judgment (8 Q): 40 points (5 points each)
- Incident report: 20 points (factual accuracy 4, root cause analysis 4, resolution steps 3, client communication plan 3, preventive measures 3, ownership assignment 3)
- Pass Threshold: 42/60 (70%). Must score 14+ on incident report.
- Borderline / Review Needed: 36-41/60
- Fail: below 36/60

Result Triggers:
- Pass (42+): Advance
- Borderline (36-41): Advance with operations standards training
- Fail (<36): Do not advance

Training / Remediation Link:
Defrilex Operations OS. SLA management training. Incident response protocol. Root-cause analysis methodology.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'type' => 'mcq',
                'title' => 'Scenario: You are managing interpreter scheduling. A healthcare client needs a Haitian Creole interpreter for a session in 45 minutes. Your primary interpreter just called in unavailable. What do you do?',
                'options' => [
                    'Tell the client you cannot fulfill the request.',
                    'Immediately activate the backup interpreter protocol: check the secondary and tertiary interpreter lists, confirm availability, brief the backup on the client\'s needs, and communicate the status to the client within 15 minutes.',
                    'Wait to see if the primary interpreter changes their mind.',
                    'Ask the client to reschedule.',
                ],
                'answer' => 'Immediately activate the backup interpreter protocol: check the secondary and tertiary interpreter lists, confirm availability, brief the backup on the client\'s needs, and communicate the status to the client within 15 minutes.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: Your SLA with a client specifies 95% on-time interpreter connection. This month, you are at 91% with one week remaining. What do you do?',
                'options' => [
                    'Hope the last week brings the average up.',
                    'Analyze which sessions missed the SLA and why. Implement specific fixes for the root causes this week. Proactively communicate the current status to the account manager so they can manage the client relationship. Create a corrective action plan.',
                    'Lower the SLA target.',
                    'Wait until the monthly report to address it.',
                ],
                'answer' => 'Analyze which sessions missed the SLA and why. Implement specific fixes for the root causes this week. Proactively communicate the current status to the account manager so they can manage the client relationship. Create a corrective action plan.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: Sales closes a new client and promises a 5-day deployment timeline. Operations typically needs 10 days for proper setup. How should operations handle this?',
                'options' => [
                    'Just do it in 5 days by cutting corners.',
                    'Raise the concern immediately. Work with sales and the client to establish a realistic timeline. Propose a phased launch: begin with core services in 5 days, full deployment in 10. Never sacrifice setup quality for unrealistic timelines.',
                    'Refuse the timeline and tell sales they made a mistake.',
                    'Start the setup but do not tell anyone it will be late until it is late.',
                ],
                'answer' => 'Raise the concern immediately. Work with sales and the client to establish a realistic timeline. Propose a phased launch: begin with core services in 5 days, full deployment in 10. Never sacrifice setup quality for unrealistic timelines.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A client reports that interpreter quality has declined over the past month. QA scores do not show a decline. What should you investigate?',
                'options' => [
                    'Tell the client the QA data does not support their claim.',
                    'Investigate deeper: review the specific interpreters assigned to this client, check if QA scoring criteria still align with client expectations, ask the client for specific examples, and consider whether QA rubric needs updating for this client\'s specialty. Client perception IS a quality signal even when QA scores look fine.',
                    'Ignore it - the data says quality is fine.',
                    'Replace all interpreters assigned to this client.',
                ],
                'answer' => 'Investigate deeper: review the specific interpreters assigned to this client, check if QA scoring criteria still align with client expectations, ask the client for specific examples, and consider whether QA rubric needs updating for this client\'s specialty. Client perception IS a quality signal even when QA scores look fine.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: Two clients have conflicting urgent needs at the same time and you only have one available interpreter who can serve both. How do you prioritize?',
                'options' => [
                    'First come, first served.',
                    'Evaluate based on: contractual SLA priority, patient/case urgency, relationship impact, and whether alternative coverage exists for either client. Make a decision, document it, and communicate transparently with the deprioritized client about when they will receive coverage.',
                    'Flip a coin.',
                    'Tell both clients there is a system outage.',
                ],
                'answer' => 'Evaluate based on: contractual SLA priority, patient/case urgency, relationship impact, and whether alternative coverage exists for either client. Make a decision, document it, and communicate transparently with the deprioritized client about when they will receive coverage.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You notice that the same scheduling error has occurred three times this month, each time with a different coordinator. What is the appropriate response?',
                'options' => [
                    'Discipline the coordinators.',
                    'Treat it as a process problem, not a people problem. Investigate the root cause: is the scheduling tool confusing? Is a process step unclear? Fix the system/process to prevent the error, then retrain the team on the corrected process.',
                    'Ignore it - three times is not a pattern.',
                    'Add more review steps to slow the process down.',
                ],
                'answer' => 'Treat it as a process problem, not a people problem. Investigate the root cause: is the scheduling tool confusing? Is a process step unclear? Fix the system/process to prevent the error, then retrain the team on the corrected process.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A new operations team member is ramping up and asks you to check all their work before it goes out. How do you handle this?',
                'options' => [
                    'Check everything for them indefinitely.',
                    'Check their work closely for the first 2-4 weeks, provide specific feedback on errors, then gradually reduce oversight as they demonstrate accuracy. The goal is independence, not dependence.',
                    'Tell them to figure it out on their own.',
                    'Have them watch you do it instead of doing it themselves.',
                ],
                'answer' => 'Check their work closely for the first 2-4 weeks, provide specific feedback on errors, then gradually reduce oversight as they demonstrate accuracy. The goal is independence, not dependence.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: At the end of the month, you discover that a billing error resulted in a client being undercharged by $2,400. What do you do?',
                'options' => [
                    'Absorb the loss - it is your team\'s mistake.',
                    'Document the error, determine root cause, fix the billing process to prevent recurrence, and work with the account manager to communicate transparently with the client and arrange correction. Honesty protects long-term trust.',
                    'Bill the client silently next month and hope they do not notice.',
                    'Wait until the client notices and then fix it.',
                ],
                'answer' => 'Document the error, determine root cause, fix the billing process to prevent recurrence, and work with the account manager to communicate transparently with the client and arrange correction. Honesty protects long-term trust.',
            ],
            [
                'type' => 'long_answer',
                'title' => <<<'HTML'
<h5>Practical Exercise: Incident Response Documentation (20 points)</h5>
<p><strong>Scenario:</strong> A government client\'s scheduled interpretation session (Spanish, legal proceeding) did not connect. The interpreter was logged in but the client\'s platform had a configuration error. The session was delayed by 25 minutes. The client\'s SLA specifies a maximum 5-minute connection time.</p>
<p>Write a <strong>complete incident report</strong> covering:</p>
<ul>
<li>What happened (facts only)</li>
<li>Root cause analysis</li>
<li>Immediate resolution steps taken</li>
<li>Client communication plan</li>
<li>Preventive measures for the future</li>
<li>Who owns each follow-up action</li>
</ul>
<p>Keep it concise and professional.</p>
HTML
            ],
        ];
    }
}
