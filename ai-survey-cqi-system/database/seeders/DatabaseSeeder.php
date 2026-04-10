<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // The order here is CRITICAL because of Foreign Key constraints
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ProgramSeeder::class,
            CurriculumSeeder::class,
            SubjectSeeder::class,
            OfferingTypeSeeder::class,
            StudentStatusSeeder::class, // Moved up to satisfy Enrollment dependencies
            SemesterSeeder::class,
            ProspectusSeeder::class,
            CourseOfferingSeeder::class,
            EnrollmentSeeder::class,
            SurveyReferenceSeeder::class,
        ]);
    }
}