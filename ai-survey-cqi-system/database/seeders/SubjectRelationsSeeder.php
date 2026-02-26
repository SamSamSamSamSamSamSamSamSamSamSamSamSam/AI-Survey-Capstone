<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\User;

class SubjectRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['course_code' => 'IT3201', 'name' => 'Web Systems'],
            ['course_code' => 'CIS2204', 'name' => 'Data Structures'],
            ['course_code' => 'GE-ART', 'name' => 'Art Appreciation'],
        ];

        foreach ($subjects as $subjectData) {
            $subject = Subject::firstOrCreate(['course_code' => $subjectData['course_code']], $subjectData);

            // Random group numbers
            $groups = ['1', '2', '3'];

            foreach ($groups as $group) {
                // Assign teachers to group
                $teachers = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
                    ->inRandomOrder()->take(1)->pluck('id');

                foreach ($teachers as $teacherId) {
                    $subject->teachers()->attach($teacherId, ['group' => $group]);
                }

                // Assign students to group
                $students = User::whereHas('roles', fn($q) => $q->where('name', 'student'))
                    ->where('email', '!=', 'nosubject@example.com') 
                    ->take(10)
                    ->pluck('id');

                foreach ($students as $studentId) {
                    $subject->students()->attach($studentId, ['group' => $group]);
                }
            }
        }

        $this->command->info('✅ Subjects, teachers, and students with groups have been seeded successfully.');
    
    }
}
