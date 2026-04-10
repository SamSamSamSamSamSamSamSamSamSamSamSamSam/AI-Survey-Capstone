<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $teacherRole = Role::where('name', 'faculty')->first();
        $studentRole = Role::where('name', 'student')->first();

        /*
        |--------------------------------------------------------------------------
        | Create Default Admin
        |--------------------------------------------------------------------------
        */

        $admin = User::create([
            'user_id_number' => 'ADMIN001',
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $admin->roles()->attach($adminRole);

        $studentsample = User::create([
            'user_id_number' => '20230001',
            'name' => 'Student Sample',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        $teachersample = User::create([
            'user_id_number' => 'TEACHER001',
            'name' => 'Teacher Sample',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $teachersample->roles()->attach($teacherRole);
        $studentsample->roles()->attach($studentRole);


        /*
        |--------------------------------------------------------------------------
        | Create Teacher Users
        |--------------------------------------------------------------------------
        */

        $teacherUsers = User::factory()->count(5)->create();

        foreach ($teacherUsers as $teacher) {
            $teacher->roles()->attach($teacherRole);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Student Users
        |--------------------------------------------------------------------------
        */

        $studentUsers = User::factory()->count(30)->create();

        foreach ($studentUsers as $student) {
            $student->roles()->attach($studentRole);
        }
    }
}