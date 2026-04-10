<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OfferingType;

class OfferingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Regular'],
            ['name' => 'Offsemester'],
            ['name' => 'Summer'],
        ];

        foreach ($types as $type) {
            OfferingType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}