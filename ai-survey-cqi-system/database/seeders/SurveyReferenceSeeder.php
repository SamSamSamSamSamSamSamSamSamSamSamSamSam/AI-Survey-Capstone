<?php

namespace Database\Seeders;

use App\Models\QuestionCategory;
use App\Models\Scale;
use App\Models\ScaleOption;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use App\Models\QuestionCategory as Category;
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
        $categoryMap = [
            'classroom' => Category::where('name', 'Classroom Management')->first(),
            'teaching'  => Category::where('name', 'Teaching and Learning')->first(),
            'assessment'=> Category::where('name', 'Assessment')->first(),
            'feedback'  => Category::where('name', 'General Feedback')->first(),
        ];

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
                ['The instructor presents lessons in a clear and organized manner.',                    'classroom',  'rating'],
                ['The instructor uses varied teaching strategies to facilitate learning.',               'classroom',  'rating'],
                ['The instructor demonstrates mastery of the subject matter.',                          'classroom',  'rating'],

                // Communication
                ['The instructor communicates course expectations clearly.',                            'teaching',           'rating'],
                ['The instructor is approachable and responsive to student concerns.',                  'teaching',           'rating'],

                // Assessment & Feedback
                ['The instructor provides timely and constructive feedback on assessments.',            'assessment',   'rating'],
                ['Grading criteria are fair, clear, and consistently applied.',                        'assessment',   'rating'],
                // Overall Satisfaction
                ['Overall, I am satisfied with the quality of instruction in this course.',            'feedback',    'rating'],

                // Open-ended
                ['What aspects of this course or instructor do you find most effective?',              'feedback',    'text'],
                ['What suggestions do you have for improving this course or teaching approach?',       'feedback',    'text'],
            ];

            foreach ($questions as $order => [$text, $catName, $type]) {
                SurveyTemplateQuestion::create([
                    'survey_template_id' => $template->id,
                    'question_text'      => $text,
                    'question_type'      => $type,
                    'category_id'        => $categoryMap[$catName]->id,
                    'scale_id'           => $type === 'rating' ? $scale->id : null,
                    'order_number'       => $order + 1,
                ]);
            }
        }
    }
}
