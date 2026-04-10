<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudentStatus;

class StudentStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Regular', 'description' => 'Regular standing, able to enroll block section.'],
            ['name' => 'Irregular', 'description' => 'Irregular standing, unable to enroll block section.'],
        ];

        foreach ($statuses as $status) {
            StudentStatus::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}