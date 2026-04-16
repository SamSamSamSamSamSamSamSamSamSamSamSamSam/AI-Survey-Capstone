<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseOffering;
use App\Models\Prospectus;

class CourseOfferingSeeder extends Seeder
{
    public function run(): void
    {
        $prospectuses = Prospectus::all();
        
        // Get all faculty members once to avoid repeated queries
        $facultyMembers = \App\Models\User::whereHas('roles', function($query) {
            $query->where('name', 'faculty');
        })->get();

        foreach ($prospectuses as $pros) {
            $numSections = rand(1, 3);

            for ($i = 1; $i <= $numSections; $i++) {
                $semesterId = \App\Models\Semester::where('academic_start_year', 2024)
                    ->where('semester_number', $pros->semester_number)
                    ->first()->id ?? 1;

                \App\Models\CourseOffering::updateOrCreate(
                    [
                        'subject_id'   => $pros->subject_id,
                        'semester_id'  => $semesterId,
                        'group_number' => $i,
                    ],
                    [
                        'offering_type_id' => $pros->offered_type_id,
                        // Pull a random faculty ID just like your factory does
                        'teacher_id'       => $facultyMembers->random()->id,
                    ]
                );
            }
        }
    }
}