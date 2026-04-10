<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseOffering;
use App\Models\Prospectus;

class CourseOfferingSeeder extends Seeder
{
    public function run(): void
    {
        // Loop over all prospectus entries to generate course offerings
        $prospectuses = Prospectus::all();

        foreach ($prospectuses as $pros) {

            // Generate 1–3 sections per prospectus entry
            $numSections = rand(1, 3);

            for ($i = 1; $i <= $numSections; $i++) {

                CourseOffering::factory()->create([
                    'subject_id' => $pros->subject_id,
                    'semester_id' => \App\Models\Semester::where('academic_start_year', 2024)
                        ->where('semester_number', $pros->semester_number)
                        ->first()->id ?? 1,
                    'offering_type_id' => $pros->offered_type_id,
                ]);
            }
        }
    }
}