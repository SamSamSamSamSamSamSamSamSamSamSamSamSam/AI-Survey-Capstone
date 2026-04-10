<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [

            [
                'course_code' => 'IT101',
                'name' => 'Introduction to Computing',
                'description' => 'Overview of computing concepts, hardware, software, and IT careers.',
                'units' => 3,
            ],

            [
                'course_code' => 'IT102',
                'name' => 'Computer Programming 1',
                'description' => 'Fundamentals of programming using a high-level language.',
                'units' => 3,
            ],

            [
                'course_code' => 'IT103',
                'name' => 'Discrete Mathematics',
                'description' => 'Mathematical foundations for computing including logic and sets.',
                'units' => 3,
            ],

            [
                'course_code' => 'IT201',
                'name' => 'Data Structures and Algorithms',
                'description' => 'Study of data structures and algorithm efficiency.',
                'units' => 3,
            ],

            [
                'course_code' => 'IT202',
                'name' => 'Database Management Systems',
                'description' => 'Design and implementation of relational databases.',
                'units' => 3,
            ],

            [
                'course_code' => 'IT203',
                'name' => 'Web Development',
                'description' => 'Front-end and back-end web development fundamentals.',
                'units' => 3,
            ],

            [
                'course_code' => 'IT301',
                'name' => 'Information Assurance and Security',
                'description' => 'Principles of information security and risk management.',
                'units' => 3,
            ],

            [
                'course_code' => 'IT302',
                'name' => 'Software Engineering',
                'description' => 'Software development lifecycle, methodologies, and project management.',
                'units' => 3,
            ],

            [
                'course_code' => 'IT401',
                'name' => 'Capstone Project 1',
                'description' => 'Planning and design phase of the capstone project.',
                'units' => 3,
            ],

            [
                'course_code' => 'IT402',
                'name' => 'Capstone Project 2',
                'description' => 'Implementation and defense of the capstone project.',
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