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
                'password' => Hash::make(env($adminData['env'], 'default_password')),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]);
            
            // This ensures EVERY admin in the loop gets the role attached
            $admin->roles()->attach($adminRole);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Sample Teacher (2)
        |--------------------------------------------------------------------------
        */
        
        $teachersample = User::create([
            'user_id_number' => 'TEACHER001',
            'name' => 'Teacher A',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        $teachersample->roles()->attach($teacherRole);

        $teachersample2 = User::create([
            'user_id_number' => 'TEACHER002',
            'name' => 'Teacher B',
            'email' => 'teacher2@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        $teachersample2->roles()->attach($teacherRole);


        /*
        |--------------------------------------------------------------------------
        | Create Sample Student (2)
        |--------------------------------------------------------------------------
        */
        $studentsample = User::create([
            'user_id_number' => '20230001',
            'name' => 'Student A',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        $studentsample->roles()->attach($studentRole);

        $studentsample2 = User::create([
            'user_id_number' => '20230002',
            'name' => 'Student B',
            'email' => 'studentb@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        $studentsample2->roles()->attach($studentRole);

        /*
        |--------------------------------------------------------------------------
        | Create Unverified User (2)
        |--------------------------------------------------------------------------
        */

        $unverified = User::create([
            'user_id_number' => '12345678',
            'name' => 'Unverified Teacher',
            'email' => 'unverified_student@example.com',
            'password' => Hash::make('password'),
            'must_change_password' => true,
        ]);
        $unverified->roles()->attach($teacherRole);

        $unverified1 = User::create([
            'user_id_number' => '12345679',
            'name' => 'Unverified Student',
            'email' => 'unverified_faculty@example.com',
            'password' => Hash::make('password'),
            'must_change_password' => true,
        ]);
        $unverified1->roles()->attach($studentRole);

        /*
        |--------------------------------------------------------------------------
        | Create Not Enrolled User (2)
        |--------------------------------------------------------------------------
        */
        $notenrolled = User::create([
            'user_id_number' => '24681012',
            'name' => 'Not_Assigned Faculty',
            'email' => 'notassigned_faculty@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        $notenrolled->roles()->attach($teacherRole);

        $notenrolled1 = User::create([
            'user_id_number' => '24681013',
            'name' => 'Not_Enrolled Student',
            'email' => 'notenrolled_student@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        $notenrolled1->roles()->attach($studentRole);
    }
}