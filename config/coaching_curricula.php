<?php

/**
 * Structured coaching curricula — Defrilex Academy COACH (guide), not generic assistant.
 * Each track progresses module by module, step by step.
 */
return [
    'advance_marker' => '[[COACH_ADVANCE]]',

    'tracks' => [
        'customer_service' => [
            'name' => 'Customer Service',
            'description' => 'Build rapport, handle inquiries, and resolve difficult situations.',
            'modules' => [
                [
                    'id' => 'cs_mindset',
                    'title' => 'Customer service mindset',
                    'steps' => [
                        ['id' => 'cs_s1', 'title' => 'What great service means', 'objective' => 'Define customer service as meeting and exceeding expectations with empathy.', 'practice' => 'Describe one moment when you felt truly well served as a customer.'],
                        ['id' => 'cs_s2', 'title' => 'Internal vs external customers', 'objective' => 'Recognize that colleagues are also customers and service culture starts internally.', 'practice' => 'Name one internal partner you support and what they need from you.'],
                        ['id' => 'cs_s3', 'title' => 'First impressions', 'objective' => 'Apply greeting standards: tone, pace, and clarity in the first 30 seconds.', 'practice' => 'Write a 2-sentence greeting you would use on phone and in person.'],
                    ],
                ],
                [
                    'id' => 'cs_communication',
                    'title' => 'Communication skills',
                    'steps' => [
                        ['id' => 'cs_s4', 'title' => 'Active listening', 'objective' => 'Use paraphrase, clarification questions, and silence to understand before responding.', 'practice' => 'Paraphrase this complaint: "I have called three times and nobody fixed my issue."'],
                        ['id' => 'cs_s5', 'title' => 'Telephone techniques', 'objective' => 'Apply professional phone etiquette: identification, hold protocol, and closing.', 'practice' => 'Draft how you place a caller on hold and return within 2 minutes.'],
                        ['id' => 'cs_s6', 'title' => 'Written communication', 'objective' => 'Write clear, courteous emails and chat messages with actionable next steps.', 'practice' => 'Rewrite a blunt email into a professional customer reply.'],
                    ],
                ],
                [
                    'id' => 'cs_resolution',
                    'title' => 'Resolution & recovery',
                    'steps' => [
                        ['id' => 'cs_s7', 'title' => 'De-escalation basics', 'objective' => 'Stay calm, acknowledge emotion, and separate the person from the problem.', 'practice' => 'List three phrases that validate frustration without admitting fault.'],
                        ['id' => 'cs_s8', 'title' => 'Problem-solving framework', 'objective' => 'Clarify issue, explore options, agree on solution, confirm follow-up.', 'practice' => 'Walk through the framework for a billing error scenario.'],
                        ['id' => 'cs_s9', 'title' => 'Service recovery', 'objective' => 'Turn a failure into loyalty through apology, fix, and prevention.', 'practice' => 'Propose a recovery plan when a delivery is two days late.'],
                    ],
                ],
            ],
        ],
        'sales' => [
            'name' => 'Sales',
            'description' => 'Prospecting, discovery, objection handling, and closing with integrity.',
            'modules' => [
                [
                    'id' => 'sales_foundations',
                    'title' => 'Sales foundations',
                    'steps' => [
                        ['id' => 'sl_s1', 'title' => 'Consultative selling', 'objective' => 'Shift from pitching to diagnosing customer needs before proposing solutions.', 'practice' => 'List three discovery questions for a new B2B prospect.'],
                        ['id' => 'sl_s2', 'title' => 'Value proposition', 'objective' => 'Articulate outcomes, not features, in one clear sentence.', 'practice' => 'Write your value proposition for your main product or service.'],
                        ['id' => 'sl_s3', 'title' => 'Sales process stages', 'objective' => 'Map prospecting, qualification, presentation, negotiation, and close.', 'practice' => 'Identify which stage is weakest in your current pipeline and why.'],
                    ],
                ],
                [
                    'id' => 'sales_conversations',
                    'title' => 'Sales conversations',
                    'steps' => [
                        ['id' => 'sl_s4', 'title' => 'Discovery calls', 'objective' => 'Run structured calls: agenda, questions, recap, and next step.', 'practice' => 'Outline a 15-minute discovery call agenda.'],
                        ['id' => 'sl_s5', 'title' => 'Objection handling', 'objective' => 'Listen, clarify, respond with evidence, and confirm understanding.', 'practice' => 'Respond to "Your price is too high" without discounting immediately.'],
                        ['id' => 'sl_s6', 'title' => 'Closing with integrity', 'objective' => 'Summarize mutual fit, confirm decision criteria, and propose clear next action.', 'practice' => 'Write a closing summary email after a successful demo.'],
                    ],
                ],
            ],
        ],
        'interpreters' => [
            'name' => 'Interpreters',
            'description' => 'Ethics, modes, accuracy, and professional boundaries.',
            'modules' => [
                [
                    'id' => 'int_ethics',
                    'title' => 'Ethics & role',
                    'steps' => [
                        ['id' => 'in_s1', 'title' => 'Impartiality', 'objective' => 'Maintain neutrality and avoid advocacy unless safety requires intervention.', 'practice' => 'Describe a situation where neutrality is tested and how you would respond.'],
                        ['id' => 'in_s2', 'title' => 'Confidentiality', 'objective' => 'Protect all information learned in the encounter per professional standards.', 'practice' => 'List what you may and may not share after an assignment.'],
                        ['id' => 'in_s3', 'title' => 'Scope of practice', 'objective' => 'Decline assignments outside your training, certification, or language skill.', 'practice' => 'When would you request a team interpreter or decline a job?'],
                    ],
                ],
                [
                    'id' => 'int_modes',
                    'title' => 'Modes & accuracy',
                    'steps' => [
                        ['id' => 'in_s4', 'title' => 'Consecutive vs simultaneous', 'objective' => 'Choose the appropriate mode for setting, pace, and number of speakers.', 'practice' => 'Match each scenario to consecutive, simultaneous, or sight translation.'],
                        ['id' => 'in_s5', 'title' => 'Complete transmission', 'objective' => 'Render full meaning without omission, summary, or embellishment.', 'practice' => 'Explain why summary interpreting is inappropriate in legal settings.'],
                        ['id' => 'in_s6', 'title' => 'Managing challenges', 'objective' => 'Handle unfamiliar terms, overlapping speech, and clarification requests professionally.', 'practice' => 'How do you ask for clarification without breaking role?'],
                    ],
                ],
            ],
        ],
        'marketing' => [
            'name' => 'Marketing',
            'description' => 'Audience, messaging, channels, and compliant promotion.',
            'modules' => [
                [
                    'id' => 'mkt_strategy',
                    'title' => 'Marketing strategy',
                    'steps' => [
                        ['id' => 'mk_s1', 'title' => 'Target audience', 'objective' => 'Define who you serve, their pain points, and where they pay attention.', 'practice' => 'Sketch one primary persona for your offer.'],
                        ['id' => 'mk_s2', 'title' => 'Core message', 'objective' => 'Craft a clear promise and proof points aligned to audience needs.', 'practice' => 'Write a one-sentence tagline and three supporting bullets.'],
                        ['id' => 'mk_s3', 'title' => 'Channel mix', 'objective' => 'Select channels by audience behavior, not trends alone.', 'practice' => 'Pick two channels and justify them for your persona.'],
                    ],
                ],
                [
                    'id' => 'mkt_execution',
                    'title' => 'Execution & compliance',
                    'steps' => [
                        ['id' => 'mk_s4', 'title' => 'Digital marketing basics', 'objective' => 'Understand SEO, email, social, and paid ads as a coordinated system.', 'practice' => 'Map one customer journey from awareness to conversion.'],
                        ['id' => 'mk_s5', 'title' => 'Truthful advertising', 'objective' => 'Avoid deceptive claims, disclose partnerships, and substantiate promises.', 'practice' => 'Identify one claim in a sample ad that would need proof or revision.'],
                        ['id' => 'mk_s6', 'title' => 'Measure what matters', 'objective' => 'Define KPIs tied to business goals, not vanity metrics.', 'practice' => 'Choose three KPIs for your next campaign and why.'],
                    ],
                ],
            ],
        ],
        'management' => [
            'name' => 'Management',
            'description' => 'Lead with clarity, develop people, and drive engagement.',
            'modules' => [
                [
                    'id' => 'mgr_leadership',
                    'title' => 'Leadership essentials',
                    'steps' => [
                        ['id' => 'mg_s1', 'title' => 'Expectations & trust', 'objective' => 'Set clear expectations and build psychological safety on your team.', 'practice' => 'Describe one expectation your team misunderstands and how to clarify it.'],
                        ['id' => 'mg_s2', 'title' => 'Coach in the moment', 'objective' => 'Give timely praise or redirection tied to specific behavior.', 'practice' => 'Script a 30-second coaching moment for a recurring mistake.'],
                        ['id' => 'mg_s3', 'title' => 'Development conversations', 'objective' => 'Hold growth-focused talks separate from performance ratings.', 'practice' => 'Prepare three open questions for a development check-in.'],
                    ],
                ],
                [
                    'id' => 'mgr_engagement',
                    'title' => 'Engagement & accountability',
                    'steps' => [
                        ['id' => 'mg_s4', 'title' => 'Recognition', 'objective' => 'Recognize contributions in ways that reinforce desired behaviors.', 'practice' => 'Plan one recognition action for this week.'],
                        ['id' => 'mg_s5', 'title' => 'Action plans', 'objective' => 'Co-create SMART actions after coaching conversations.', 'practice' => 'Turn a vague goal into a SMART action plan.'],
                        ['id' => 'mg_s6', 'title' => 'Follow-up rhythm', 'objective' => 'Schedule consistent follow-ups so coaching becomes habit, not event.', 'practice' => 'Design a simple weekly follow-up routine for your team.'],
                    ],
                ],
            ],
        ],
    ],
];
