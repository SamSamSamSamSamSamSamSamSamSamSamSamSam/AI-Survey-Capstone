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
        | Create Default Admins (Reduced to 5)
        |--------------------------------------------------------------------------
        */

        $admins = [
            ['id' => 'ADMIN001', 'email' => 'admin1@example.com', 'env' => 'ADMIN_PASSWORD1'],
            ['id' => 'ADMIN002', 'email' => 'admin2@example.com', 'env' => 'ADMIN_PASSWORD2'],
            ['id' => 'ADMIN003', 'email' => 'admin3@example.com', 'env' => 'ADMIN_PASSWORD3'],
            ['id' => 'ADMIN004', 'email' => 'admin4@example.com', 'env' => 'ADMIN_PASSWORD4'],
            ['id' => 'ADMIN005', 'email' => 'admin5@example.com', 'env' => 'ADMIN_PASSWORD5'],
        ];

        foreach ($admins as $adminData) {
            $admin = User::create([
                'user_id_number' => $adminData['id'],
                'name' => 'System Administrator',
                'email' => $adminData['email'],
                'password' => Hash::make(env($adminData['env'], 'default_password')),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]);
            
            // This ensures EVERY admin in the loop gets the role attached
            $admin->roles()->attach($adminRole);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Sample Student & Teacher
        |--------------------------------------------------------------------------
        */

        $studentsample = User::create([
            'user_id_number' => '20230001',
            'name' => 'Student Sample',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        $studentsample->roles()->attach($studentRole);
        
        $teachersample = User::create([
            'user_id_number' => 'TEACHER001',
            'name' => 'Teacher Sample',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        $teachersample->roles()->attach($teacherRole);


    }
}