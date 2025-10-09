<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResponseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $responses = [];
        $usedKeys = [];

        // Example: 3 surveys, 8 evaluators, 5 evaluatees, 5 questions each
        for ($surveyId = 1; $surveyId <= 3; $surveyId++) {
            for ($evaluatorId = 1; $evaluatorId <= 8; $evaluatorId++) {
                for ($evaluateeId = 1; $evaluateeId <= 5; $evaluateeId++) {
                    for ($questionId = 1; $questionId <= 5; $questionId++) {
                        $subjectId = rand(1, 3);
                        $key = "{$surveyId}-{$evaluatorId}-{$questionId}-{$evaluateeId}-{$subjectId}";

                        // Skip duplicates completely
                        if (isset($usedKeys[$key])) {
                            continue;
                        }

                        $usedKeys[$key] = true;

                        $responses[] = [
                            'survey_id' => $surveyId,
                            'evaluator_id' => $evaluatorId,
                            'question_id' => $questionId,
                            'evaluatee_id' => $evaluateeId,
                            'subject_id' => $subjectId,
                            'response' => rand(1, 5), // 1 to 5 rating
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }
        }

        // Truncate before inserting (clean reset)
        DB::table('responses')->truncate();

        // Bulk insert safely
        DB::table('responses')->insert($responses);
    }
}
