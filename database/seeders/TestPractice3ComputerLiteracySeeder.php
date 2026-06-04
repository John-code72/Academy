<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice3ComputerLiteracySeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 3: Test 2.3 - Computer Literacy Assessment';

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
Verifies that the candidate has the foundational computer skills required to work in a remote, AI-native, technology-dependent environment. Defrilex operates entirely with cloud-based tools, CRM systems, video conferencing, and digital workflows. A team member who cannot navigate basic computer tasks creates operational drag.

Who Should Take It:
All candidates. All roles. All levels.

Skills / Competencies Assessed:
File management, email proficiency, cloud tool navigation (Google Workspace / Microsoft 365), browser competency, basic spreadsheet skills, video conferencing tool usage, keyboard shortcuts, file format knowledge, troubleshooting basics.

Format:
20 multiple-choice and scenario-based questions.
20 minutes.

Scoring Rubric:
- Multiple-choice (20 Q): 60 points (3 points per correct answer, no partial credit)
- Pass Threshold: 42/60 (70%)
- Borderline / Review Needed: 36-41/60
- Fail: below 36/60

Result Triggers:
- Pass (42+): Advance
- Borderline (36-41): Advance with tech coaching flag for non-technical roles only
- Fail (<36): Do not advance

Training / Remediation Link:
Defrilex Digital Workplace Foundations module. Cloud tools onboarding track. Computer literacy remediation.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'title' => 'What is the keyboard shortcut to copy selected text on most systems?',
                'options' => ['Ctrl + V', 'Ctrl + C', 'Ctrl + X', 'Ctrl + Z'],
                'answer' => 'Ctrl + C',
            ],
            [
                'title' => 'You need to send a large file (50 MB) to a client. What is the most professional approach?',
                'options' => [
                    'Attach it directly to the email.',
                    'Upload it to a cloud storage platform and share the link.',
                    'Compress it into multiple ZIP files and send several emails.',
                    'Print it and mail a physical copy.',
                ],
                'answer' => 'Upload it to a cloud storage platform and share the link.',
            ],
            [
                'title' => 'Which file format is best for sending a finalized document that should not be easily edited by the recipient?',
                'options' => ['.docx', '.xlsx', '.pdf', '.txt'],
                'answer' => '.pdf',
            ],
            [
                'title' => 'You are on a video call and your audio is not working. What is the best first troubleshooting step?',
                'options' => [
                    'Restart your computer immediately.',
                    'Check that the correct microphone is selected in the video call settings.',
                    'Send a message saying your computer is broken.',
                    'Leave the call and email the other participants instead.',
                ],
                'answer' => 'Check that the correct microphone is selected in the video call settings.',
            ],
            [
                'title' => 'What does BCC mean in an email, and when should you use it?',
                'options' => [
                    'Blind Carbon Copy — use it to send a copy to someone without other recipients seeing their address.',
                    'Basic Carbon Copy — use it for all group emails.',
                    'Backup Carbon Copy — use it to save a copy of the email.',
                    'Block Carbon Copy — use it to prevent replies.',
                ],
                'answer' => 'Blind Carbon Copy — use it to send a copy to someone without other recipients seeing their address.',
            ],
            [
                'title' => 'In Google Sheets or Excel, which formula would you use to add up all values in cells A1 through A10?',
                'options' => ['=ADD(A1:A10)', '=SUM(A1:A10)', '=TOTAL(A1:A10)', '=COUNT(A1:A10)'],
                'answer' => '=SUM(A1:A10)',
            ],
            [
                'title' => 'What is the purpose of a VPN in a remote work environment?',
                'options' => [
                    'To make your internet faster.',
                    'To create a secure, encrypted connection to protect data.',
                    'To block websites you should not visit.',
                    'To replace your WiFi router.',
                ],
                'answer' => 'To create a secure, encrypted connection to protect data.',
            ],
            [
                'title' => 'A colleague shares a Google Doc with you and asks for feedback. Where should you add your comments?',
                'options' => [
                    'Edit the text directly without telling them.',
                    'Use the comment feature (Ctrl+Alt+M or Insert > Comment).',
                    'Copy the document and email your version back.',
                    'Print it and write notes in pen.',
                ],
                'answer' => 'Use the comment feature (Ctrl+Alt+M or Insert > Comment).',
            ],
            [
                'title' => 'You accidentally delete an important file from your desktop. What should you do first?',
                'options' => [
                    'Accept the loss and recreate it from memory.',
                    'Check the Recycle Bin or Trash folder.',
                    'Call IT support immediately.',
                    'Restart your computer.',
                ],
                'answer' => 'Check the Recycle Bin or Trash folder.',
            ],
            [
                'title' => 'Which of these is NOT a cloud storage platform?',
                'options' => ['Google Drive', 'Dropbox', 'Microsoft Notepad', 'OneDrive'],
                'answer' => 'Microsoft Notepad',
            ],
            [
                'title' => "You receive an email with the subject line: 'URGENT: Your account has been compromised. Click here to verify.' What should you do?",
                'options' => [
                    'Click the link immediately to protect your account.',
                    'Forward it to your entire team as a warning.',
                    'Do not click the link. Report it to your IT team or manager as a potential phishing attempt.',
                    'Reply asking them to verify their identity.',
                ],
                'answer' => 'Do not click the link. Report it to your IT team or manager as a potential phishing attempt.',
            ],
            [
                'title' => 'What does Ctrl + Z do?',
                'options' => ['Zoom in', 'Close the application', 'Undo the last action', 'Save the file'],
                'answer' => 'Undo the last action',
            ],
            [
                'title' => "You need to join a Zoom meeting but the link says 'Meeting has not started.' The meeting was supposed to start 2 minutes ago. What do you do?",
                'options' => [
                    'Give up and email the host that you tried.',
                    'Wait a reasonable time (3-5 minutes), check your calendar for the correct link, and message the host if the issue persists.',
                    'Join a random Zoom room and hope it is the right one.',
                    'Restart your computer.',
                ],
                'answer' => 'Wait a reasonable time (3-5 minutes), check your calendar for the correct link, and message the host if the issue persists.',
            ],
            [
                'title' => 'What file extension indicates a spreadsheet file?',
                'options' => ['.pptx', '.xlsx', '.docx', '.pdf'],
                'answer' => '.xlsx',
            ],
            [
                'title' => "Which of the following best describes what 'the cloud' means in computing?",
                'options' => [
                    'A physical server in your office.',
                    'Remote servers accessed via the internet that store and process data.',
                    'A special type of WiFi network.',
                    'Software that only works offline.',
                ],
                'answer' => 'Remote servers accessed via the internet that store and process data.',
            ],
            [
                'title' => 'Scenario: Your manager asks you to create a shared folder where the whole team can upload documents and everyone can view them. What is the best approach?',
                'options' => [
                    'Create a folder on your local desktop and email each file individually.',
                    'Create a shared folder on Google Drive or SharePoint with appropriate access permissions.',
                    'Create a ZIP file and email it to everyone.',
                    'Post files one by one in a group chat.',
                ],
                'answer' => 'Create a shared folder on Google Drive or SharePoint with appropriate access permissions.',
            ],
            [
                'title' => "What does the 'Reply All' button do in email?",
                'options' => [
                    'Sends your reply only to the original sender.',
                    'Sends your reply to the sender and all other recipients on the email.',
                    'Forwards the email to your entire company.',
                    'Deletes the email and creates a new one.',
                ],
                'answer' => 'Sends your reply to the sender and all other recipients on the email.',
            ],
            [
                'title' => 'You are working on a document and your computer suddenly shuts down. What feature would have helped prevent data loss?',
                'options' => ['Larger monitor', 'Auto-save / cloud sync', 'Faster internet', 'More RAM'],
                'answer' => 'Auto-save / cloud sync',
            ],
            [
                'title' => 'Which keyboard shortcut selects all text in a document?',
                'options' => ['Ctrl + S', 'Ctrl + P', 'Ctrl + A', 'Ctrl + F'],
                'answer' => 'Ctrl + A',
            ],
            [
                'title' => 'A client sends you a .csv file. What type of data does this file typically contain?',
                'options' => [
                    'A presentation with slides.',
                    'A video recording.',
                    'Tabular data (rows and columns) that can be opened in a spreadsheet application.',
                    'An image file.',
                ],
                'answer' => 'Tabular data (rows and columns) that can be opened in a spreadsheet application.',
            ],
        ];
    }
}
