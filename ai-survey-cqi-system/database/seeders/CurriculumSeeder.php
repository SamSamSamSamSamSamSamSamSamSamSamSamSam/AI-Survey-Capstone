<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curriculum;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $curricula = [
            [
                'program_id' => 1, // BSIT
                'curriculum_code' => 'BSIT-2018',
                'description' => 'Curriculum for BSIT effective 2018',
                'effective_year' => 2018,
                'is_active' => true,
            ],
            [
                'program_id' => 1, // BSIT
                'curriculum_code' => 'BSIT-2024',
                'description' => 'Curriculum for BSIT effective 2024',
                'effective_year' => 2024,
                'is_active' => true,
            ],
            [
                'program_id' => 2, // BSCS
                'curriculum_code' => 'BSCS-2024',
                'description' => 'Curriculum for BSCS effective 2024',
                'effective_year' => 2024,
                'is_active' => true,
            ],
            [
                'program_id' => 3, // BSIS
                'curriculum_code' => 'BSIS-2024',
                'description' => 'Curriculum for BSIS effective 2024',
                'effective_year' => 2024,
                'is_active' => true,
            ],
        ];

        foreach ($curricula as $curr) {
            Curriculum::updateOrCreate(
                [
                    'program_id' => $curr['program_id'],
                    'curriculum_code' => $curr['curriculum_code']
                ],
                $curr
            );
        }
    }
}