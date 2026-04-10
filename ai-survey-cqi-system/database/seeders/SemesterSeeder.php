<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Semester;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $semesters = [
            [
                'name' => '1st Semester',
                'academic_start_year' => 2024,
                'semester_number' => 1,
                'is_active' => true, // current semester
            ],
            [
                'name' => '2nd Semester',
                'academic_start_year' => 2024,
                'semester_number' => 2,
                'is_active' => false,
            ],
            [
                'name' => 'Summer',
                'academic_start_year' => 2024,
                'semester_number' => 3,
                'is_active' => false,
            ],
            [
                'name' => '1st Semester',
                'academic_start_year' => 2025,
                'semester_number' => 1,
                'is_active' => false,
            ],
        ];

        foreach ($semesters as $sem) {
            Semester::updateOrCreate(
                [
                    'name' => $sem['name'],
                    'academic_start_year' => $sem['academic_start_year'],
                ],
                $sem
            );
        }
    }
}