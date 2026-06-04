<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice381FinanceReadinessSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 15: Test 3.8.1 - Finance Readiness Assessment';

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
Evaluates whether a finance candidate has the analytical accuracy, judgment, and operational finance understanding required to support Defrilex's financial operations, billing accuracy, margin management, and financial reporting.

Who Should Take It:
All finance candidates: finance analysts, billing specialists, controllers, financial operations staff.

Skills / Competencies Assessed:
Financial accuracy, billing operations, gross margin analysis, revenue recognition awareness, budget variance analysis, financial reporting, data integrity, compliance awareness.

Format:
8 knowledge/judgment questions + 1 practical exercise (billing discrepancy analysis).
25 minutes.

Scoring Rubric:
- Knowledge/judgment (8 Q): 40 points (5 points each)
- Billing discrepancy analysis: 20 points (each discrepancy identified up to 16 pts at 4 each; resolution quality 4 pts)
- Pass Threshold: 42/60 (70%). Must score 14+ on billing analysis.
- Borderline / Review Needed: 36-41/60
- Fail: below 36/60

Grading answer key (billing exercise):
1. VRI billed at $67 instead of contracted $75 - underbilling of $8 x 45 = $360 revenue loss.
2. Correct VRI line should be 45 x $75 = $3,375, not $3,000 - confirms $375 underbilling on VRI line.
3. Total invoice should be $9,900 + $3,375 = $13,275 vs $12,900 - $375 revenue leakage vs correct contract.
4. Gross margin: OPI ($55-$28)/$55 = 49%; VRI ($75-$38)/$75 = 49.3% - both healthy; confirm vs target.
5. Payment matched incorrect invoice - need corrected invoice and collection of $375 difference (or reconcile per policy).

Result Triggers:
- Pass (42+): Advance
- Borderline (36-41): Advance with finance onboarding focus on billing operations
- Fail (<36): Do not advance

Training / Remediation Link:
Defrilex Finance Operating System. Billing operations training. Revenue recognition fundamentals. Financial integrity standards.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'type' => 'mcq',
                'title' => 'Defrilex bills a healthcare client $50 per interpretation session. The interpreter is paid $25 per session. What is the gross margin per session?',
                'options' => ['25%', '50%', '75%', '100%'],
                'answer' => '50%',
            ],
            [
                'type' => 'mcq',
                'title' => 'A client is billed monthly based on session volume. In March, 120 sessions were delivered but only 115 were logged in the billing system. What risk does this create?',
                'options' => [
                    'No risk - 5 sessions is immaterial.',
                    'Revenue leakage. The 5 unbilled sessions represent lost revenue. This indicates a process gap between service delivery tracking and billing that must be investigated and fixed.',
                    'Overbilling risk.',
                    'Compliance risk only.',
                ],
                'answer' => 'Revenue leakage. The 5 unbilled sessions represent lost revenue. This indicates a process gap between service delivery tracking and billing that must be investigated and fixed.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: The CEO asks you to prepare a financial report for a board meeting. You discover that one revenue figure is $40K higher than what the data supports. The CEO wants to present the higher number. What do you do?',
                'options' => [
                    'Use the higher number - the CEO requested it.',
                    'Present the accurate number with full documentation. Explain the discrepancy to the CEO privately and recommend presenting truthful figures. Financial integrity is non-negotiable.',
                    'Use the lower number but do not tell the CEO you changed it.',
                    'Average the two numbers.',
                ],
                'answer' => 'Present the accurate number with full documentation. Explain the discrepancy to the CEO privately and recommend presenting truthful figures. Financial integrity is non-negotiable.',
            ],
            [
                'type' => 'mcq',
                'title' => 'What is the difference between revenue and cash collected?',
                'options' => [
                    'They are the same thing.',
                    'Revenue is recognized when earned (services delivered); cash collected is when payment is actually received. In a services business, the timing difference creates accounts receivable.',
                    'Revenue is always higher than cash collected.',
                    'Cash collected is a subset of profit.',
                ],
                'answer' => 'Revenue is recognized when earned (services delivered); cash collected is when payment is actually received. In a services business, the timing difference creates accounts receivable.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You notice that DSO (Days Sales Outstanding) has increased from 35 days to 52 days over the past quarter. What does this signal and what action should you take?',
                'options' => [
                    'It is normal fluctuation.',
                    'It signals that clients are taking longer to pay. Investigate: are specific clients responsible? Are invoices being sent on time? Are there disputes? Implement targeted collection follow-up and report the trend to leadership as a cash flow risk.',
                    'It means revenue is declining.',
                    'Wait another quarter to see if it resolves.',
                ],
                'answer' => 'It signals that clients are taking longer to pay. Investigate: are specific clients responsible? Are invoices being sent on time? Are there disputes? Implement targeted collection follow-up and report the trend to leadership as a cash flow risk.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Defrilex signs a 12-month contract worth $120,000 with a new client. How should this revenue be recognized?',
                'options' => [
                    'Recognize the full $120,000 when the contract is signed.',
                    'Recognize $10,000 per month as services are delivered over the 12-month period.',
                    'Recognize it when cash is received.',
                    'Recognize half upfront and half at the end.',
                ],
                'answer' => 'Recognize $10,000 per month as services are delivered over the 12-month period.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You are reviewing monthly expenses and notice that a software subscription increased from $200/month to $800/month with no approval documentation. What should you do?',
                'options' => [
                    'It is only $600 - not worth investigating.',
                    'Flag it for investigation. Determine who authorized the change, whether it was needed, and whether proper approval was obtained. Even small unauthorized expenses indicate process gaps that can grow.',
                    'Cancel the subscription immediately.',
                    'Let it go this month and watch for next month.',
                ],
                'answer' => 'Flag it for investigation. Determine who authorized the change, whether it was needed, and whether proper approval was obtained. Even small unauthorized expenses indicate process gaps that can grow.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A client disputes an invoice, claiming they were billed for sessions that did not occur. Your delivery records are incomplete for that period. How should this be handled?',
                'options' => [
                    'Tell the client the invoice is correct.',
                    'Acknowledge the dispute, investigate by cross-referencing all available records (interpreter logs, platform data, client-side records), and resolve with transparency. If records cannot confirm delivery, credit the client. Then fix the record-keeping gap.',
                    'Credit the full invoice without investigation.',
                    'Ignore the dispute and send a reminder.',
                ],
                'answer' => 'Acknowledge the dispute, investigate by cross-referencing all available records (interpreter logs, platform data, client-side records), and resolve with transparency. If records cannot confirm delivery, credit the client. Then fix the record-keeping gap.',
            ],
            [
                'type' => 'long_answer',
                'title' => <<<'HTML'
<h5>Practical Exercise: Billing Discrepancy Analysis (20 points)</h5>
<p>Review the following data and <strong>identify all discrepancies</strong>. For each, state the <strong>issue</strong>, the <strong>financial impact</strong>, and your <strong>recommended resolution</strong>.</p>
<ul style="line-height:1.75;">
<li><strong>Client:</strong> Metro Hospital System</li>
<li><strong>Contract Rate:</strong> $55 per OPI session, $75 per VRI session</li>
<li><strong>March Delivery Report:</strong> 180 OPI sessions, 45 VRI sessions delivered</li>
<li><strong>March Invoice Sent:</strong> $9,900 for OPI (180 x $55), $3,000 for VRI (45 x $67), Total: $12,900</li>
<li><strong>March Payment Received:</strong> $12,900</li>
<li><strong>Interpreter Payroll for This Client:</strong> 180 OPI sessions at $28/session = $5,040. 45 VRI sessions at $38/session = $1,710. Total: $6,750.</li>
</ul>
HTML
            ],
        ];
    }
}
