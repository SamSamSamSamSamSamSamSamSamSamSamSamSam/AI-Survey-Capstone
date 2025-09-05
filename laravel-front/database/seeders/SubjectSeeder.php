<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\User;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        $subjects = [
            ['course_code' => 'MATH101', 'name' => 'Calculus I'],
            ['course_code' => 'PHYS101', 'name' => 'Physics I'],
            ['course_code' => 'CHEM101', 'name' => 'Chemistry I'],
            ['course_code' => 'BIO101', 'name' => 'Biology I'],
            ['course_code' => 'CS101', 'name' => 'Introduction to Programming'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }

        // Assign teachers to subjects
        $teachers = User::whereHas('roles', function ($query) {
            $query->where('name', 'teacher');
        })->get();

        $subjects = Subject::all();

        foreach ($subjects as $index => $subject) {
            $teacher = $teachers[$index % count($teachers)];
            $subject->teachers()->attach($teacher->id);
        }
    }
}