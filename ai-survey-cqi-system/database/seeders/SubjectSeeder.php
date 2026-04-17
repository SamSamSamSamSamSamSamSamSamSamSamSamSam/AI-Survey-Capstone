<?php

// database/seeders/SubjectSeeder.php

use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $files = [
            database_path('data/formatted_BSIT2018-4years_csv.csv'),
            database_path('data/formatted_BSIT2023-3years_csv.csv'),
        ];

        foreach ($files as $file) {
            $rows = array_map('str_getcsv', file($file));
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = array_combine($header, $row);

                Subject::firstOrCreate(
                    ['course_code' => $data['course_code']],
                    [
                        'name' => $data['name'],
                        'description' => $data['description'] ?? null,
                        'units' => $data['units'],
                    ]
                );
            }
        }
    }
}