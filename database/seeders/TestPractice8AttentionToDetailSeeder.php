<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice8AttentionToDetailSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 8: Test 2.8 - Attention to Detail Assessment';

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
                'total_mark' => 50,
                'pass_mark' => 35,
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
Measures the candidate's ability to catch errors, inconsistencies, and inaccuracies in data, text, and processes. In regulated-industry services, a single overlooked error can create compliance violations, client trust damage, or financial loss.

Who Should Take It:
All candidates. All roles. Especially weighted for operations, QA, finance, and client-facing roles.

Skills / Competencies Assessed:
Error detection in text, numerical accuracy, pattern recognition, consistency checking, data validation, process compliance awareness.

Format:
4 exercises: data error detection, text proofreading, email inconsistency check, process checklist validation.
20 minutes.

Scoring Rubric:
- Exercise 1: Data Errors (5 items): 15 pts (3 points per correctly identified error)
- Exercise 2: Email Problems (5 items): 10 pts (2 points per correctly identified problem)
- Exercise 3: Checklist Issues (5 items): 15 pts (3 points per correctly identified issue)
- Exercise 4: Number Discrepancies (5 items): 10 pts (2 points per correctly identified discrepancy)
- Pass Threshold: 35/50 (70%)
- Borderline / Review Needed: 25-34/50
- Fail: below 25/50

Grading Answer Keys (for reviewers):

Exercise 1:
1. 'Frnech' should be 'French' (Row 2)
2. Maria Santos double-booked — 9:00 AM and 9:30 AM on same date (Rows 1 and 4); scheduling conflict / insufficient gap
3. Invalid date '13/17/2026' — no 13th month (Row 5)
4. 'Confrimed' should be 'Confirmed' (Row 6)
5. Maria Santos overlap / double-booking creates scheduling conflict (tie to rows 1 and 4)

Exercise 2:
1. Subject says 'Great Quarter' but results are mixed (below target on closes, retention down)
2. 'Outstanding quarter all around' contradicts the data (missed client target, retention declined)
3. 'Lead' should be 'led' (grammar — team lead by Marcus)
4. Retention declined from 94% to 91% but email frames everything as positive — tone/intellectual honesty
5. Closing 12 vs 15 target — miss not adequately acknowledged

Exercise 3:
1. Missing: BAA/compliance documentation step (healthcare) before service begins
2. Out of order: Kickoff call (Step 4) should come before interpreter scheduling (Step 3)
3. Missing: Interpreter vetting / language pair confirmation for client needs
4. Missing: SLA/quality standards documentation shared with client
5. Out of order: Welcome packet (Step 6) should come before or with kickoff (Step 4), not only after scheduling

Exercise 4:
1. Total sessions: summary says 847, data says 874
2. Language pairs: summary says 12, data says 14
3. QA score: summary says 94.2%, data says 92.4%
4. Spanish called highest-volume at 312, but Haitian Creole had 318
5. QA improvement overstated — actual Feb 91.8% to Mar 92.4%, not to 94.2%

Result Triggers:
- Pass (35+): Advance
- Borderline (25-34): Advance with coaching flag; weight for detail-critical roles
- Fail (<25): Do not advance for detail-critical roles unless other signals are very strong

Training / Remediation Link:
Defrilex Quality Assurance Standards module. Data verification practices. Process compliance training.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'title' => <<<'HTML'
<h5>Exercise 1: Data Error Detection (15 points)</h5>
<p>Review the following interpreter scheduling data table. <strong>Find and list all errors</strong> (there are 5 errors). Each correctly identified error is worth 3 points. Number your answers clearly.</p>
<table class="table table-bordered table-sm" style="font-size:14px;">
<thead><tr><th>Interpreter</th><th>Language</th><th>Date</th><th>Time</th><th>Client</th><th>Status</th></tr></thead>
<tbody>
<tr><td>Maria Santos</td><td>Spanish</td><td>03/15/2026</td><td>9:00 AM</td><td>HealthFirst Clinic</td><td>Confirmed</td></tr>
<tr><td>Jean-Pierre Duval</td><td>Frnech</td><td>03/15/2026</td><td>10:30 AM</td><td>Metro Hospital</td><td>Confirmed</td></tr>
<tr><td>Yuki Tanaka</td><td>Japanese</td><td>03/16/2026</td><td>2:00 PM</td><td>City Gov't Office</td><td>Pending</td></tr>
<tr><td>Maria Santos</td><td>Spanish</td><td>03/15/2026</td><td>9:30 AM</td><td>Sunrise Medical</td><td>Confirmed</td></tr>
<tr><td>Ahmed Hassan</td><td>Arabic</td><td>13/17/2026</td><td>11:00 AM</td><td>Legal Aid Society</td><td>Confirmed</td></tr>
<tr><td>Li Wei</td><td>Mandarin</td><td>03/17/2026</td><td>3:00 PM</td><td>FinTrust Bank</td><td>Confrimed</td></tr>
<tr><td>Jean-Pierre Duval</td><td>French</td><td>03/18/2026</td><td>9:00 AM</td><td>Metro Hospital</td><td>Pending</td></tr>
</tbody>
</table>
HTML
            ],
            [
                'title' => <<<'HTML'
<h5>Exercise 2: Email Proofreading (10 points)</h5>
<p>Read the following internal email. <strong>Identify 5 problems</strong> (factual inconsistencies, tone issues, or errors). Each correctly identified problem is worth 2 points.</p>
<div style="border:1px solid #e2e8f0;padding:16px;border-radius:8px;background:#f8fafc;">
<p><strong>Subject:</strong> Quarterly Results — Great Quarter!</p>
<p>Team,</p>
<p>I wanted to share that Q1 results are in. We closed 12 new clients this quarter, which is below our target of 15. Our revenue grew by 18% compared to last quarter. This was an outstanding quarter all around and everyone should be proud.</p>
<p>Client retention was 91%, down from 94% last quarter. Also, the new interpreter onboarding process reduced average time-todeploy from 14 days to 9 days, which is a huge win for the Operations team lead by Marcus.</p>
<p>Looking forward to an even better Q2!</p>
<p>Best,<br>Fritz</p>
</div>
HTML
            ],
            [
                'title' => <<<'HTML'
<h5>Exercise 3: Process Checklist Validation (15 points)</h5>
<p>Below is a checklist for onboarding a new healthcare client at Defrilex. <strong>Review it and identify 5 missing or out-of-order steps</strong> that would create risk. Each correctly identified issue is worth 3 points.</p>
<ol style="line-height:1.8;">
<li>Receive signed contract from sales team.</li>
<li>Assign account manager.</li>
<li>Begin interpreter scheduling for first sessions.</li>
<li>Conduct client kickoff call.</li>
<li>Set up client in billing system.</li>
<li>Send welcome packet to client.</li>
<li>Launch service delivery.</li>
<li>Conduct 30-day review with client.</li>
</ol>
HTML
            ],
            [
                'title' => <<<'HTML'
<h5>Exercise 4: Number Verification (10 points)</h5>
<p>The following summary paragraph describes performance metrics. <strong>Verify whether the paragraph's claims are consistent with the data provided below it.</strong> Identify any discrepancies (there are 5). Each correctly identified discrepancy is worth 2 points.</p>
<div style="border:1px solid #e2e8f0;padding:16px;border-radius:8px;margin-bottom:12px;">
<p><strong>Summary:</strong> "In March 2026, Defrilex completed 847 interpretation sessions across 12 language pairs. The average QA score was 94.2%, up from 91.8% in February. Client satisfaction reached 4.7 out of 5.0. Response time averaged 3.2 minutes, meeting our SLA of under 5 minutes. Spanish was our highest-volume language at 312 sessions."</p>
</div>
<p><strong>Actual Data:</strong></p>
<ul>
<li>Total sessions: 874.</li>
<li>Language pairs served: 14.</li>
<li>Average QA score: 92.4% (February: 91.8%).</li>
<li>Client satisfaction: 4.7/5.0.</li>
<li>Average response time: 3.2 minutes.</li>
<li>SLA target: under 5 minutes.</li>
<li>Spanish sessions: 312.</li>
<li>Haitian Creole sessions: 318.</li>
</ul>
HTML
            ],
        ];
    }
}
