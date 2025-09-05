<?php

namespace Database\Seeders;

// database/seeders/DatabaseSeeder.php

use Database\Seeders\RolesAndSubjectsSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SubjectSeeder::class,
            // Add other seeders here
        ]);
    }
}
