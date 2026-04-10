<?php

namespace Database\Seeders;

use App\Models\SentimentType;
use Illuminate\Database\Seeder;

class SentimentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['positive', 'neutral', 'negative'];

        foreach ($types as $label) {
            SentimentType::firstOrCreate(['label' => $label]);
        }
    }
}
