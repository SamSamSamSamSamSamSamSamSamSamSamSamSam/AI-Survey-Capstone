<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Subject;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Response;
use App\Models\CQIReport;
use Illuminate\Support\Facades\Hash;

class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 🚫 Clear old data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Response::truncate();
        Question::truncate();
        Survey::truncate();
        Subject::truncate();
        User::truncate();
        Role::truncate();
        CQIReport::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 👤 Roles
        $adminRole = Role::create(['name' => 'admin']);
        $teacherRole = Role::create(['name' => 'teacher']);
        $studentRole = Role::create(['name' => 'student']);

        // 👩‍💼 Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->roles()->attach($adminRole);

        // 👨‍🏫 Teachers
        $teachers = collect([
            User::create(['name' => 'Mr. Smith', 'email' => 'smith@example.com', 'password' => Hash::make('password')]),
            User::create(['name' => 'Ms. Johnson', 'email' => 'johnson@example.com', 'password' => Hash::make('password')]),
            User::create(['name' => 'Prof. Brown', 'email' => 'brown@example.com', 'password' => Hash::make('password')]),
        ]);
        $teachers->each(fn($t) => $t->roles()->attach($teacherRole));

        // 👨‍🎓 Students
        $students = collect([
            User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'password' => Hash::make('password')]),
            User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => Hash::make('password')]),
            User::create(['name' => 'Charlie', 'email' => 'charlie@example.com', 'password' => Hash::make('password')]),
            User::create(['name' => 'Diana', 'email' => 'diana@example.com', 'password' => Hash::make('password')]),
            User::create(['name' => 'Eve', 'email' => 'eve@example.com', 'password' => Hash::make('password')]),
        ]);
        $students->each(fn($s) => $s->roles()->attach($studentRole));

        // 📘 Subject
        $subject = Subject::create([
            'course_code' => 'CS101',
            'name' => 'Introduction to Computer Science',
        ]);

        // 📄 Survey
        $survey = Survey::create([
            'title' => 'Demo Survey',
            'description' => 'A sample survey for dashboard metrics testing.',
            'created_by' => $admin->id,
            'evaluatee_id' => $teachers->first()->id,
            'target_role' => 'Student',
            'is_active' => true,
        ]);

        // ❓ Questions
        $q1 = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'The instructor communicates clearly.',
            'type' => 'rating',
        ]);
        $q2 = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'The instructor provides helpful feedback.',
            'type' => 'rating',
        ]);

        // 🗳️ Hardcoded Responses (unique per student and teacher)
        $responses = [
            // Teacher 1 - Mr. Smith
            ['evaluator' => 'Alice', 'evaluatee' => 'Mr. Smith', 'r1' => 5, 'r2' => 4],
            ['evaluator' => 'Bob', 'evaluatee' => 'Mr. Smith', 'r1' => 4, 'r2' => 4],
            ['evaluator' => 'Charlie', 'evaluatee' => 'Mr. Smith', 'r1' => 5, 'r2' => 5],

            // Teacher 2 - Ms. Johnson
            ['evaluator' => 'Diana', 'evaluatee' => 'Ms. Johnson', 'r1' => 3, 'r2' => 4],
            ['evaluator' => 'Eve', 'evaluatee' => 'Ms. Johnson', 'r1' => 4, 'r2' => 3],

            // Teacher 3 - Prof. Brown
            ['evaluator' => 'Alice', 'evaluatee' => 'Prof. Brown', 'r1' => 5, 'r2' => 5],
            ['evaluator' => 'Bob', 'evaluatee' => 'Prof. Brown', 'r1' => 4, 'r2' => 5],
            ['evaluator' => 'Charlie', 'evaluatee' => 'Prof. Brown', 'r1' => 4, 'r2' => 4],
        ];

        foreach ($responses as $r) {
            $student = $students->firstWhere('name', $r['evaluator']);
            $teacher = $teachers->firstWhere('name', $r['evaluatee']);

            Response::create([
                'survey_id' => $survey->id,
                'question_id' => $q1->id,
                'evaluator_id' => $student->id,
                'evaluatee_id' => $teacher->id,
                'subject_id' => $subject->id,
                'response' => $r['r1'],
            ]);

            Response::create([
                'survey_id' => $survey->id,
                'question_id' => $q2->id,
                'evaluator_id' => $student->id,
                'evaluatee_id' => $teacher->id,
                'subject_id' => $subject->id,
                'response' => $r['r2'],
            ]);
        }

        // 🧮 Compute CQI Report
        $averageRating = Response::avg('response');

        CQIReport::create([
            'title' => 'Demo CQI Report',
            'description' => 'Sample CQI Report generated from demo data.',
            'survey_id' => $survey->id,
            'generated_by' => $admin->id,
            'data' => [
                'average_rating' => $averageRating,
                'remarks' => 'Instructor performance is satisfactory based on survey results.',
            ],
        ]);

        $this->command->info('✅ Dashboard demo data seeded successfully (hardcoded responses)!');
    }
}
