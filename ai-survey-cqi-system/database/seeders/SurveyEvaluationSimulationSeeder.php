<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Survey;
use App\Models\Enrollment;
use App\Models\SurveyAttempt;
use App\Models\SurveyTemplate;
use App\Models\CourseOffering;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SurveyEvaluationSimulationSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Ensure the Official Questionnaire Template exists first
        $this->call(OfficialQuestionnaireSeeder::class);
        $template = SurveyTemplate::where('is_official', true)->first();
        
        if (!$template) {
            $this->command->error('Official Survey Template not found!');
            return;
        }

        // ── 1. Apply Question Weights ────────────────────────────────────────
        // Based on the 'add_category_weight_to_questions' migration schema
        DB::table('survey_template_questions')
            ->where('survey_template_id', $template->id)
            ->where('question_type', 'rating')
            ->update(['category_weight' => 20.00]); // Balanced weights distributed per question item

        // Fetch template questions
        $questions = DB::table('survey_template_questions')->where('survey_template_id', $template->id)->get();

        // Target role, subject, and semester dependencies
        $teacherRoleId = DB::table('roles')->where('name', 'teacher')->value('id') ?? 2; 
        $studentRoleId = DB::table('roles')->where('name', 'student')->value('id') ?? 3;

        $subjectId  = DB::table('subjects')->value('id') ?? DB::table('subjects')->insertGetId(['name' => 'Sample Subject', 'code' => 'SUBJ101']);
        $semesterId = DB::table('semesters')->value('id') ?? DB::table('semesters')->insertGetId(['name' => '1st Semester 2026', 'is_active' => true]);

        // ── 2. Create Users ───────────────────────────────────────────────────
        $facultyUsers = [];
        $studentUsers = [];

        for ($i = 1; $i <= 5; $i++) {
            $facultyName = fake()->name();
            $facultyEmail = strtolower(str_replace(' ', '', $facultyName)) . '@example.com';
            $faculty = User::create([
                'user_id_number' => fake()->numerify('########'),
                'name' => $facultyName,
                'email' => $facultyEmail,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]);
            DB::table('role_user')->insert(['user_id' => $faculty->id, 'role_id' => $teacherRoleId]);
            $facultyUsers[] = $faculty;
        }

        for ($i = 1; $i <= 15; $i++) {
            $studentName = fake()->name();
            $studentEmail = strtolower(str_replace(' ', '', $studentName)) . '@example.com';
            $student = User::create([
                'user_id_number' => fake()->numerify('########'),
                'name' => $studentName,
                'email' => $studentEmail,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]);
            DB::table('role_user')->insert(['user_id' => $student->id, 'role_id' => $studentRoleId]);
            $studentUsers[] = $student;
        }

        // ── 3. Survey Sentiment Config Profile ────────────────────────────────
        $surveyProfiles = [
            0 => ['sentiment' => 'positive', 'static_text' => false], // Survey 1: Unique
            1 => ['sentiment' => 'negative', 'static_text' => false], // Survey 2: Unique
            2 => ['sentiment' => 'neutral',  'static_text' => false], // Survey 3: Unique
            3 => ['sentiment' => 'mixed',    'static_text' => false], // Survey 4: Unique
            4 => ['sentiment' => 'mixed',    'static_text' => true],  // Survey 5: Repeated Cloned values
        ];

        $textBanks = [
            'positive' => [
                0 => 'The interactive structure immensely kept me focused and motivated throughout.',
                1 => 'Using real-time code examples and active peer learning sessions helped clear up difficult concepts.',
                2 => 'Yes, every assessment aligned perfectly with what we discussed in lectures.',
                3 => 'The instructor\'s approachable demeanor and clear passion for teaching.',
                4 => 'Maybe provide more supplementary reading materials, but overall it is already great.',
                5 => 'Incredibly fulfilling. I feel fully competent in the topics covered.',
                6 => 'Absolutely! I highly recommend taking any class under this professor.'
            ],
            'negative' => [
                0 => 'The chaotic environment and rigid structure made it difficult to follow along.',
                1 => 'The presentation slides were overcrowded and didn\'t address our actual project problems.',
                2 => 'No, the assignments felt disjointed from the lecture material and rubric grading was arbitrary.',
                3 => 'The syllabus breakdown was fine, but that was about it.',
                4 => 'Communication needs massive overhaul; emails went unanswered for weeks.',
                5 => 'Frustrating and chaotic. I had to self-study almost everything.',
                6 => 'No, I would definitely choose an alternative instructor if available.'
            ],
            'neutral' => [
                0 => 'It was standard. Practices were regular, nothing extraordinary or particularly flawed.',
                1 => 'Basic traditional lectures worked fine, though some segments felt repetitive.',
                2 => 'They were fair and covered basic requirements, though grading took a while.',
                3 => 'The set lecture hours were strictly followed without unnecessary extensions.',
                4 => 'Pacing could be slightly adjusted to allow more buffer time before midterms.',
                5 => 'An average experience. It met the minimum curriculum requirements.',
                6 => 'Yes, they get the job done efficiently.'
            ],
            'mixed' => [
                0 => 'Lectures were solid, but the asynchronous structure was slightly neglected.',
                1 => 'Practical assignments were great, but theoretical parts were rushed.',
                2 => 'The quizzes were accurate to the lectures, but the final exam was unexpectedly difficult.',
                3 => 'The project-based learning segments were highly interactive.',
                4 => 'Returning feedback on assignments in a timelier manner would optimize growth.',
                5 => 'It had its high points and challenging bottlenecks, a decent class overall.',
                6 => 'Yes, if you are willing to put in significant independent work.'
            ]
        ];

        // ── 4. Generate Offerings, Attempts, and Dynamic Responses ───────────
        foreach ($facultyUsers as $index => $faculty) {
            $profile = $surveyProfiles[$index];
            $sentiment = $profile['sentiment'];

            $offering = CourseOffering::create([
                'subject_id'   => $subjectId,
                'semester_id'  => $semesterId,
                'teacher_id'   => $faculty->id,
                'group_number' => $index + 1,
            ]);

            $survey = Survey::create([
                'offering_id'    => $offering->id,
                'created_by'     => $faculty->id,
                'template_id'    => $template->id,
                'target_role_id' => $studentRoleId,
                'title'          => "Evaluation for {$faculty->name} (" . ucfirst($sentiment) . ")",
                'description'    => "Profile feedback delivery run",
                'is_active'      => true,
                'start_date'     => now()->subDays(2),
                'end_date'       => now()->addDays(7),
            ]);

            // Define fixed responses array for Survey 5 to repeat perfectly across all 15 students
            $clonedAnswers = [
                0 => 'The environment structure influenced my workflow exceptionally.',
                1 => 'Hands-on labs helped clear up early bottlenecks.',
                2 => 'Assessments aligned exactly with active coursework assignments.',
                3 => 'The structured documentation and flexible office hours.',
                4 => 'More code challenges added to the weekly repository.',
                5 => 'Thoroughly engaging and standard setting course structure.',
                6 => 'Definitely recommended without reservations.'
            ];

            foreach ($studentUsers as $student) {
                Enrollment::create([
                    'offering_id'        => $offering->id,
                    'student_id'         => $student->id,
                    'enrollment_type_id' => 1,
                ]);

                $attempt = SurveyAttempt::create([
                    'survey_id'        => $survey->id,
                    'student_id'       => $student->id,
                    'submitted_at'     => now(),
                    'notify_email'     => true,
                    'notify_dashboard' => true,
                ]);

                $textQuestionCounter = 0;

                foreach ($questions as $question) {
                    $scaleValue = null;
                    $textResponse = null;

                    if ($question->question_type === 'rating') {
                        switch ($sentiment) {
                            case 'positive':
                                $scaleValue = fake()->numberBetween(4, 5);
                                break;
                            case 'negative':
                                $scaleValue = fake()->numberBetween(1, 2);
                                break;
                            case 'neutral':
                                $scaleValue = 3;
                                break;
                            case 'mixed':
                            default:
                                $scaleValue = fake()->numberBetween(1, 5);
                                break;
                        }
                    } else {
                        // Text Question Assignment
                        if ($profile['static_text']) {
                            // Survey 5: Cloned Answers repeated for all students
                            $textResponse = $clonedAnswers[$textQuestionCounter];
                        } else {
                            // Surveys 1-4: Unique answers combining specific indexes and sentences 
                            // to guarantee unique strings across all 60 independent student submissions.
                            $basePhrase = $textBanks[$sentiment][$textQuestionCounter] ?? 'Valid course insight.';
                            $textResponse = "Response [S{$index}-Q{$textQuestionCounter}-ID{$student->user_id_number}]: " . $basePhrase . " " . fake()->sentence(4);
                        }
                        $textQuestionCounter++;
                    }

                    DB::table('responses')->insert([
                        'id'                 => Str::ulid(),
                        'attempt_id'         => $attempt->id,
                        'survey_question_id' => $question->id,
                        'scale_value'        => $scaleValue,
                        'text_response'      => $textResponse,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                }
            }
        }

        $this->command->info('✓ Seeder executed safely: 66 Unique text options deployed for surveys 1-4, cloned responses repeated for survey 5.');
    }
}