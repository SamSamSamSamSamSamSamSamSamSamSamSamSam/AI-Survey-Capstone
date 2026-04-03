<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Administrator with full system access'],
            ['name' => 'teacher', 'description' => 'Teacher who can be evaluated and evaluate admins'],
            ['name' => 'student', 'description' => 'Student who can evaluate teachers'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}