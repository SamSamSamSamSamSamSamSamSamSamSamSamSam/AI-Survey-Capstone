<?php

namespace Database\Seeders;

use App\Models\QuestionCategory;
use App\Models\Scale;
use App\Models\ScaleOption;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use Illuminate\Database\Seeder;

class SurveyReferenceSeeder extends Seeder
{
    public function run(): void
    {
        // ------------------------------------------------------------------
        // 1. Default scale: 1–5 Likert
        // ------------------------------------------------------------------
        $scale = Scale::firstOrCreate(
            ['name' => '5-Point Likert Scale'],
            ['min_value' => 1, 'max_value' => 5]
        );

        $options = [
            [1, 'Strongly Disagree'],
            [2, 'Disagree'],
            [3, 'Neutral'],
            [4, 'Agree'],
            [5, 'Strongly Agree'],
        ];

        foreach ($options as $i => [$value, $label]) {
            ScaleOption::firstOrCreate(
                ['scale_id' => $scale->id, 'value' => $value],
                ['label' => $label, 'order_number' => $i + 1]
            );
        }

        // ------------------------------------------------------------------
        // 2. Default question categories
        // ------------------------------------------------------------------
        $categories = [
            ['name' => 'Teaching Effectiveness',    'description' => 'Questions about instructional quality and delivery.'],
            ['name' => 'Communication',              'description' => 'Clarity and responsiveness of the instructor.'],
            ['name' => 'Assessment & Feedback',      'description' => 'Fairness and timeliness of grading and feedback.'],
            ['name' => 'Course Content',             'description' => 'Relevance and organization of course material.'],
            ['name' => 'Student Engagement',         'description' => 'Instructor\'s ability to motivate and involve students.'],
            ['name' => 'Learning Environment',       'description' => 'Classroom atmosphere and inclusivity.'],
            ['name' => 'Overall Satisfaction',       'description' => 'General satisfaction with the course or instructor.'],
        ];

        $categoryMap = [];
        foreach ($categories as $cat) {
            $record = QuestionCategory::firstOrCreate(['name' => $cat['name']], $cat);
            $categoryMap[$cat['name']] = $record->id;
        }

        // ------------------------------------------------------------------
        // 3. Official University Questionnaire template
        // ------------------------------------------------------------------
        $template = SurveyTemplate::firstOrCreate(
            ['name' => 'Official University Faculty Evaluation Questionnaire'],
            [
                'description' => 'The standard university-wide faculty evaluation instrument.',
                'is_official'  => true,
                'is_active'    => true,
            ]
        );

        // Only seed questions if template is freshly created
        if ($template->questions()->count() === 0) {
            $questions = [
                // Teaching Effectiveness
                ['The instructor presents lessons in a clear and organized manner.',                    'Teaching Effectiveness',  'rating'],
                ['The instructor uses varied teaching strategies to facilitate learning.',               'Teaching Effectiveness',  'rating'],
                ['The instructor demonstrates mastery of the subject matter.',                          'Teaching Effectiveness',  'rating'],

                // Communication
                ['The instructor communicates course expectations clearly.',                            'Communication',           'rating'],
                ['The instructor is approachable and responsive to student concerns.',                  'Communication',           'rating'],

                // Assessment & Feedback
                ['The instructor provides timely and constructive feedback on assessments.',            'Assessment & Feedback',   'rating'],
                ['Grading criteria are fair, clear, and consistently applied.',                        'Assessment & Feedback',   'rating'],

                // Course Content
                ['The course content is relevant to my field of study.',                               'Course Content',          'rating'],
                ['The course materials and resources support my learning.',                             'Course Content',          'rating'],

                // Student Engagement
                ['The instructor encourages active participation and critical thinking.',               'Student Engagement',      'rating'],

                // Learning Environment
                ['The instructor fosters a respectful and inclusive learning environment.',             'Learning Environment',    'rating'],

                // Overall Satisfaction
                ['Overall, I am satisfied with the quality of instruction in this course.',            'Overall Satisfaction',    'rating'],

                // Open-ended
                ['What aspects of this course or instructor do you find most effective?',              'Overall Satisfaction',    'text'],
                ['What suggestions do you have for improving this course or teaching approach?',       'Overall Satisfaction',    'text'],
            ];

            foreach ($questions as $order => [$text, $catName, $type]) {
                SurveyTemplateQuestion::create([
                    'survey_template_id' => $template->id,
                    'question_text'      => $text,
                    'question_type'      => $type,
                    'category_id'        => $categoryMap[$catName],
                    'scale_id'           => $type === 'rating' ? $scale->id : null,
                    'order_number'       => $order + 1,
                ]);
            }
        }
    }
}
