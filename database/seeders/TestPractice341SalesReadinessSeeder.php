<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice341SalesReadinessSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 11: Test 3.4.1 - Sales Readiness Assessment (SDR / Account Manager)';

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
Evaluates whether a sales candidate has the prospecting judgment, discovery skill, qualification discipline, and consultative selling instincts required to sell trust-first into regulated industries. Defrilex does not sell on price or volume - it sells on trust, compliance, and quality.

Who Should Take It:
SDR candidates, Account Manager candidates, BD candidates. All sales levels.

Skills / Competencies Assessed:
Prospecting judgment, ICP understanding, discovery questioning, qualification discipline (knowing when NOT to pursue), objection handling, consultative vs. transactional selling, CRM discipline, pipeline management awareness.

Format:
8 scenario-based judgment questions + 1 discovery call role-play outline + 1 prospecting prioritization exercise.
30 minutes.

Scoring Rubric:
- Scenario judgment (8 Q): 40 points (5 points each)
- Discovery call outline: 15 points (opening/agenda 3, question quality and insight 7, close/next steps 5)
- Prospecting prioritization: 15 points (correct ranking logic 8, quality of reasoning 7; deduct if non-ICP prospect ranked highest)
- Pass Threshold: 49/70 (70%)
- Borderline / Review Needed: 42-48/70
- Fail: below 42/70

Suggested prioritization reference (evaluators): C (government, RFI - active buyer), A (healthcare, contract expiring, engaged), E (legal, quality pain), D (fintech ICP but cold), B (not ICP - lowest).

Result Triggers:
- Pass (49+): Advance to culture interview
- Borderline (42-48): Advance with sales training deep-dive planned
- Fail (<42): Do not advance

Training / Remediation Link:
Defrilex Sales Operating System. Consultative selling methodology. ICP and qualification training. Discovery call framework. CRM discipline standards.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'type' => 'mcq',
                'title' => 'Scenario: You are prospecting for Defrilex. You find a healthcare system with 15 hospitals. They currently use a competitor for interpretation services. Their contract renews in 8 months. What is the best next step?',
                'options' => [
                    'Send a cold email with Defrilex pricing to undercut the competitor.',
                    'Research their specific pain points, identify the decision-maker, and begin a long-cycle relationship-building outreach strategy. An 8-month runway is ideal for trust-building before renewal.',
                    'Wait until 2 months before renewal to reach out.',
                    'Call the front desk and ask to speak to whoever handles language services.',
                ],
                'answer' => 'Research their specific pain points, identify the decision-maker, and begin a long-cycle relationship-building outreach strategy. An 8-month runway is ideal for trust-building before renewal.',
            ],
            [
                'type' => 'mcq',
                'title' => "Scenario: During a discovery call, the prospect says: 'We are not really looking to switch providers right now. We just took the meeting out of curiosity.' How do you handle this?",
                'options' => [
                    'End the call - they are not interested.',
                    "Acknowledge their honesty and reframe: 'That is perfectly fine. My goal is not to sell you today but to understand your current setup so that if you ever do evaluate options, you already know what is possible. Can I ask a few questions about how things are working now?'",
                    'Push harder to create urgency.',
                    'Offer a discount to get them interested.',
                ],
                'answer' => "Acknowledge their honesty and reframe: 'That is perfectly fine. My goal is not to sell you today but to understand your current setup so that if you ever do evaluate options, you already know what is possible. Can I ask a few questions about how things are working now?'",
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A prospect seems very interested and wants to move fast, but they are a small company with only 5 employees, no regulated-industry clients, and a very small budget. What should you do?',
                'options' => [
                    'Close the deal quickly - revenue is revenue.',
                    'Qualify honestly: this prospect does not fit Defrilex\'s ICP. Politely explain that Defrilex may not be the best fit for their current needs. Recommend an alternative if appropriate. Do not waste pipeline space on bad-fit deals.',
                    'Offer a massive discount to make it work.',
                    'Add them to the pipeline at a high stage to make your numbers look good.',
                ],
                'answer' => 'Qualify honestly: this prospect does not fit Defrilex\'s ICP. Politely explain that Defrilex may not be the best fit for their current needs. Recommend an alternative if appropriate. Do not waste pipeline space on bad-fit deals.',
            ],
            [
                'type' => 'mcq',
                'title' => "Scenario: The prospect says: 'Your competitor offers the same services at 20% less.' What is the best response?",
                'options' => [
                    'We can match that price.',
                    'I understand price is a factor. Can I ask what matters most to you beyond price? In regulated industries, the cost of a quality failure often exceeds the savings on a lower-priced provider. Let me show you specifically how our QA process, compliance posture, and interpreter vetting protect your organization.',
                    'You get what you pay for.',
                    'Let me check with my manager about a discount.',
                ],
                'answer' => 'I understand price is a factor. Can I ask what matters most to you beyond price? In regulated industries, the cost of a quality failure often exceeds the savings on a lower-priced provider. Let me show you specifically how our QA process, compliance posture, and interpreter vetting protect your organization.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You have a discovery call with a government agency. They are asking detailed questions about compliance certifications you are not sure Defrilex currently holds. What do you do?',
                'options' => [
                    'Imply that Defrilex has all the certifications - you can figure it out later.',
                    "Be honest: 'That is an important question. I want to give you an accurate answer, so let me confirm the specific certifications we hold and get back to you by tomorrow. I do not want to misrepresent anything.'",
                    'Change the subject to avoid the question.',
                    'Make up certification names that sound plausible.',
                ],
                'answer' => "Be honest: 'That is an important question. I want to give you an accurate answer, so let me confirm the specific certifications we hold and get back to you by tomorrow. I do not want to misrepresent anything.'",
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You closed a deal and the sales-to-operations handoff is happening. Operations has concerns about the timeline you promised the client. What is the appropriate action?',
                'options' => [
                    'Tell ops to figure it out - the deal is closed.',
                    'Listen to the operations concerns. If the timeline is not feasible, proactively manage the client\'s expectations before there is a delivery failure. Never sell what ops cannot fulfill.',
                    'Ignore the concerns and hope it works out.',
                    'Blame ops for being slow.',
                ],
                'answer' => 'Listen to the operations concerns. If the timeline is not feasible, proactively manage the client\'s expectations before there is a delivery failure. Never sell what ops cannot fulfill.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: Your pipeline is below target this quarter. You have a prospect who might close but has not returned your last two emails. What do you do?',
                'options' => [
                    'Send five more emails this week.',
                    'Evaluate: have you provided genuine value in your outreach? Try a different channel (LinkedIn, phone). Provide a specific insight or resource relevant to their business. If they remain unresponsive after a reasonable sequence, move on and focus on healthier pipeline.',
                    'Mark them as closing this month in the CRM to improve your forecast numbers.',
                    'Give up on them entirely.',
                ],
                'answer' => 'Evaluate: have you provided genuine value in your outreach? Try a different channel (LinkedIn, phone). Provide a specific insight or resource relevant to their business. If they remain unresponsive after a reasonable sequence, move on and focus on healthier pipeline.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: A prospect asks you to send a proposal before you have completed a discovery call. How do you respond?',
                'options' => [
                    'Send a generic proposal immediately.',
                    'I would love to send you something relevant. To make sure the proposal actually addresses your specific situation, can we schedule a 30-minute call so I can tailor it to your needs? A generic proposal would not do your evaluation justice.',
                    'Refuse to send anything without a full sales cycle.',
                    'Send the proposal and hope it works.',
                ],
                'answer' => 'I would love to send you something relevant. To make sure the proposal actually addresses your specific situation, can we schedule a 30-minute call so I can tailor it to your needs? A generic proposal would not do your evaluation justice.',
            ],
            [
                'type' => 'long_answer',
                'title' => <<<'HTML'
<h5>Practical Exercise 1: Discovery Call Outline (15 points)</h5>
<p><strong>Scenario:</strong> You are preparing for a 30-minute discovery call with the VP of Patient Services at a mid-size hospital system. They currently use ad-hoc bilingual staff for most interpretation needs but have had compliance concerns.</p>
<p><strong>Write a discovery call outline</strong> that includes:</p>
<ul>
<li>Your opening (how you set the agenda)</li>
<li>5 specific discovery questions you would ask (explain what each reveals)</li>
<li>How you would close the call (next steps)</li>
</ul>
HTML
            ],
            [
                'type' => 'long_answer',
                'title' => <<<'HTML'
<h5>Practical Exercise 2: Prospecting Prioritization (15 points)</h5>
<p>You have 5 prospects in your pipeline. <strong>Rank them from highest to lowest priority</strong> and explain your reasoning for each ranking. Use what you know about Defrilex's ICP (regulated industries, trust-first buyers, compliance needs).</p>
<p><strong>Prospect A:</strong> Large regional hospital system (200 beds). Current interpretation provider contract expires in 6 months. VP of Patient Experience responded to your LinkedIn message.</p>
<p><strong>Prospect B:</strong> Small marketing agency. 10 employees. Needs occasional translation for ad copy. Found you on Google.</p>
<p><strong>Prospect C:</strong> State government agency responsible for Medicaid enrollment. No current language services provider. Issued an RFI last month.</p>
<p><strong>Prospect D:</strong> Large fintech company. 500 employees. Growing international customer base. No language service provider yet. Your cold email got no response.</p>
<p><strong>Prospect E:</strong> Mid-size law firm specializing in immigration. Currently uses freelance interpreters found on Craigslist. Partner expressed frustration with quality.</p>
HTML
            ],
        ];
    }
}
