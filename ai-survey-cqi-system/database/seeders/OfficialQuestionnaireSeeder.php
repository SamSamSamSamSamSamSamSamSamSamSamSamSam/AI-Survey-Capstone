<?php

namespace Database\Seeders;

use App\Models\QuestionCategory;
use App\Models\Scale;
use App\Models\SurveyTemplate;
use Illuminate\Database\Seeder;

/**
 * Seeds the official Adapted ISMIS Survey Questionnaire.
 *
 * Run with:
 *   php artisan db:seed --class=OfficialQuestionnaireSeeder
 *
 * Safe to re-run — uses firstOrCreate so it won't duplicate.
 */
class OfficialQuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Ensure a 5-point Likert scale exists ──────────────────────────
        $scale = Scale::firstOrCreate(
            ['name' => '5-Point Likert Scale'],
            ['min_value' => 1, 'max_value' => 5]
        );

        // Seed scale options if missing
        if ($scale->wasRecentlyCreated || $scale->options()->count() === 0) {
            $options = [
                [1, 'Strongly Disagree'],
                [2, 'Disagree'],
                [3, 'Neutral'],
                [4, 'Agree'],
                [5, 'Strongly Agree'],
            ];
            foreach ($options as [$value, $label]) {
                $scale->options()->firstOrCreate(
                    ['value' => $value],
                    ['label' => $label, 'order_number' => $value]
                );
            }
        }

        // ── 2. Ensure question categories exist ──────────────────────────────
        $catClassroom  = QuestionCategory::firstOrCreate(
            ['name' => 'Classroom Management'],
            ['description' => 'Questions about how the teacher manages the classroom environment.']
        );

        $catTeaching   = QuestionCategory::firstOrCreate(
            ['name' => 'Teaching and Learning'],
            ['description' => 'Questions about teaching strategies, content delivery, and student learning outcomes.']
        );

        $catAssessment = QuestionCategory::firstOrCreate(
            ['name' => 'Assessment'],
            ['description' => 'Questions about how student learning is evaluated.']
        );

        $catGeneral    = QuestionCategory::firstOrCreate(
            ['name' => 'General Feedback'],
            ['description' => 'Open-ended general questions about the overall course experience.']
        );

        // ── 3. Create the official template (idempotent) ─────────────────────
        $template = SurveyTemplate::firstOrCreate(
            ['name' => 'Adapted ISMIS Official Evaluation Questionnaire'],
            [
                'description' => 'The official university faculty evaluation instrument adapted from the ISMIS survey questionnaires. Covers Classroom Management, Teaching & Learning, Assessment, and General Feedback.',
                'is_official' => true,
                'is_active'   => true,
            ]
        );

        // If it already exists but questions were already seeded, skip.
        if (! $template->wasRecentlyCreated && $template->questions()->count() > 0) {
            $this->command->info('Official questionnaire already seeded — skipping.');
            return;
        }

        // ── 4. Seed questions ─────────────────────────────────────────────────
        $order = 1;

        // --- SECTION 1: Classroom Management ---
        $classroomRating = [
            'The teacher motivates us to participate in the activities.',
            'The teacher provides us opportunities to express our ideas.',
            'The teacher deals with our questions and comments.',
            'The teacher clarified directions on assignments and other requirements when needed.',
            'The teacher engages her students actively during class meetings.',
            'The teacher\'s teaching presence can be felt in asynchronous online class activities.',
            'The teacher responds to correspondence sent via email, through the LMS or through official channels within a reasonable time.',
        ];

        foreach ($classroomRating as $text) {
            $template->questions()->create([
                'question_text' => $text,
                'question_type' => 'rating',
                'category_id'   => $catClassroom->id,
                'scale_id'      => $scale->id,
                'order_number'  => $order++,
            ]);
        }

        $template->questions()->create([
            'question_text' => 'How has the classroom environment, teaching practices, or structure influenced your learning experience, and what improvements would you recommend?',
            'question_type' => 'text',
            'category_id'   => $catClassroom->id,
            'scale_id'      => null,
            'order_number'  => $order++,
        ]);

        // --- SECTION 2: Teaching and Learning ---
        $teachingRating = [
            'The teacher\'s teaching presence in regular class meetings (synchronous or face-to-face) motivates me to actively participate in this course.',
            'The course provided applications that relate to my program specialization and other relevant fields.',
            'The teacher used varied and engaging teaching strategies which facilitated my learning.',
            'The teacher integrated technology tools effectively which supported my learning.',
            'The pacing of course activities provided me adequate time to reflect and apply my learning.',
            'The teacher engages us with questions to deepen our learning.',
            'Assignments are designed to provide us opportunity to demonstrate our learning.',
            'The requirements are relevant to the stated unit or course outcomes.',
            'The requirements are well-paced to give me adequate time to work on them.',
            'The teacher provides us opportunities to reflect on our learning experiences.',
            'The syllabus is a well-organized plan that provides an overview of the course.',
            'The syllabus clearly describes to me what I will be able to learn and do at the end of the course.',
            'The syllabus provides varied learning resources that can support my learning.',
            'The learning plan in the syllabus shows the connections of the stated outcomes and content with learning activities and assessments.',
            'I can demonstrate the stated unit outcomes of the course with competence.',
            'I can apply the knowledge and skills I learned in this course to analyze problems, create products or perform processes.',
            'I can communicate my learning in this course orally or in written form.',
            'I can connect theory and practical knowledge of this course.',
            'I have improved my problem-solving, critical thinking and decision-making skills through this course.',
        ];

        foreach ($teachingRating as $text) {
            $template->questions()->create([
                'question_text' => $text,
                'question_type' => 'rating',
                'category_id'   => $catTeaching->id,
                'scale_id'      => $scale->id,
                'order_number'  => $order++,
            ]);
        }

        $template->questions()->create([
            'question_text' => 'Which teaching strategies or activities enhanced your understanding of the lessons, and what improvements can be made to clarify confusing topics and improve lesson delivery?',
            'question_type' => 'text',
            'category_id'   => $catTeaching->id,
            'scale_id'      => null,
            'order_number'  => $order++,
        ]);

        // --- SECTION 3: Assessment ---
        $assessmentRating = [
            'The course syllabus provides the information that serves as the basis for our grades (e.g. requirements, rubrics and grade components).',
            'The results of tests, assignments and other tasks are returned with feedback on my performance.',
            'The varied forms of assessments in this course enable me to track my own learning progress.',
            'The tests and other requirements that provide the basis for my grade are clearly communicated.',
        ];

        foreach ($assessmentRating as $text) {
            $template->questions()->create([
                'question_text' => $text,
                'question_type' => 'rating',
                'category_id'   => $catAssessment->id,
                'scale_id'      => $scale->id,
                'order_number'  => $order++,
            ]);
        }

        $template->questions()->create([
            'question_text' => 'Do you feel the assessments accurately reflect what was taught, and how would you prefer your learning to be evaluated?',
            'question_type' => 'text',
            'category_id'   => $catAssessment->id,
            'scale_id'      => null,
            'order_number'  => $order++,
        ]);

        // --- SECTION 4: General Open-Ended ---
        $generalOpen = [
            'What do you like best about this course?',
            'What aspects of this course would you want to be improved?',
            'What is your overall experience in this course?',
            'Will you recommend this course under the present instructor/professor?',
        ];

        foreach ($generalOpen as $text) {
            $template->questions()->create([
                'question_text' => $text,
                'question_type' => 'text',
                'category_id'   => $catGeneral->id,
                'scale_id'      => null,
                'order_number'  => $order++,
            ]);
        }

        $this->command->info("✓ Official questionnaire seeded: {$template->name}");
        $this->command->info("  Total questions: " . ($order - 1));
    }
}