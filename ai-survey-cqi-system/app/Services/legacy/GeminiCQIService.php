<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiCQIService
{
    private string $apiKey;
    private string $endpoint;
    private string $model = 'gemini-2.5-flash';

    public function __construct()
    {
        $this->apiKey   = config('services.gemini.key');
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
    }

    /**
     * Send the analytics payload to Gemini and receive a structured CQI narrative.
     *
     * @param  array $analyticsPayload  The output of CQIDataService::build()
     * @return array                    Structured CQI narrative sections
     */
    public function generate(array $analyticsPayload): array
    {
        $prompt = $this->buildPrompt($analyticsPayload);

        try {
            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->endpoint}?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.4,
                        'topK'            => 40,
                        'topP'            => 0.95,
                        'maxOutputTokens' => 4096, // increase to avoid truncation
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->fallbackResponse();
            }

            $rawText = data_get($response->json(), 'candidates.0.content.parts.0.text', '');
            Log::info('Gemini raw text', ['text' => $rawText]);

            $parsed = $this->parseResponse($rawText);
            Log::info('Parsed Gemini narrative', $parsed);

            return $parsed;

        } catch (\Exception $e) {
            Log::error('Gemini CQI Service exception', ['message' => $e->getMessage()]);
            return $this->fallbackResponse();
        }
    }

    // ── Prompt Engineering ────────────────────────────────────────────────────

    private function buildPrompt(array $data): string
    {
        $meta         = $data['meta'];
        $summary      = $data['summary'];
        $openText     = $data['open_text'] ?? [];
        $overallMean  = $data['overall_mean'];
        $overallInterp= $data['overall_interpretation'];

        // Build summary table text
        $summaryLines = '';
        foreach ($summary as $item) {
            $summaryLines .= "- {$item['label']}: Mean Score = {$item['mean_score']} ({$item['interpretation']})\n";
        }

        // Build open text feedback summary
        $feedbackText = '';
        foreach ($openText as $item) {
            $feedbackText .= "\nQuestion: {$item['question']}\n";
            foreach ($item['responses'] as $r) {
                $feedbackText .= "  * {$r}\n";
            }
        }

        return <<<PROMPT
            You are an educational quality assurance specialist writing a formal Continuous Quality Improvement (CQI) report for a university.

            You will analyze teacher evaluation data and produce a structured CQI narrative. Your tone must be professional, formal, and evidence-based. Use the evaluation scores and student feedback to support every claim.

            == TEACHER EVALUATION DATA ==

            Teacher: {$meta['teacher_name']}
            Course: {$meta['course_handled']}
            Academic Term: {$meta['academic_term']} | Academic Year: {$meta['academic_year']}
            Group: {$meta['group_number']}

            == EVALUATION SUMMARY ==
            {$summaryLines}
            Overall Mean Score: {$overallMean} ({$overallInterp})

            == STUDENT OPEN-ENDED FEEDBACK ==
            {$feedbackText}

            == INSTRUCTIONS ==

            Produce a JSON object with EXACTLY these keys. Do not include any text outside the JSON. Do not wrap in markdown code blocks.

            {
            "analysis": "A 3-4 sentence paragraph analyzing performance from an Outcome-Based Education (OBE) perspective. Reference specific scores.",

            "identified_gaps": [
                {
                "area": "Area name",
                "gap": "Specific gap description",
                "impact": "Impact on learning outcomes"
                }
            ],

            "strengths": [
                "Strength statement 1 referencing specific scores or feedback",
                "Strength statement 2",
                "Strength statement 3"
            ],

            "areas_for_improvement": [
                "Improvement area 1 with specific recommendation",
                "Improvement area 2",
                "Improvement area 3"
            ],

            "root_cause_analysis": [
                {
                "issue": "Issue name",
                "possible_cause": "Root cause explanation"
                }
            ],

            "action_plan": [
                {
                "area": "Area for Improvement",
                "action": "Specific action to take",
                "responsible": "Responsible person/unit",
                "timeline": "Timeline",
                "expected_outcome": "Expected result"
                }
            ],

            "monitoring": [
                "Monitoring activity 1",
                "Monitoring activity 2",
                "Monitoring activity 3"
            ],

            "conclusion": "A 3-4 sentence concluding paragraph summarizing overall performance, key improvements needed, and the outlook for the next evaluation cycle."
            }

            Ensure identified_gaps has 3-4 items, strengths has 3-5 items, areas_for_improvement has 3-5 items, root_cause_analysis has 3-4 items, action_plan has 4-5 items, and monitoring has 3-4 items. Base all content strictly on the provided data.
        PROMPT;
    }

    // ── Response Parsing ────────────────────────────────────────────────

    private function parseResponse(string $text): array
    {
        // Strip markdown fences
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/\s*```$/m', '', $text);
        $text = trim($text);

        // Replace newlines inside strings to avoid json_decode errors
        $text = preg_replace("/\\r\\n|\\r|\\n/", " ", $text);

        $parsed = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed)) {
            Log::warning('Gemini response could not be parsed as JSON', ['raw' => $text]);
            return $this->fallbackResponse();
        }

        // Deep merge parsed response with fallback to ensure all keys exist
        return $this->mergeWithFallback($parsed);
    }

    private function mergeWithFallback(array $parsed): array
    {
        $fallback = $this->fallbackResponse();

        foreach ($fallback as $key => $value) {
            // Replace only if parsed key exists and is non-empty
            if (isset($parsed[$key]) && !empty($parsed[$key])) {
                $fallback[$key] = $parsed[$key];
            }
        }

        return $fallback;
    }

    // ── Fallback ─────────────────────────────────────────────────────────

    private function fallbackResponse(): array
    {
        return [
            'analysis'              => 'Analysis could not be generated at this time. Please review the evaluation scores and retry.',
            'identified_gaps'       => [],
            'strengths'             => [],
            'areas_for_improvement' => [],
            'root_cause_analysis'   => [],
            'action_plan'           => [],
            'monitoring'            => [],
            'conclusion'            => 'Conclusion could not be generated. Please retry the report generation.',
        ];
    }
}