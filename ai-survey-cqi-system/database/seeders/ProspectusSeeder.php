<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prospectus;
use App\Models\Curriculum;
use App\Models\Subject;

class ProspectusSeeder extends Seeder
{
    public function run(): void
    {
        $curriculum = Curriculum::where('curriculum_code', 'BSIT-2024')->first();

        // $regular = OfferingType::where('name', 'Regular')->first();
        // $offsem  = OfferingType::where('name', 'Offsemester')->first();
        // $summer  = OfferingType::where('name', 'Summer')->first();

        $entries = [

            // Year 1 - Semester 1
            [
                'subject_code' => 'IT101',
                'year_level' => 1,
                'semester_number' => 1,
            ],

            [
                'subject_code' => 'IT102',
                'year_level' => 1,
                'semester_number' => 1,
            ],

            // Year 1 - Semester 2
            [
                'subject_code' => 'IT103',
                'year_level' => 1,
                'semester_number' => 2,
            ],

            // Year 2 - Semester 1
            [
                'subject_code' => 'IT201',
                'year_level' => 2,
                'semester_number' => 1,
            ],

            [
                'subject_code' => 'IT202',
                'year_level' => 2,
                'semester_number' => 1,
            ],

            // Year 2 - Semester 2
            [
                'subject_code' => 'IT203',
                'year_level' => 2,
                'semester_number' => 2,
            ],

            // Year 4
            [
                'subject_code' => 'IT401',
                'year_level' => 4,
                'semester_number' => 1,
            ],

            [
                'subject_code' => 'IT402',
                'year_level' => 4,
                'semester_number' => 2,
            ],

        ];

        foreach ($entries as $entry) {

            $subject = Subject::where('course_code', $entry['subject_code'])->first();

            Prospectus::updateOrCreate(
                [
                    'curriculum_id' => $curriculum->id,
                    'subject_id' => $subject->id,
                    'year_level' => $entry['year_level'],
                    'semester_number' => $entry['semester_number'],
                ],
            );
        }
    }
}