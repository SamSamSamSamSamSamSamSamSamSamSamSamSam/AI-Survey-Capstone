<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $CIS_courses = [
            [
                'course_code' => 'IT 101',
                'name' => 'Introduction to Computing',
                'description' => 'Overview of computing concepts, hardware, software, and IT careers.',
                'units' => 3,
            ],
       ];

       $GE_courses = [
            [
                'course_code' => 'IT 101',
                'name' => 'Introduction to Computing',
                'description' => 'Overview of computing concepts, hardware, software, and IT careers.',
                'units' => 3,
            ],
       ];

       $NSTP_courses = [
            [
                'course_code' => 'IT 101',
                'name' => 'Introduction to Computing',
                'description' => 'Overview of computing concepts, hardware, software, and IT careers.',
                'units' => 3,
            ],
       ];

       $ELECTIVE_courses = [
            [
                'course_code' => 'IT 101',
                'name' => 'Introduction to Computing',
                'description' => 'Overview of computing concepts, hardware, software, and IT careers.',
                'units' => 3,
            ],
       ];

       $IT_courses = [
            [
                'course_code' => 'IT 101',
                'name' => 'Introduction to Computing',
                'description' => 'Overview of computing concepts, hardware, software, and IT careers.',
                'units' => 3,
            ],
       ];

       $CS_courses = [
            [
                'course_code' => 'IT 101',
                'name' => 'Introduction to Computing',
                'description' => 'Overview of computing concepts, hardware, software, and IT careers.',
                'units' => 3,
            ],
       ];

       $IS_courses = [
            [
                'course_code' => 'IT 101',
                'name' => 'Introduction to Computing',
                'description' => 'Overview of computing concepts, hardware, software, and IT careers.',
                'units' => 3,
            ],
       ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['course_code' => $subject['course_code']],
                $subject
            );
        }
    }
}