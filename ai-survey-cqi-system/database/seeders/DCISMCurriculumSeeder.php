<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        // Programs Offered - Allready in ProgramSeeder
        // DB::table('programs')->insert([
        //     ['program_code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
        //     ['program_code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science'],
        //     ['program_code' => 'BSIS', 'name' => 'Bachelor of Science in Information Systems'],
        // ]);
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

        foreach ($CIS_courses as $subject) {
            Subject::updateOrCreate(
                ['course_code' => $subject['course_code']],
                $subject
            );
        }
        
        DB::table('subjects')->insert([
            ['course_code' => 'IT101', 'name' => 'Introduction to Computing', 'units' => 3],
            ['course_code' => 'CS101', 'name' => 'Programming 1', 'units' => 3],
        ]);

        DB::table('semesters')->insert([
            ['name' => '1st Semester', 'academic_start_year' => 2025, 'semester_number' => 1, 'is_active' => true],
        ]);

        // 2. Dependent Tables
        $programId = DB::table('programs')->where('program_code', 'BSIT')->value('id');
        $semesterId = DB::table('semesters')->where('semester_number', 1)->value('id');

        DB::table('curricula')->insert([
            'program_id' => $programId,
            'curriculum_code' => '2025-2029',
            'effective_year' => 2025,
        ]);

        DB::table('blocks')->insert([
            'program_id' => $programId,
            'semester_id' => $semesterId,
            'name' => 'BSIT-1A',
            'year_level' => 1,
        ]);

        // 3. Junction/Pivot Tables
        $curriculumId = DB::table('curricula')->where('curriculum_code', '2025-2029')->value('id');
        $subjectId = DB::table('subjects')->where('course_code', 'IT101')->value('id');

        DB::table('prospectuses')->insert([
            'curriculum_id' => $curriculumId,
            'subject_id' => $subjectId,
            'year_level' => 1,
            'semester_number' => 1,
        ]);
    }
}