<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.gemini.api_key', '');
        $this->model   = config('services.gemini.model', 'gemini-2.5-flash');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
    }

    /**
     * Generate CQI report content from structured analytics data.
     *
     * Returns structured JSON with keys:
     *   analysis, identified_gaps, strengths, areas_for_improvement,
     *   root_cause_analysis, action_plan, monitoring, conclusion
     */
    public function generateCqiReport(array $analyticsData): array
    {
        $prompt = $this->buildCqiPrompt($analyticsData);

        try {
            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.4,
                        'maxOutputTokens' => 4096,
                        'responseMimeType'=> 'application/json',
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \RuntimeException('Gemini API request failed: ' . $response->status());
            }

            $raw  = $response->json('candidates.0.content.parts.0.text', '{}');
            $data = json_decode($raw, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
                throw new \RuntimeException('Gemini returned invalid JSON response.');
            }

            return $data;

        } catch (\Throwable $e) {
            Log::error('GeminiService::generateCqiReport failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildCqiPrompt(array $d): string
    {
        $categoryScores = '';
        foreach ($d['category_scores'] ?? [] as $cat => $score) {
            if (is_array($score)) {
                continue;
            }

            $interp = $this->interpretScore((float)$score, $d['scale_max'] ?? 5);
            $categoryScores .= "   - {$cat}: {$score}/{$d['scale_max']} ({$interp})\n";
        }

        $openEndedSamples = '';
        foreach ($d['open_ended_samples'] ?? [] as $question => $responses) {
            $openEndedSamples .= "  Question: {$question}\n";
            foreach (array_slice($responses, 0, 8) as $r) {
                $openEndedSamples .= "    * {$r}\n";
            }
        }

        return <<<PROMPT
You are an academic quality assurance expert specializing in Outcomes-Based Education (OBE) and Continuous Quality Improvement (CQI). 
Analyze the following faculty evaluation data and generate a comprehensive CQI report.

## CONTEXT
- Institution: {$d['institution']}
- Faculty: {$d['faculty_name']}
- Course: {$d['course_code']} — {$d['course_name']}
- Program: {$d['program_name']}
- Semester: {$d['semester']}
- Academic Year: {$d['academic_year']}
- Group: {$d['group_number']}
- Total Respondents: {$d['response_count']}

## QUANTITATIVE RESULTS
- Overall Average Rating: {$d['avg_rating']}/{$d['scale_max']}
- Sentiment Distribution: {$d['positive_pct']}% positive, {$d['neutral_pct']}% neutral, {$d['negative_pct']}% negative

## CATEGORY SCORES
{$categoryScores}

## STUDENT OPEN-ENDED RESPONSES
{$openEndedSamples}

## INSTRUCTIONS
Based on the above data, generate a structured CQI report in **valid JSON only** with these exact keys:

{
  "overall_interpretation": "2-3 sentence overall performance summary",
  "analysis": {
    "summary": "OBE-perspective analysis paragraph",
    "highlights": ["key highlight 1", "key highlight 2", ...]
  },
  "identified_gaps": [
    {"area": "gap area", "gap": "description", "impact": "impact on learning"}
  ],
  "strengths": ["strength 1", "strength 2", ...],
  "areas_for_improvement": ["area 1", "area 2", ...],
  "root_cause_analysis": [
    {"issue": "issue name", "possible_cause": "root cause explanation"}
  ],
  "action_plan": [
    {
      "area": "area for improvement",
      "action": "specific action",
      "responsible_person": "who is responsible",
      "timeline": "e.g. Next Semester",
      "expected_outcome": "measurable expected outcome"
    }
  ],
  "monitoring": ["monitoring activity 1", "monitoring activity 2", ...],
  "conclusion": "2-3 sentence conclusion paragraph"
}

Return ONLY the JSON object. No markdown, no explanation, no preamble.
PROMPT;
    }

    private function interpretScore(mixed $score, int $max): string
    {
        if (is_array($score)) {
            return 'N/A'; // Or handle as needed
        }
        $pct = $score / $max;
        return match(true) {
            $pct >= 0.90 => 'Excellent',
            $pct >= 0.80 => 'Very Good',
            $pct >= 0.70 => 'Good',
            $pct >= 0.60 => 'Fair',
            default      => 'Needs Improvement',
        };
    }
}
