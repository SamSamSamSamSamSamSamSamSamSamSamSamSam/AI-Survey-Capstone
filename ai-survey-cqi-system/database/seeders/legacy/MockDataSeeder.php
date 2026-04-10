<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Response;
use App\Models\CQIReport;
use App\Models\Setting;

class MockDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Settings ────────────────────────────────────────────────────────
        // Matches: settings(key unique, value text nullable)
        $settings = [
            'institution_name'        => 'DCISM',
            'department_name'         => 'Department of Computer and Information Sciences and Mathematics',
            'target_rating'           => '4.0',
            'cqi_priority_high'       => '1.80',
            'cqi_priority_medium'     => '1.60',
            'min_responses_threshold' => '3',
            'ai_provider'             => 'gemini',
            'ai_api_key'              => '',
            'report_title_prefix'     => 'CQI Summary Report',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // ── 2. Roles ───────────────────────────────────────────────────────────
        // Matches: roles(name unique, description nullable)
        $adminRole   = Role::create(['name' => 'admin',   'description' => 'System administrator with full access']);
        $teacherRole = Role::create(['name' => 'teacher', 'description' => 'Faculty member who teaches subjects']);
        $studentRole = Role::create(['name' => 'student', 'description' => 'Student who evaluates teachers']);

        // ── 3. Users ───────────────────────────────────────────────────────────
        // Matches: users(name, email unique, password, remember_token, email_verified_at)
        // role_user pivot: user_id, role_id

        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@dcism.edu',
            'password' => Hash::make('password'),
        ]);
        $admin->roles()->attach($adminRole->id);

        $teacherData = [
            ['name' => 'Dr. Maria Santos',  'email' => 'maria.santos@dcism.edu'],
            ['name' => 'Prof. Jose Reyes',  'email' => 'jose.reyes@dcism.edu'],
            ['name' => 'Dr. Ana Flores',    'email' => 'ana.flores@dcism.edu'],
            ['name' => 'Prof. Carlos Diaz', 'email' => 'carlos.diaz@dcism.edu'],
            ['name' => 'Dr. Elena Cruz',    'email' => 'elena.cruz@dcism.edu'],
        ];

        $teachers = [];
        foreach ($teacherData as $data) {
            $teacher = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make('password'),
            ]);
            $teacher->roles()->attach($teacherRole->id);
            $teachers[] = $teacher;
        }

        $studentData = [
            ['name' => 'Juan dela Cruz',    'email' => 'juan.delacruz@student.dcism.edu'],
            ['name' => 'Maria Gonzales',    'email' => 'maria.gonzales@student.dcism.edu'],
            ['name' => 'Pedro Aquino',      'email' => 'pedro.aquino@student.dcism.edu'],
            ['name' => 'Rosa Mendoza',      'email' => 'rosa.mendoza@student.dcism.edu'],
            ['name' => 'Miguel Villanueva', 'email' => 'miguel.villanueva@student.dcism.edu'],
            ['name' => 'Liza Ramos',        'email' => 'liza.ramos@student.dcism.edu'],
            ['name' => 'Carlo Bautista',    'email' => 'carlo.bautista@student.dcism.edu'],
            ['name' => 'Anna Castillo',     'email' => 'anna.castillo@student.dcism.edu'],
            ['name' => 'Ryan Dela Torre',   'email' => 'ryan.delatorre@student.dcism.edu'],
            ['name' => 'Sofia Padilla',     'email' => 'sofia.padilla@student.dcism.edu'],
        ];

        $students = [];
        foreach ($studentData as $data) {
            $student = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make('password'),
            ]);
            $student->roles()->attach($studentRole->id);
            $students[] = $student;
        }

        // ── 4. Semesters ──────────────────────────────────────────────────────
        // Matches: semesters(name, academic_year, semester_number tinyInt, is_active bool)
        $sem1 = Semester::create([
            'name'            => '1st Semester 2024-2025',
            'academic_year'   => '2024-2025',
            'semester_number' => 1,
            'is_active'       => false,
        ]);

        $sem2 = Semester::create([
            'name'            => '2nd Semester 2024-2025',
            'academic_year'   => '2024-2025',
            'semester_number' => 2,
            'is_active'       => true,
        ]);

        // ── 5. Subjects ────────────────────────────────────────────────────────
        // Matches: subjects(course_code unique, name nullable, description nullable)
        $subjectData = [
            ['course_code' => 'CS101', 'name' => 'Introduction to Computing',      'description' => 'Fundamentals of computing and information systems'],
            ['course_code' => 'CS201', 'name' => 'Data Structures and Algorithms', 'description' => 'Core data structures and algorithm analysis'],
            ['course_code' => 'CS301', 'name' => 'Object-Oriented Programming',    'description' => 'OOP principles using Java and design patterns'],
            ['course_code' => 'CS401', 'name' => 'Database Management Systems',    'description' => 'Relational databases, SQL, and normalization'],
            ['course_code' => 'CS501', 'name' => 'Software Engineering',           'description' => 'SDLC, agile methodologies, and software design'],
        ];

        $subjects = [];
        foreach ($subjectData as $data) {
            $subjects[] = Subject::create($data);
        }

        // Assign teachers → subject_teacher(subject_id, teacher_id, group, semester_id)
        // semester_id column added by 2026_03_20_021151_add_semester_totable
        foreach ($subjects as $i => $subject) {
            $subject->teachers()->attach($teachers[$i]->id, [
                'group'       => 'A',
                'semester_id' => $sem2->id,
            ]);
        }

        // Enroll all students → subject_student(subject_id, student_id, group, semester_id)
        // semester_id column added by 2026_03_20_021151_add_semester_totable
        foreach ($subjects as $subject) {
            foreach ($students as $student) {
                $subject->students()->attach($student->id, [
                    'group'       => 'A',
                    'semester_id' => $sem2->id,
                ]);
            }
        }

        // ── 6. Surveys (5 surveys, one per subject/teacher) ────────────────────
        // Matches: surveys(title, description, created_by, evaluatee_id, subject_id,
        //          group, target_role, is_active)
        // semester_id column added by 2026_03_20_021151_add_semester_totable
        $surveyDefs = [
            ['title' => 'Faculty Evaluation – Introduction to Computing',      'subject' => $subjects[0], 'teacher' => $teachers[0]],
            ['title' => 'Faculty Evaluation – Data Structures and Algorithms', 'subject' => $subjects[1], 'teacher' => $teachers[1]],
            ['title' => 'Faculty Evaluation – Object-Oriented Programming',    'subject' => $subjects[2], 'teacher' => $teachers[2]],
            ['title' => 'Faculty Evaluation – Database Management Systems',    'subject' => $subjects[3], 'teacher' => $teachers[3]],
            ['title' => 'Faculty Evaluation – Software Engineering',           'subject' => $subjects[4], 'teacher' => $teachers[4]],
        ];

        $surveys = [];
        foreach ($surveyDefs as $def) {
            $surveys[] = Survey::create([
                'title'        => $def['title'],
                'description'  => "Student evaluation of teaching effectiveness for {$def['subject']->course_code}, 2nd Semester 2024-2025.",
                'created_by'   => $admin->id,
                'evaluatee_id' => $def['teacher']->id,
                'subject_id'   => $def['subject']->id,
                'semester_id'  => $sem2->id,
                'target_role'  => 'student',
                'is_active'    => true,
                'group'        => 'A',
            ]);
        }

        // ── 7. Questions (8 per survey, 40 total) ─────────────────────────────
        // Matches: questions(survey_id, question_text, category, type enum[rating|text],
        //          options json nullable, order int)
        // IMPORTANT: Only 'rating' and 'text' are valid enum values per the migration.
        $questionTemplates = [
            ['question_text' => 'The instructor explains concepts clearly and understandably.',          'type' => 'rating', 'options' => null, 'order' => 1, 'category' => 'Teaching Effectiveness'],
            ['question_text' => 'The instructor is well-prepared and organized for each class.',         'type' => 'rating', 'options' => null, 'order' => 2, 'category' => 'Teaching Effectiveness'],
            ['question_text' => 'The instructor encourages student participation and questions.',         'type' => 'rating', 'options' => null, 'order' => 3, 'category' => 'Student Engagement'],
            ['question_text' => 'The instructor provides helpful and timely feedback on my work.',       'type' => 'rating', 'options' => null, 'order' => 4, 'category' => 'Feedback & Assessment'],
            ['question_text' => 'The instructor demonstrates mastery of the subject matter.',            'type' => 'rating', 'options' => null, 'order' => 5, 'category' => 'Subject Mastery'],
            ['question_text' => 'The instructor uses real-world examples to support the lessons.',       'type' => 'rating', 'options' => null, 'order' => 6, 'category' => 'Teaching Effectiveness'],
            ['question_text' => 'What do you appreciate most about this instructor\'s teaching style?', 'type' => 'text',   'options' => null, 'order' => 7, 'category' => 'Open Feedback'],
            ['question_text' => 'What areas do you suggest the instructor can improve on?',              'type' => 'text',   'options' => null, 'order' => 8, 'category' => 'Open Feedback'],
        ];

        $allQuestions = [];
        foreach ($surveys as $survey) {
            $surveyQuestions = [];
            foreach ($questionTemplates as $qt) {
                $surveyQuestions[] = Question::create([
                    'survey_id'     => $survey->id,
                    'question_text' => $qt['question_text'],
                    'type'          => $qt['type'],
                    'options'       => $qt['options'],
                    'order'         => $qt['order'],
                    'category'      => $qt['category'],
                ]);
            }
            $allQuestions[$survey->id] = $surveyQuestions;
        }

        // ── 8. Responses (10 students × 8 questions × 5 surveys = 400 total) ──
        // Matches: responses(survey_id, question_id, evaluator_id, evaluatee_id,
        //          subject_id nullable, response text, sentiment_label varchar(32),
        //          sentiment_score double(8,4))
        // semester_id column added by 2026_03_20_021151_add_semester_totable
        //
        // Unique constraint on [survey_id, evaluator_id, question_id, evaluatee_id, subject_id]
        // is naturally satisfied: each student answers each question exactly once per survey.

        $positiveComments = [
            'The instructor explains things very clearly with great real-world examples.',
            'Very approachable and always willing to help students outside of class.',
            'Lessons are well-structured and easy to follow from start to finish.',
            'Makes complex topics feel simple and keeps the class very engaging.',
            'Knowledgeable and clearly passionate about teaching the subject.',
            'Great use of visuals and live demonstrations that aid understanding.',
            'Always comes to class fully prepared and well-organized.',
            'Encourages critical thinking and healthy discussion among students.',
            'Feedback on assignments is always detailed, specific, and constructive.',
            'Connects lesson content to practical industry scenarios very effectively.',
        ];

        $improvementComments = [
            'Could slow down a bit when covering particularly difficult topics.',
            'More hands-on practice exercises and drills would be very helpful.',
            'Returned feedback on assignments sometimes comes a little late.',
            'Would appreciate more student interaction and Q&A during lectures.',
            'Some slides are too text-heavy and can be hard to absorb quickly.',
            'Additional worked examples for abstract concepts would really help.',
            'Extending office hours would allow more students to get support.',
            'Incorporating more group activities and collaborative tasks would help.',
            'Quiz content could align more closely with what was covered in lectures.',
            'More frequent comprehension check-ins throughout the class would be great.',
        ];

        $sentimentMap = [
            '1' => ['label' => 'negative', 'score' => 0.1200],
            '2' => ['label' => 'negative', 'score' => 0.3100],
            '3' => ['label' => 'neutral',  'score' => 0.5500],
            '4' => ['label' => 'positive', 'score' => 0.7800],
            '5' => ['label' => 'positive', 'score' => 0.9400],
        ];

        foreach ($surveys as $surveyIndex => $survey) {
            $def      = $surveyDefs[$surveyIndex];
            $teacher  = $def['teacher'];
            $subject  = $def['subject'];
            $questions = $allQuestions[$survey->id];

            foreach ($students as $studentIndex => $student) {
                foreach ($questions as $question) {
                    $responseText   = null;
                    $sentimentLabel = null;
                    $sentimentScore = null;

                    if ($question->type === 'rating') {
                        // 70% of students rate high (4–5); 30% rate mixed (2–4)
                        $pool         = $studentIndex < 7
                            ? ['4', '4', '5', '5', '5']
                            : ['2', '3', '3', '4', '4'];
                        $responseText   = $pool[array_rand($pool)];
                        $sentimentLabel = $sentimentMap[$responseText]['label'];
                        $sentimentScore = $sentimentMap[$responseText]['score'];

                    } elseif ($question->type === 'text') {
                        if ($question->order === 7) {
                            $responseText   = $positiveComments[$studentIndex % count($positiveComments)];
                            $sentimentLabel = 'positive';
                            $sentimentScore = round(0.75 + ($studentIndex % 4) * 0.04, 4);
                        } else {
                            $responseText   = $improvementComments[$studentIndex % count($improvementComments)];
                            $sentimentLabel = 'neutral';
                            $sentimentScore = round(0.45 + ($studentIndex % 4) * 0.03, 4);
                        }
                    }

                    Response::create([
                        'survey_id'       => $survey->id,
                        'question_id'     => $question->id,
                        'evaluator_id'    => $student->id,
                        'evaluatee_id'    => $teacher->id,
                        'subject_id'      => $subject->id,
                        'semester_id'     => $sem2->id,
                        'response'        => $responseText,
                        'sentiment_label' => $sentimentLabel,
                        'sentiment_score' => $sentimentScore,
                    ]);
                }
            }
        }

        // ── 9. CQI Reports (1 per survey, 5 total) ────────────────────────────
        // Matches: cqi_reports(title, description, survey_id, generated_by,
        //          data json, file_path nullable)
        // semester_id column added by 2026_03_20_021151_add_semester_totable
        foreach ($surveys as $surveyIndex => $survey) {
            $def     = $surveyDefs[$surveyIndex];
            $teacher = $def['teacher'];
            $subject = $def['subject'];

            CQIReport::create([
                'title'        => "CQI Summary Report – {$subject->course_code}",
                'description'  => "Continuous Quality Improvement report for {$subject->name} taught by {$teacher->name}, 2nd Semester 2024-2025.",
                'survey_id'    => $survey->id,
                'semester_id'  => $sem2->id,
                'generated_by' => $admin->id,
                'file_path'    => "reports/cqi_{$subject->course_code}_sem2_2024_2025.pdf",
                'data'         => [
                    'summary'            => "Overall positive ratings received for {$subject->name}.",
                    'average_rating'     => 4.2,
                    'total_responses'    => 10,
                    'sentiment_positive' => 70,
                    'sentiment_neutral'  => 20,
                    'sentiment_negative' => 10,
                    'categories'         => [
                        'Teaching Effectiveness' => 4.3,
                        'Student Engagement'     => 4.1,
                        'Feedback & Assessment'  => 4.0,
                        'Subject Mastery'        => 4.4,
                    ],
                    'generated_at' => now()->toDateTimeString(),
                ],
            ]);
        }
    }
}