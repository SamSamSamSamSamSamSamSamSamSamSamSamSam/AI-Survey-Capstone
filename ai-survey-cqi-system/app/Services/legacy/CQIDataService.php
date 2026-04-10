<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\Response;
use App\Models\Question;

class CQIDataService
{
    /**
     * Build the full analytics payload for a given survey.
     *
     * Returns an array with:
     *  - meta         : teacher, subject, semester, group info
     *  - categories   : per-category scoring tables (counts, WM, totals)
     *  - satisfaction : the 1-10 satisfaction question result
     *  - open_text    : keyed open-ended feedback responses
     *  - summary      : per-category mean scores + overall mean
     */
    public function build(Survey $survey): array
    {
        $survey->load(['evaluatee', 'subject', 'semester', 'questions.responses']);

        // ── Meta ─────────────────────────────────────────────────────────────
        $meta = [
            'teacher_name'   => $survey->evaluatee?->name ?? 'N/A',
            'program'        => 'Bachelor of Science in Information Technology',
            'academic_term'  => $survey->semester?->getLabelAttribute() ?? 'N/A',
            'academic_year'  => $survey->semester?->academic_year ?? 'N/A',
            'course_handled' => $survey->subject?->course_code ?? 'N/A',
            'group_number'   => $survey->group ?? 'N/A',
        ];

        // ── Separate questions by type ────────────────────────────────────────
        // Rated questions  : type = 'rating' (1-4 scale), grouped by category
        // Satisfaction     : type = 'rating' with category = 'satisfaction' (1-10)
        // Open-ended       : type = 'text'

        $ratedCategories  = [];
        $satisfactionData = null;
        $openTextData     = [];

        // Scale labels used in the evaluation form
        $scaleCategories = [
            'course_syllabus'            => 'Course Syllabus',
            'teaching_quality'           => 'Teaching Quality',
            'student_outcomes'           => 'Student Outcomes',
            'student_learning_assessment'=> 'Student Learning Assessment',
            'teacher_learner_support'    => 'Teacher-Learner Support and Engagement',
        ];

        foreach ($survey->questions as $question) {
            $category = $question->category ?? 'uncategorized';
            $type     = $question->type;

            // ── 1–10 satisfaction question ────────────────────────────────
            if ($type === 'rating' && $category === 'satisfaction') {
                $responses = $question->responses
                    ->where('survey_id', $survey->id)
                    ->pluck('response')
                    ->filter()
                    ->map(fn($v) => (int) $v);

                $total = $responses->count();
                $counts = array_fill(1, 10, 0);
                foreach ($responses as $val) {
                    if ($val >= 1 && $val <= 10) {
                        $counts[$val]++;
                    }
                }
                $weightedSum = collect($counts)->map(fn($c, $k) => $c * $k)->sum();
                $wm = $total > 0 ? round($weightedSum / $total, 1) : 0;

                $satisfactionData = [
                    'question' => $question->question_text,
                    'counts'   => $counts,
                    'total'    => $total,
                    'wm'       => $wm,
                    'label'    => $this->satisfactionLabel($wm),
                ];
                continue;
            }

            // ── 1–4 scale rated questions ─────────────────────────────────
            if ($type === 'rating') {
                $responses = $question->responses
                    ->where('survey_id', $survey->id)
                    ->pluck('response')
                    ->filter()
                    ->map(fn($v) => (int) $v);

                $total  = $responses->count();
                $counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                foreach ($responses as $val) {
                    if (isset($counts[$val])) {
                        $counts[$val]++;
                    }
                }
                $weightedSum = collect($counts)->map(fn($c, $k) => $c * $k)->sum();
                $wm = $total > 0 ? round($weightedSum / $total, 2) : 0;

                if (!isset($ratedCategories[$category])) {
                    $ratedCategories[$category] = [
                        'label'     => $scaleCategories[$category] ?? ucwords(str_replace('_', ' ', $category)),
                        'questions' => [],
                        'total_wm'  => 0,
                    ];
                }

                $ratedCategories[$category]['questions'][] = [
                    'text'   => $question->question_text,
                    'counts' => $counts,
                    'total'  => $total,
                    'wm'     => $wm,
                ];

                $ratedCategories[$category]['total_wm'] += $wm;
                continue;
            }

            // ── Open-ended / text questions ───────────────────────────────
            if ($type === 'text') {
                $responses = $question->responses
                    ->where('survey_id', $survey->id)
                    ->pluck('response')
                    ->filter()
                    ->values()
                    ->toArray();

                $openTextData[] = [
                    'question'  => $question->question_text,
                    'responses' => $responses,
                ];
            }
        }

        // ── Summary of Findings ───────────────────────────────────────────────
        $summary     = [];
        $overallSum  = 0;
        $overallCount= 0;

        foreach ($ratedCategories as $key => $cat) {
            $questionCount = count($cat['questions']);
            $meanScore     = $questionCount > 0 ? round($cat['total_wm'] / $questionCount, 2) : 0;

            $summary[$key] = [
                'label'          => $cat['label'],
                'mean_score'     => $meanScore,
                'interpretation' => $this->interpretScore($meanScore),
            ];

            $overallSum  += $meanScore;
            $overallCount++;
        }

        $overallMean = $overallCount > 0 ? round($overallSum / $overallCount, 2) : 0;

        return [
            'meta'         => $meta,
            'categories'   => $ratedCategories,
            'satisfaction' => $satisfactionData,
            'open_text'    => $openTextData,
            'summary'      => $summary,
            'overall_mean' => $overallMean,
            'overall_interpretation' => $this->interpretScore($overallMean),
        ];
    }

    // ── Score interpretation helpers ─────────────────────────────────────────

    private function interpretScore(float $score): string
    {
        // if ($score >= 3.50) return 'Excellent';
        // if ($score >= 2.50) return 'Very Good';
        // if ($score >= 1.50) return 'Good';
        // return 'Needs Improvement';
        if ($score >= 4.21) return 'Excellent';
        if ($score >= 3.41) return 'Very Good';
        if ($score >= 2.61) return 'Good';
        if ($score >= 1.81) return 'Fair';
        return 'Needs Improvement';
    }

    private function satisfactionLabel(float $score): string
    {
        if ($score >= 9.0)  return 'Very Satisfied';
        if ($score >= 7.0)  return 'Satisfied';
        if ($score >= 5.0)  return 'Moderately Satisfied';
        if ($score >= 3.0)  return 'Dissatisfied';
        return 'Very Dissatisfied';
    }
}