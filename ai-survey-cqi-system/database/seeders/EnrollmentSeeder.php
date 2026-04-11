<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\CourseOffering;
use App\Models\User;
use App\Models\EnrollmentType;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'student');})->get();
        $enrollmentTypes = EnrollmentType::all();
        $offerings = CourseOffering::all();

        foreach ($students as $student) {

            // Each student enrolled in 3–5 random offerings
            $assignedOfferings = $offerings->random(rand(3, 5));

            foreach ($assignedOfferings as $offering) {

                Enrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'offering_id' => $offering->id,
                    ],
                    [
                        'enrollment_type_id' => $enrollmentTypes->random()->id,
                    ]
                );
            }
        }
    }
}