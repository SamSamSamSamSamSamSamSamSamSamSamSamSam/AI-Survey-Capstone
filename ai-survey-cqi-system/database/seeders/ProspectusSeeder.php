<?php
// database/seeders/ProspectusSeeder.php

use App\Models\Curriculum;
use App\Models\Subject;
use App\Models\Prospectus;

class ProspectusSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed('BSIT-2018', 'formatted_BSIT2018-4years_csv.csv');
        $this->seed('BSIT-2023', 'formatted_BSIT2023-3years_csv.csv');
    }

    private function seed($curriculumCode, $fileName)
    {
        $curriculum = Curriculum::where('curriculum_code', $curriculumCode)->first();

        $rows = array_map('str_getcsv', file(database_path("data/$fileName")));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            $data = array_combine($header, $row);

            // -------------------------------
            // 1. Create / Get Subject
            // -------------------------------
            $subject = Subject::firstOrCreate(
                ['course_code' => $data['Course Code']],
                [
                    'name' => $data['Title'],
                    'units' => $data['Acad Units'],
                    'description' => 'Lec: '.$data['Lec Units'].' Lab: '.$data['Lab Units'],
                ]
            );

            // -------------------------------
            // 2. Create Prospectus Entry
            // -------------------------------
            Prospectus::create([
                'curriculum_id'    => $curriculum->id,
                'subject_id'       => $subject->id,
                'year_level'       => $this->mapYear($data['Year']),
                'semester_number'  => $this->mapSemester($data['Semester']),
            ]);
        }
    }

    private function mapYear($year)
    {
        return match (trim($year)) {
            'First Year' => 1,
            'Second Year' => 2,
            'Third Year' => 3,
            'Fourth Year' => 4,
            default => null,
        };
    }

    private function mapSemester($semester)
    {
        return match (trim($semester)) {
            'First Semester' => 1,
            'Second Semester' => 2,
            'Summer' => 3,
            default => null,
        };
    }
}