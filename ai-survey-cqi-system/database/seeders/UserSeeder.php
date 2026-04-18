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
            'email' => 'admin1@example.com',
            'password' => Hash::make(env('ADMIN_PASSWORD1', 'default_password')),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);

                $admin = User::create([
            'user_id_number' => 'ADMIN002',
            'name' => 'System Administrator',
            'email' => 'admin2@example.com',
            'password' => Hash::make(env('ADMIN_PASSWORD2', 'default_password')),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);

                $admin = User::create([
            'user_id_number' => 'ADMIN003',
            'name' => 'System Administrator',
            'email' => 'admin3@example.com',
            'password' => Hash::make(env('ADMIN_PASSWORD3', 'default_password')),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);

                $admin = User::create([
            'user_id_number' => 'ADMIN004',
            'name' => 'System Administrator',
            'email' => 'admin4@example.com',
            'password' => Hash::make(env('ADMIN_PASSWORD4', 'default_password')),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);

                $admin = User::create([
            'user_id_number' => 'ADMIN005',
            'name' => 'System Administrator',
            'email' => 'admin5@example.com',
            'password' => Hash::make(env('ADMIN_PASSWORD5', 'default_password')),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);

                $admin = User::create([
            'user_id_number' => 'ADMIN006',
            'name' => 'System Administrator',
            'email' => 'admin6@example.com',
            'password' => Hash::make(env('ADMIN_PASSWORD6', 'default_password')),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);

        

        $admin->roles()->attach($adminRole);

        $studentsample = User::create([
            'user_id_number' => '20230001',
            'name' => 'Student Sample',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        
        $teachersample = User::create([
            'user_id_number' => 'TEACHER001',
            'name' => 'Teacher Sample',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'must_change_password' => false,
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