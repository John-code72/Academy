<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestPractice6RemoteWorkLiteracySeeder extends Seeder
{
    public function run(): void
    {
        $title = 'test-pratice 6: Test 2.6 - Remote Work Literacy Assessment';

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
                'total_mark' => 48,
                'pass_mark' => 34,
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
Verifies that the candidate understands how to work effectively and professionally in a fully remote environment. Defrilex operates as a distributed company. Remote discipline is not optional - it is a core competency.

Who Should Take It:
All candidates. All roles.

Skills / Competencies Assessed:
Self-management, time management, asynchronous communication, workspace setup, proactive reporting, availability discipline, remote meeting etiquette, accountability without supervision.

Format:
12 scenario-based judgment questions.
15 minutes.

Scoring Rubric:
- Scenario judgment (12 Q): 48 points (4 points per correct answer, no partial credit)
- Pass Threshold: 34/48 (70%)
- Borderline / Review Needed: 28-33/48
- Fail: below 28/48

Result Triggers:
- Pass (34+): Advance
- Borderline (28-33): Advance with remote work readiness discussion in onboarding
- Fail (<28): Do not advance

Training / Remediation Link:
Defrilex Remote Work Standards module. Asynchronous communication training. Self-management and accountability training.
TEXT;
    }

    private function questions(): array
    {
        return [
            [
                'title' => 'Scenario: You wake up feeling slightly unwell but could still work at reduced capacity. Your team has a deadline today. What should you do?',
                'options' => [
                    'Work without telling anyone - push through.',
                    'Notify your manager immediately, communicate your reduced capacity, and ask how to prioritize your tasks given the deadline.',
                    'Call in sick and do not check in until tomorrow.',
                    'Work but only on easy tasks and hope no one notices the deadline items are late.',
                ],
                'answer' => 'Notify your manager immediately, communicate your reduced capacity, and ask how to prioritize your tasks given the deadline.',
            ],
            [
                'title' => 'Scenario: You are working from home and realize you have been stuck on a problem for over an hour with no progress. What is the best approach?',
                'options' => [
                    'Keep trying for the rest of the day - you will figure it out eventually.',
                    'Message your manager or a colleague, explain the problem clearly, what you have tried, and ask for guidance.',
                    'Switch to a different task and hope the problem resolves itself.',
                    'Wait until the next scheduled meeting to bring it up.',
                ],
                'answer' => 'Message your manager or a colleague, explain the problem clearly, what you have tried, and ask for guidance.',
            ],
            [
                'title' => 'Scenario: Your internet connection drops during an important client call. What should you do?',
                'options' => [
                    'Wait and hope it reconnects.',
                    'Immediately switch to your phone hotspot or backup connection, message the meeting organizer via chat to explain the disruption, and rejoin as quickly as possible.',
                    'Send an email apologizing after the call is over.',
                    'Cancel the rest of your day since your internet is unreliable.',
                ],
                'answer' => 'Immediately switch to your phone hotspot or backup connection, message the meeting organizer via chat to explain the disruption, and rejoin as quickly as possible.',
            ],
            [
                'title' => 'Scenario: You finished your assigned tasks for the day by 2 PM. There is no specific work queued for the afternoon. What should you do?',
                'options' => [
                    'Log off early - you finished your work.',
                    'Proactively message your manager about your availability, offer to take on additional tasks, or use the time for skill development or process improvement.',
                    'Watch videos until end of day.',
                    'Do personal errands and stay logged in.',
                ],
                'answer' => 'Proactively message your manager about your availability, offer to take on additional tasks, or use the time for skill development or process improvement.',
            ],
            [
                'title' => 'Scenario: A colleague sends you a Slack message at 8 PM asking for something non-urgent. What is the appropriate response?',
                'options' => [
                    'Respond immediately regardless of the hour.',
                    'Acknowledge it the next morning during your work hours, unless it is flagged as urgent.',
                    'Ignore it entirely.',
                    'Tell them they should not message after hours.',
                ],
                'answer' => 'Acknowledge it the next morning during your work hours, unless it is flagged as urgent.',
            ],
            [
                'title' => 'Scenario: You have a scheduled video meeting but your home environment is noisy (construction outside, family members present). What should you do?',
                'options' => [
                    'Join with your camera and microphone on and hope for the best.',
                    'Find the quietest space available, use a headset, mute when not speaking, and briefly apologize for any background noise if necessary.',
                    'Skip the meeting and ask for notes afterward.',
                    'Join audio-only and do not explain.',
                ],
                'answer' => 'Find the quietest space available, use a headset, mute when not speaking, and briefly apologize for any background noise if necessary.',
            ],
            [
                'title' => 'Scenario: Your manager sends you a task on Monday morning with a Friday deadline. By Wednesday, you realize you will not finish by Friday. What should you do?',
                'options' => [
                    'Push through and deliver whatever you have on Friday without mentioning the issue.',
                    'Notify your manager on Wednesday (not Friday) that you are at risk of missing the deadline, explain why, and propose solutions (deadline extension, reduced scope, or help).',
                    'Wait until Thursday to mention it.',
                    'Quietly reduce the quality to meet the deadline.',
                ],
                'answer' => 'Notify your manager on Wednesday (not Friday) that you are at risk of missing the deadline, explain why, and propose solutions (deadline extension, reduced scope, or help).',
            ],
            [
                'title' => 'Scenario: You are asked to submit a daily end-of-day report. You had a day with mostly meetings and no significant output. What should you write?',
                'options' => [
                    '"Nothing to report."',
                    'Honestly document the day: meetings attended, key takeaways, decisions made, and what you plan to accomplish tomorrow. Flag that the meeting load impacted output time.',
                    'Make up tasks to look productive.',
                    'Skip the report for that day.',
                ],
                'answer' => 'Honestly document the day: meetings attended, key takeaways, decisions made, and what you plan to accomplish tomorrow. Flag that the meeting load impacted output time.',
            ],
            [
                'title' => 'Scenario: A team member in a different time zone needs your input to proceed with their work. They have been waiting for your response for 6 hours. What does this situation teach you about remote work?',
                'options' => [
                    'Time zones are their problem, not yours.',
                    'Asynchronous communication requires proactive responsiveness. When someone is blocked on your input, treating it as high priority reduces total team delay.',
                    'They should have called you instead of waiting.',
                    'This is why remote work does not really work.',
                ],
                'answer' => 'Asynchronous communication requires proactive responsiveness. When someone is blocked on your input, treating it as high priority reduces total team delay.',
            ],
            [
                'title' => 'Scenario: You are onboarding at Defrilex. After your first week, you have questions about your role but your manager seems very busy. What should you do?',
                'options' => [
                    'Wait for your manager to reach out to you.',
                    'Compile your questions in writing, schedule a brief check-in, and come prepared with specific questions to use the manager\'s time efficiently.',
                    'Ask random colleagues instead.',
                    'Assume you will figure it out on your own over time.',
                ],
                'answer' => 'Compile your questions in writing, schedule a brief check-in, and come prepared with specific questions to use the manager\'s time efficiently.',
            ],
            [
                'title' => 'Scenario: Your workspace at home has poor lighting and an unmade bed visible behind you on video calls. Before starting a client-facing role at Defrilex, what should you do?',
                'options' => [
                    'Do not worry about it - clients understand remote work.',
                    'Set up a clean, professional background (physical or virtual), ensure good lighting on your face, and test your video setup before your first call.',
                    'Use blur background and never turn your camera on.',
                    'Only take audio calls.',
                ],
                'answer' => 'Set up a clean, professional background (physical or virtual), ensure good lighting on your face, and test your video setup before your first call.',
            ],
            [
                'title' => 'Scenario: You completed a task and sent it to your manager, but received no acknowledgment after 24 hours. What should you do?',
                'options' => [
                    'Assume they received it and move on.',
                    'Send a polite follow-up message confirming they received the deliverable and asking if they need anything else.',
                    'Get offended and stop working.',
                    'Resend the same message five times.',
                ],
                'answer' => 'Send a polite follow-up message confirming they received the deliverable and asking if they need anything else.',
            ],
        ];
    }
}
