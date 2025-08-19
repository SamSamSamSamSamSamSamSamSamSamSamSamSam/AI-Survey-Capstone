<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FacultyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // sample faculty / teachers
        User::create([
            'name' => 'Dr. Alice',
            'email' => 'alice@survey.com',
            'password' => Hash::make('teacher123'),
            'role' => 'teacher',
        ]);

        User::create([
            'name' => 'Prof. Bob',
            'email' => 'bob@survey.com',
            'password' => Hash::make('teacher123'),
            'role' => 'teacher',
        ]);
    }
}
