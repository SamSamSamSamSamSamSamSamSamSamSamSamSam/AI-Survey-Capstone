<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@school.edu',
            'password' => Hash::make('password'),
        ]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        // Create teacher who is also admin
        $teacherAdmin = User::create([
            'name' => 'John Doe',
            'email' => 'johndoe@school.edu',
            'password' => Hash::make('password'),
        ]);
        $teacherAdmin->roles()->attach([
            Role::where('name', 'admin')->first()->id,
            Role::where('name', 'teacher')->first()->id
        ]);

        // Create regular teacher
        $teacher = User::create([
            'name' => 'Jane Smith',
            'email' => 'janesmith@school.edu',
            'password' => Hash::make('password'),
        ]);
        $teacher->roles()->attach(Role::where('name', 'teacher')->first());

        $teacher = User::create([
            'name' => 'Teacher Faculty',
            'email' => 'teacher@school.edu',
            'password' => Hash::make('password'),
        ]);
        $teacher->roles()->attach(Role::where('name', 'teacher')->first());

        // Create student who is also teacher
        $studentTeacher = User::create([
            'name' => 'Alex Johnson',
            'email' => 'alexj@school.edu',
            'password' => Hash::make('password'),
        ]);
        $studentTeacher->roles()->attach([
            Role::where('name', 'student')->first()->id,
            Role::where('name', 'teacher')->first()->id
        ]);

        // Create regular students
        for ($i = 1; $i <= 10; $i++) {
            $student = User::create([
                'name' => 'Student ' . $i,
                'email' => 'student' . $i . '@school.edu',
                'password' => Hash::make('password'),
            ]);
            $student->roles()->attach(Role::where('name', 'student')->first());
        }
    }
}