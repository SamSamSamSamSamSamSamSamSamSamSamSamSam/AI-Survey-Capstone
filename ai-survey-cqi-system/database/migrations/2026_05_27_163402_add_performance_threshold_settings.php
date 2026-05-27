<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $thresholds = [
        'survey.excellent_threshold' => [
            'value'        => '90',
            'type'         => 'number',
            'label'        => 'Excellent Threshold (%)',
            'description'  => 'Minimum achievement % for "Excellent". Default: 90.',
            'group'        => 'survey',
            'is_sensitive' => false,
            'is_readonly'  => false,
        ],
        'survey.very_good_threshold' => [
            'value'        => '80',
            'type'         => 'number',
            'label'        => 'Very Good Threshold (%)',
            'description'  => 'Minimum achievement % for "Very Good". Default: 80.',
            'group'        => 'survey',
            'is_sensitive' => false,
            'is_readonly'  => false,
        ],
        'survey.good_threshold' => [
            'value'        => '70',
            'type'         => 'number',
            'label'        => 'Good Threshold (%)',
            'description'  => 'Minimum achievement % for "Good". Default: 70.',
            'group'        => 'survey',
            'is_sensitive' => false,
            'is_readonly'  => false,
        ],
        'survey.fair_threshold' => [
            'value'        => '60',
            'type'         => 'number',
            'label'        => 'Fair Threshold (%)',
            'description'  => 'Minimum achievement % for "Fair". Below this is "Needs Improvement". Default: 60.',
            'group'        => 'survey',
            'is_sensitive' => false,
            'is_readonly'  => false,
        ],
    ];

    public function up(): void
    {
        foreach ($this->thresholds as $key => $data) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                array_merge($data, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', array_keys($this->thresholds))->delete();
    }
};