<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice391ItCybersecurityReadinessSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 16: Test 3.9.1 - IT and Cybersecurity Readiness Assessment';

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
Evaluates whether an IT/cybersecurity candidate has the technical knowledge, incident response judgment, and compliance awareness required to protect Defrilex's infrastructure and its regulated-industry clients' data.

Who Should Take It:
All IT and cybersecurity candidates: IT support, systems administrators, security analysts, compliance-focused IT roles.

Skills / Competencies Assessed:
Network security fundamentals, incident response, compliance framework awareness (HIPAA, SOC 2), access control management, vulnerability management, encryption understanding, security policy enforcement, vendor security assessment.

Format:
PART A: 10 technical knowledge questions. PART B: 3 scenario-based judgment questions. Practical exercise: incident response plan.
30 minutes.

Scoring Rubric:
- Technical knowledge (10 Q): 30 points (3 points each)
- Scenario judgment (3 Q): 18 points (6 points each)
- Incident response plan: 22 points (containment 4, investigation 4, notification 4, remediation 4, prevention 4, order/completeness 2)
- Pass Threshold: 49/70 (70%). Must score 15+ on incident response plan.
- Borderline / Review Needed: 42-48/70
- Fail: below 42/70

Result Triggers:
- Pass (49+): Advance
- Borderline (42-48): Advance with compliance-focused onboarding
- Fail (<42): Do not advance

Training / Remediation Link:
Defrilex Cybersecurity and Compliance module. HIPAA compliance training. Incident response protocol. Vendor security assessment training. SOC 2 preparation.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'type' => 'mcq',
                'title' => 'PART A — Which encryption standard is most commonly required for protecting health data at rest?',
                'options' => ['MD5', 'AES-256', 'Base64', 'ROT13'],
                'answer' => 'AES-256',
            ],
            [
                'type' => 'mcq',
                'title' => 'PART A — What is the principle of least privilege?',
                'options' => [
                    'Giving all employees admin access for efficiency.',
                    'Granting users only the minimum access permissions necessary to perform their job functions.',
                    'Restricting access only for contractors.',
                    'Giving maximum access to senior staff.',
                ],
                'answer' => 'Granting users only the minimum access permissions necessary to perform their job functions.',
            ],
            [
                'type' => 'mcq',
                'title' => 'PART A — Defrilex processes Protected Health Information (PHI) for healthcare clients. Which regulation primarily governs this data?',
                'options' => ['GDPR', 'PCI-DSS', 'HIPAA', 'SOX'],
                'answer' => 'HIPAA',
            ],
            [
                'type' => 'mcq',
                'title' => 'PART A — What is a Business Associate Agreement (BAA) and why does Defrilex need one?',
                'options' => [
                    'A partnership agreement for revenue sharing.',
                    'A legally required agreement between a covered entity and a business associate (like Defrilex) that handles PHI, defining data protection obligations.',
                    'An employment contract.',
                    'A marketing agreement.',
                ],
                'answer' => 'A legally required agreement between a covered entity and a business associate (like Defrilex) that handles PHI, defining data protection obligations.',
            ],
            [
                'type' => 'mcq',
                'title' => 'PART A — Which of the following is the MOST effective defense against phishing attacks?',
                'options' => [
                    'Antivirus software.',
                    'A combination of employee security awareness training, email filtering, multi-factor authentication, and a culture of reporting suspicious communications.',
                    'Blocking all external emails.',
                    'Stronger passwords.',
                ],
                'answer' => 'A combination of employee security awareness training, email filtering, multi-factor authentication, and a culture of reporting suspicious communications.',
            ],
            [
                'type' => 'mcq',
                'title' => 'PART A — What is the purpose of a SOC 2 audit?',
                'options' => [
                    'To audit financial statements.',
                    "To evaluate an organization's information security controls based on trust service criteria (security, availability, processing integrity, confidentiality, privacy).",
                    'To check employee performance.',
                    'To audit marketing claims.',
                ],
                'answer' => "To evaluate an organization's information security controls based on trust service criteria (security, availability, processing integrity, confidentiality, privacy).",
            ],
            [
                'type' => 'mcq',
                'title' => 'PART A — What does MFA stand for and why is it important?',
                'options' => [
                    'Multi-File Archive - important for data compression.',
                    'Multi-Factor Authentication - requires two or more verification methods, significantly reducing the risk of unauthorized access even if passwords are compromised.',
                    'Master File Access - important for database management.',
                    'Multiple Firewall Architecture - important for network security.',
                ],
                'answer' => 'Multi-Factor Authentication - requires two or more verification methods, significantly reducing the risk of unauthorized access even if passwords are compromised.',
            ],
            [
                'type' => 'mcq',
                'title' => 'PART A — A Defrilex interpreter uses their personal laptop for VRI sessions. What security concern does this create?',
                'options' => [
                    'No concern - personal devices are fine for remote work.',
                    'Personal devices may lack required security controls (encryption, endpoint protection, patch management), creating data exposure risk for client PHI and session content.',
                    'It saves the company money.',
                    'Personal devices are more secure than company devices.',
                ],
                'answer' => 'Personal devices may lack required security controls (encryption, endpoint protection, patch management), creating data exposure risk for client PHI and session content.',
            ],
            [
                'type' => 'mcq',
                'title' => 'PART A — What is a vulnerability scan and how often should it be performed?',
                'options' => [
                    'A scan for computer viruses. Once a year.',
                    'An automated assessment of systems and applications for known security weaknesses. Should be performed regularly (at least quarterly, monthly for critical systems) with findings prioritized and remediated.',
                    'A background check on employees. At hiring.',
                    'A review of financial records. Quarterly.',
                ],
                'answer' => 'An automated assessment of systems and applications for known security weaknesses. Should be performed regularly (at least quarterly, monthly for critical systems) with findings prioritized and remediated.',
            ],
            [
                'type' => 'mcq',
                'title' => 'PART A — What is the purpose of an incident response plan?',
                'options' => [
                    'To prevent all security incidents from happening.',
                    'To define a structured, pre-planned process for detecting, containing, eradicating, and recovering from security incidents, minimizing damage and ensuring proper communication and documentation.',
                    'To punish employees who cause incidents.',
                    'To satisfy insurance requirements only.',
                ],
                'answer' => 'To define a structured, pre-planned process for detecting, containing, eradicating, and recovering from security incidents, minimizing damage and ensuring proper communication and documentation.',
            ],
            [
                'type' => 'mcq',
                'title' => 'PART B — Scenario: A Defrilex employee reports that their work account was accessed from an unfamiliar location at 3 AM. They were asleep at the time. What is your immediate response?',
                'options' => [
                    'Change their password and move on.',
                    'Immediately: lock the compromised account, check for data access or exfiltration, review login logs for scope, reset credentials, enable/verify MFA, check for lateral movement to other accounts, notify the employee and management, and document the incident.',
                    'Wait until morning to investigate.',
                    'Tell the employee to be more careful.',
                ],
                'answer' => 'Immediately: lock the compromised account, check for data access or exfiltration, review login logs for scope, reset credentials, enable/verify MFA, check for lateral movement to other accounts, notify the employee and management, and document the incident.',
            ],
            [
                'type' => 'mcq',
                'title' => "PART B — Scenario: Defrilex is evaluating a new cloud-based scheduling tool. The vendor says their platform is 'fully secure.' What should you do before approving the tool?",
                'options' => [
                    'Trust the vendor - they said it is secure.',
                    'Conduct a vendor security assessment: request SOC 2 report, review their data handling policies, confirm BAA capability for PHI, evaluate encryption practices, assess data residency, and verify incident response procedures.',
                    'Ask for a discount instead.',
                    'Check online reviews.',
                ],
                'answer' => 'Conduct a vendor security assessment: request SOC 2 report, review their data handling policies, confirm BAA capability for PHI, evaluate encryption practices, assess data residency, and verify incident response procedures.',
            ],
            [
                'type' => 'mcq',
                'title' => 'PART B — Scenario: A healthcare client asks Defrilex to provide evidence of its security posture as part of their vendor risk assessment. How should you respond?',
                'options' => [
                    'Tell them your security is fine and they should trust you.',
                    'Prepare and provide: security policies, SOC 2 report or equivalent, BAA execution status, encryption practices, employee training records, incident response plan summary, and any relevant compliance certifications. This is standard for regulated-industry vendor relationships.',
                    'Refuse - your security practices are proprietary.',
                    'Send a generic IT overview.',
                ],
                'answer' => 'Prepare and provide: security policies, SOC 2 report or equivalent, BAA execution status, encryption practices, employee training records, incident response plan summary, and any relevant compliance certifications. This is standard for regulated-industry vendor relationships.',
            ],
            [
                'type' => 'long_answer',
                'title' => <<<'HTML'
<h5>Practical Exercise: Incident Response Plan (22 points)</h5>
<p><strong>Scenario:</strong> A Defrilex employee accidentally sent a spreadsheet containing patient names and appointment details (PHI) to the wrong email address (an external recipient). The employee noticed the mistake 10 minutes after sending.</p>
<p>Write the <strong>incident response steps</strong> Defrilex should take, <strong>in order</strong>. Include:</p>
<ul>
<li>Immediate containment</li>
<li>Investigation</li>
<li>Notification requirements</li>
<li>Remediation</li>
<li>Prevention measures</li>
</ul>
HTML
            ],
        ];
    }
}
