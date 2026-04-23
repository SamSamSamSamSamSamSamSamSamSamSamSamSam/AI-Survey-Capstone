<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseOffering;
use App\Models\Prospectus;

class CourseOfferingSeeder extends Seeder
{
    public function run(): void
    {
        $prospectuses = Prospectus::where('semester_id', 1)->get()->random(5);
        
        // Define the IDs of the faculty you want to exclude
        $excludedFacultyIds = [2, 10, 15]; 

        // Get all faculty members while excluding specific IDs
        $facultyMembers = \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', 'faculty');
            })
            ->whereNotIn('id', $excludedFacultyIds)
            ->get();

        // Ensure we have faculty remaining to avoid errors
        if ($facultyMembers->isEmpty()) {
            $this->command->error('No faculty members found to assign.');
            return;
        }

        foreach ($prospectuses as $pros) {
            $numSections = rand(1, 3);

            for ($i = 1; $i <= $numSections; $i++) {
                $semesterId = \App\Models\Semester::where('academic_start_year', 2024)
                    ->where('semester_number', $pros->semester_number)
                    ->first()->id ?? 1;

                    CourseOffering::updateOrCreate(
                    [
                        'subject_id'   => $pros->subject_id,
                        'semester_id'  => $semesterId,
                        'group_number' => $i,
                    ],
                    [
                        'offering_type_id' => $pros->offered_type_id,
                        // Now random() will only pick from the filtered collection
                        'teacher_id'       => $facultyMembers->random()->id,
                    ]
                );
            }
        }
    }
}