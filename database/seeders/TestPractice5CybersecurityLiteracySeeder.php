<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice5CybersecurityLiteracySeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 5: Test 2.5 - Cybersecurity Literacy Assessment';

        $lesson = Lesson::firstOrCreate(
            [
                'title' => $title,
                'lesson_type' => 'practice_test',
            ],
            [
                'user_id' => null,
                'course_id' => null,
                'section_id' => null,
                'duration' => '0:15:0',
                'total_mark' => 60,
                'pass_mark' => 42,
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
                    'type' => 'mcq',
                    'answer' => json_encode([$question['answer']]),
                    'options' => json_encode($question['options']),
                    'sort' => $index + 1,
                ]
            );
        }
    }

    private function description(): string
    {
        return <<<TEXT
Purpose:
Verifies that the candidate understands basic cybersecurity practices required to protect Defrilex, its clients, and their data. Defrilex serves regulated industries (healthcare, government, fintech) where data breaches create legal liability, client loss, and reputational damage.

Who Should Take It:
All candidates. All roles. All levels.

Skills / Competencies Assessed:
Phishing recognition, password management, data handling awareness, secure communication practices, incident reporting, device security, social engineering awareness.

Format:
15 questions: 10 multiple-choice, 5 scenario-based judgment.
15 minutes.

Scoring Rubric:
- Multiple-choice (10 Q): 30 points (3 points per correct answer)
- Scenario judgment (5 Q): 30 points (6 points each)
- Pass Threshold: 42/60 (70%)
- Borderline / Review Needed: 36-41/60
- Fail: below 36/60

Result Triggers:
- Pass (42+): Advance
- Borderline (36-41): Advance with mandatory cybersecurity onboarding module before any system access
- Fail (<36): Must complete cybersecurity training and retest before advancing

Training / Remediation Link:
Defrilex Cybersecurity Foundations module. Phishing awareness training. Data handling compliance training.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'title' => "You receive an email from 'IT-Support@defr1lex.com' asking you to click a link and verify your password. What should you do?",
                'options' => [
                    'Click the link and enter your password - IT needs it.',
                    'Do not click the link. The sender domain is suspicious (defr1lex instead of defrilex). Report it to your manager or IT.',
                    'Forward it to the whole team so they can verify too.',
                    'Reply asking if it is legitimate.',
                ],
                'answer' => 'Do not click the link. The sender domain is suspicious (defr1lex instead of defrilex). Report it to your manager or IT.',
            ],
            [
                'title' => 'Which of the following is the strongest password?',
                'options' => ['password123', 'Defrilex2026!', 'j7$Kp!mN2@xQ9vLz', 'MyDogFido'],
                'answer' => 'j7$Kp!mN2@xQ9vLz',
            ],
            [
                'title' => 'You are working from a coffee shop. Which of the following is the safest practice?',
                'options' => [
                    'Use the public WiFi and access all your work tools normally.',
                    "Use your phone's hotspot or a VPN to create a secure connection.",
                    'Ask the barista for the WiFi password and share it with your team.',
                    'Turn off your firewall so the public WiFi works better.',
                ],
                'answer' => "Use your phone's hotspot or a VPN to create a secure connection.",
            ],
            [
                'title' => "A colleague messages you on Slack: 'Hey, can you send me the client database password? I lost mine.' What should you do?",
                'options' => [
                    'Send the password - they are a colleague.',
                    'Tell them to reset their password through the official IT process. Never share credentials via chat.',
                    'Send it but tell them to delete the message afterward.',
                    'Look up the password and call them to share it verbally.',
                ],
                'answer' => 'Tell them to reset their password through the official IT process. Never share credentials via chat.',
            ],
            [
                'title' => "What is 'two-factor authentication' (2FA)?",
                'options' => [
                    'Using two different passwords for the same account.',
                    'A security method that requires two forms of verification (e.g., password + phone code) to access an account.',
                    'Logging in from two different devices at the same time.',
                    'A backup password in case you forget your main one.',
                ],
                'answer' => 'A security method that requires two forms of verification (e.g., password + phone code) to access an account.',
            ],
            [
                'title' => "You find a USB drive in the parking lot with a label that says 'Employee Salaries Q2.' What should you do?",
                'options' => [
                    'Plug it into your computer to see what is on it.',
                    'Turn it in to your manager or IT without plugging it in - it could contain malware.',
                    'Plug it in to find out who it belongs to so you can return it.',
                    "Keep it - finder's keepers.",
                ],
                'answer' => 'Turn it in to your manager or IT without plugging it in - it could contain malware.',
            ],
            [
                'title' => 'Which of the following is an example of social engineering?',
                'options' => [
                    'A hacker exploiting a software vulnerability.',
                    'Someone calling and pretending to be IT support to trick you into revealing your login credentials.',
                    'A computer virus spreading through email attachments.',
                    'A firewall blocking suspicious traffic.',
                ],
                'answer' => 'Someone calling and pretending to be IT support to trick you into revealing your login credentials.',
            ],
            [
                'title' => 'Your work laptop is stolen from your car. What should you do FIRST?',
                'options' => [
                    'Buy a new laptop.',
                    'Report the theft immediately to your manager and IT so the device can be remotely wiped and accounts can be secured.',
                    'Wait a few days to see if it turns up.',
                    'Post about it on social media.',
                ],
                'answer' => 'Report the theft immediately to your manager and IT so the device can be remotely wiped and accounts can be secured.',
            ],
            [
                'title' => 'How often should you update your work passwords?',
                'options' => [
                    'Never - a good password lasts forever.',
                    "Per your company's policy (typically every 60-90 days) or immediately if you suspect compromise.",
                    'Only when IT forces you to.',
                    'Once a year is sufficient.',
                ],
                'answer' => "Per your company's policy (typically every 60-90 days) or immediately if you suspect compromise.",
            ],
            [
                'title' => 'A client sends you sensitive patient information via regular unencrypted email. What should you do?',
                'options' => [
                    'Save it to your desktop and use it normally.',
                    'Acknowledge receipt, then inform your manager that unencrypted transmission of sensitive data is a compliance risk and work to establish a secure transfer method.',
                    'Forward it to your personal email as a backup.',
                    'Reply and ask them to send more details.',
                ],
                'answer' => 'Acknowledge receipt, then inform your manager that unencrypted transmission of sensitive data is a compliance risk and work to establish a secure transfer method.',
            ],
            [
                'title' => 'Scenario: You receive a phone call from someone claiming to be from your company IT department. They say there has been a data breach and they need your login credentials immediately to secure your account. What should you do?',
                'options' => [
                    'Provide your credentials - it is an emergency.',
                    'Hang up and contact IT through official channels (email, Slack, or known phone number) to verify the request.',
                    'Give them only your username but not your password.',
                    'Ask them to email you instead.',
                ],
                'answer' => 'Hang up and contact IT through official channels (email, Slack, or known phone number) to verify the request.',
            ],
            [
                'title' => 'Scenario: You notice that a coworker is using the same simple password for every tool and has it written on a sticky note on their monitor. What is the appropriate response?',
                'options' => [
                    'Mind your own business.',
                    'Privately and respectfully tell them this is a security risk and suggest they use a password manager.',
                    'Report them to HR immediately.',
                    'Take a photo of the sticky note for evidence.',
                ],
                'answer' => 'Privately and respectfully tell them this is a security risk and suggest they use a password manager.',
            ],
            [
                'title' => 'Scenario: A client asks you to email them a spreadsheet containing patient names and appointment details. Your company policy requires encrypted file transfer for this type of data. What should you do?',
                'options' => [
                    'Send it via regular email since the client asked for it.',
                    'Explain to the client that this data must be transferred via the secure method required by company policy, and offer to send it through the approved encrypted channel.',
                    'Send it via email but password-protect the file.',
                    'Refuse to send it at all.',
                ],
                'answer' => 'Explain to the client that this data must be transferred via the secure method required by company policy, and offer to send it through the approved encrypted channel.',
            ],
            [
                'title' => 'Scenario: You accidentally click on a suspicious link in an email. The page looked strange but you closed it immediately. What should you do?',
                'options' => [
                    'Nothing - you closed it so you are fine.',
                    'Report the incident to IT immediately, do not enter any credentials on any site until cleared, and note the email details.',
                    'Run your own antivirus scan and consider it resolved.',
                    'Delete the email and pretend it did not happen.',
                ],
                'answer' => 'Report the incident to IT immediately, do not enter any credentials on any site until cleared, and note the email details.',
            ],
            [
                'title' => 'Scenario: You are asked to share your screen during a video call with a client. You have other client confidential information open in another tab. What should you do before sharing your screen?',
                'options' => [
                    'Share your screen immediately - the client probably will not notice.',
                    'Close all tabs and windows containing confidential information for other clients before sharing, or share only the specific application window needed.',
                    'Ask the client to look away while you close things.',
                    'It does not matter - all your clients are in the same industry anyway.',
                ],
                'answer' => 'Close all tabs and windows containing confidential information for other clients before sharing, or share only the specific application window needed.',
            ],
        ];
    }
}
