<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // sample students
        User::create([
            'name' => 'Student One',
            'email' => 'student1@survey.com',
            'password' => Hash::make('student123'),
            'role' => 'student',
        ]);

        User::create([
            'name' => 'Student Two',
            'email' => 'student2@survey.com',
            'password' => Hash::make('student123'),
            'role' => 'student',
        ]);
    }
}
