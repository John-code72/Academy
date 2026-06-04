<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice351MarketingReadinessSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 12: Test 3.5.1 - Marketing Readiness Assessment';

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
Evaluates whether a marketing candidate can produce trust-building, compliance-aware content and strategy for regulated-industry buyers. Defrilex's marketing must reduce perceived buyer risk, not just generate awareness.

Who Should Take It:
All marketing candidates: content, demand gen, brand, digital marketing.

Skills / Competencies Assessed:
Trust-first messaging, regulated-industry awareness, content strategy, brand voice discipline, audience understanding, conversion-oriented writing, channel strategy.

Format:
6 judgment questions + 2 practical exercises (messaging rewrite, content brief).
30 minutes.

Scoring Rubric:
- Judgment questions (6 Q): 30 points (5 points each)
- Messaging rewrite: 15 points (trust-first positioning 5, audience relevance 5, professional quality 5)
- Content brief: 15 points (audience clarity 3, key message strength 3, point quality 4, CTA relevance 2, SEO awareness 3)
- Pass Threshold: 42/60 (70%)
- Borderline / Review Needed: 36-41/60
- Fail: below 36/60

Result Triggers:
- Pass (42+): Advance
- Borderline (36-41): Advance with regulated-industry marketing training
- Fail (<36): Do not advance

Training / Remediation Link:
Defrilex Marketing Operating System. Trust-first messaging framework. Regulated-industry content strategy. Brand voice guidelines.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'type' => 'mcq',
                'title' => "Scenario: Defrilex's website currently says: 'We provide the best language services at the lowest prices!' What is wrong with this messaging for a company targeting regulated-industry buyers?",
                'options' => [
                    'Nothing - it is clear and compelling.',
                    'It emphasizes price over trust and quality, which is the opposite of what regulated-industry buyers care about. It sounds generic and does not address compliance, quality, or risk reduction.',
                    'It should say lowest prices guaranteed.',
                    'It needs more exclamation points.',
                ],
                'answer' => 'It emphasizes price over trust and quality, which is the opposite of what regulated-industry buyers care about. It sounds generic and does not address compliance, quality, or risk reduction.',
            ],
            [
                'type' => 'mcq',
                'title' => "Scenario: A healthcare compliance officer is evaluating Defrilex's website. What content would be MOST important for them to find? Choose the most critical content:",
                'options' => [
                    'Customer testimonials from small businesses.',
                    'Compliance certifications, HIPAA awareness, QA methodology, interpreter vetting process, and SLA commitments.',
                    'A flashy video about the company culture.',
                    'Pricing tables.',
                ],
                'answer' => 'Compliance certifications, HIPAA awareness, QA methodology, interpreter vetting process, and SLA commitments.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You are writing a case study about a healthcare client engagement. The client had a 15% improvement in patient satisfaction scores after using Defrilex. What is the most effective way to present this?',
                'options' => [
                    'Just state the number: Patient satisfaction improved 15%.',
                    'Frame the full story: the challenge (language barriers causing patient dissatisfaction and compliance risk), the solution (Defrilex managed interpretation service with QA scoring), and the specific, measurable result with context for why it matters to similar buyers.',
                    'Exaggerate it to 25% to be more impressive.',
                    'Do not use numbers - they are boring.',
                ],
                'answer' => 'Frame the full story: the challenge (language barriers causing patient dissatisfaction and compliance risk), the solution (Defrilex managed interpretation service with QA scoring), and the specific, measurable result with context for why it matters to similar buyers.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Which marketing channel is typically MOST effective for reaching regulated-industry enterprise buyers?',
                'options' => [
                    'TikTok ads.',
                    'Targeted LinkedIn outreach, industry conference presence, and thought leadership content that addresses their specific compliance and operational challenges.',
                    'Mass email blasts to purchased lists.',
                    'Facebook ads targeting healthcare workers.',
                ],
                'answer' => 'Targeted LinkedIn outreach, industry conference presence, and thought leadership content that addresses their specific compliance and operational challenges.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: Sales asks you to create a one-pager that makes Defrilex look like a large, established company even though it is early-stage. How should you handle this?',
                'options' => [
                    'Create the one-pager as requested - sales knows what clients want.',
                    'Create a one-pager that is credible and compelling without misrepresenting company size. Focus on quality of service, team expertise, compliance posture, and specific results. Regulated buyers value substance over size.',
                    'Refuse - marketing should not support sales.',
                    'Include fake client logos to look bigger.',
                ],
                'answer' => 'Create a one-pager that is credible and compelling without misrepresenting company size. Focus on quality of service, team expertise, compliance posture, and specific results. Regulated buyers value substance over size.',
            ],
            [
                'type' => 'mcq',
                'title' => 'Scenario: You notice that Defrilex social media posts are getting engagement from individual language learners but not from enterprise procurement people. What does this tell you and what should you do?',
                'options' => [
                    'Engagement is engagement - keep doing what works.',
                    'The content strategy is attracting the wrong audience. Shift content toward enterprise topics: compliance challenges, SLA frameworks, cost-of-poor-quality analysis, case studies. Target content to where decision-makers are, not where general interest is.',
                    'Post more frequently to get more of the same engagement.',
                    'Stop using social media entirely.',
                ],
                'answer' => 'The content strategy is attracting the wrong audience. Shift content toward enterprise topics: compliance challenges, SLA frameworks, cost-of-poor-quality analysis, case studies. Target content to where decision-makers are, not where general interest is.',
            ],
            [
                'type' => 'long_answer',
                'title' => <<<'HTML'
<h5>Practical Exercise 1: Messaging Rewrite (15 points)</h5>
<p>Rewrite the following <strong>homepage headline and subheadline</strong> for Defrilex, targeting a <strong>VP of Patient Services</strong> at a hospital system. Your messaging must <strong>reduce perceived buyer risk</strong> and emphasize <strong>trust, quality, and compliance</strong>.</p>
<p><strong>Current:</strong> "Fast, Affordable Language Services for Everyone! Get started in minutes!"</p>
<p>Provide your rewritten headline and subheadline below.</p>
HTML
            ],
            [
                'type' => 'long_answer',
                'title' => <<<'HTML'
<h5>Practical Exercise 2: Content Brief (15 points)</h5>
<p>Write a <strong>content brief</strong> for a blog post titled: <strong>"Why Bilingual Staff Are Not a Substitute for Professional Medical Interpreters."</strong></p>
<p>Include:</p>
<ul>
<li>Target audience</li>
<li>Key message</li>
<li>5 main points to cover</li>
<li>Desired CTA</li>
<li>SEO keywords</li>
</ul>
HTML
            ],
        ];
    }
}
