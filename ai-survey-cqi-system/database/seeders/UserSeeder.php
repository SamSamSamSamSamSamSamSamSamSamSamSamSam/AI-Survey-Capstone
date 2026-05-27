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
        // 1. Fetch Roles
        $adminRole = Role::where('name', 'admin')->first();
        $teacherRole = Role::where('name', 'faculty')->first();
        $studentRole = Role::where('name', 'student')->first();

        /*
        |--------------------------------------------------------------------------
        | Create Default Admins (5)
        |--------------------------------------------------------------------------
        */

        $admins = [
            ['id' => 'ADMIN001', 'email' => 'admin1@example.com', 'env' => 'ADMIN_PASSWORD1', 'name'=> 'System Administrator A'],
            ['id' => 'ADMIN002', 'email' => 'admin2@example.com', 'env' => 'ADMIN_PASSWORD2', 'name'=> 'System Administrator B'],
            ['id' => 'ADMIN003', 'email' => 'admin3@example.com', 'env' => 'ADMIN_PASSWORD3', 'name'=> 'System Administrator C'],
            ['id' => 'ADMIN004', 'email' => 'admin4@example.com', 'env' => 'ADMIN_PASSWORD4', 'name'=> 'System Administrator D'],
            ['id' => 'ADMIN005', 'email' => 'admin5@example.com', 'env' => 'ADMIN_PASSWORD5', 'name'=> 'System Administrator Z'],
        ];

        foreach ($admins as $adminData) {
            $admin = User::create([
                'user_id_number' => $adminData['id'],
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'password' => Hash::make(env($adminData['env'], 'password')),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]);
            
            // This ensures EVERY admin in the loop gets the role attached
            $admin->roles()->attach($adminRole);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Faculty Users (5)
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i <= 5; $i++) {
            // Generates a random 8-digit number formatted like 20101786
            $facultyId = fake()->numerify('########'); 
            $facultyName = fake()->name();
            // Generates a clean, lowercase email based on the name
            $facultyEmail = strtolower(str_replace(' ', '', $facultyName)) . '@example.com';

            $faculty = User::create([
                'user_id_number'       => $facultyId,
                'name'                 => $facultyName,
                'email'                => $facultyEmail,
                'password'             => Hash::make('password'),
                'email_verified_at'    => now(),
                'must_change_password' => false,
            ]);
            $faculty->roles()->attach($teacherRole);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Student Users (15)
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i <= 15; $i++) {
            // Generates a random 8-digit number formatted like 20101786
            $studentId = fake()->numerify('########');
            $studentName = fake()->name();
            // Generates a clean, lowercase email based on the name
            $studentEmail = strtolower(str_replace(' ', '', $studentName)) . '@example.com';

            $student = User::create([
                'user_id_number'       => $studentId,
                'name'                 => $studentName,
                'email'                => $studentEmail,
                'password'             => Hash::make('password'),
                'email_verified_at'    => now(),
                'must_change_password' => false,
            ]);
            $student->roles()->attach($studentRole);
        }

        

        
    }
}