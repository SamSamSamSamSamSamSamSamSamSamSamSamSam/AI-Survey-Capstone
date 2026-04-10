<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'program_code' => 'BSIT',
                'name' => 'Bachelor of Science in Information Technology',
            ],
            [
                'program_code' => 'BSCS',
                'name' => 'Bachelor of Science in Computer Science',
            ],
            [
                'program_code' => 'BSIS',
                'name' => 'Bachelor of Science in Information Systems',
            ],
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['program_code' => $program['program_code']],
                $program
            );
        }
    }
}