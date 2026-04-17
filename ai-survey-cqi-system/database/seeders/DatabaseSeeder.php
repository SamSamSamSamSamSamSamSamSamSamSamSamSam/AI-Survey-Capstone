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
            SemesterSeeder::class,
            CurriculumSeeder::class,
            // ProspectusSeeder::class,
            // SubjectSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            ProgramSeeder::class,
            OfferingTypeSeeder::class,
            EnrollmentTypeSeeder::class,
            CourseOfferingSeeder::class,
            EnrollmentSeeder::class,
            SurveyReferenceSeeder::class, // Seeder for University's official survey templates and categories
            OfficialQuestionnaireSeeder::class, 
            SentimentTypeSeeder::class, // Must be seeded before any sentiment-related data
            SettingsSeeder::class
        ]);
    }
}