<?php

namespace App\Http\Controllers\Admin;

use App\Models\{Survey, Response, CQIReport};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function generateCQIReport(Request $request)
    {
        // DATE RANGE PROCESSING -----------------------------------------------
        if ($request->input('range_type') === 'custom') {
            if ($request->filled(['start_date', 'end_date'])) {
                $startDateInput = Carbon::parse($request->start_date);
                $endDateInput = Carbon::parse($request->end_date);

                if ($startDateInput->greaterThan($endDateInput)) {
                    throw ValidationException::withMessages([
                        'end_date' => ['The end date must be after or the same as the start date.'],
                    ]);
                }

                $startDate = $startDateInput->startOfMonth();
                $endDate = $endDateInput->endOfMonth();
            }
        } else {
            $endDate = now();
            $startDate = now()->subMonths(5)->startOfMonth();
        }

        // FETCH SURVEYS -------------------------------------------------------
        $surveys = Survey::whereBetween('created_at', [$startDate, $endDate])
            ->with('questions')
            ->get();

        if ($surveys->isEmpty()) {
            return back()->with('error', 'No surveys found for the selected period.');
        }

        $allResponses = Response::whereIn('survey_id', $surveys->pluck('id'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('question')
            ->get();

        if ($allResponses->isEmpty()) {
            return back()->with('error', 'No responses found for the selected period.');
        }

        // RAW MATRIX (RESPONDENT DATA) ---------------------------------------
        // For reproduction of the DOCX table (Respondents × Items)
        $matrix = [];
        $questionsOrdered = [];

        // Collect all unique question codes (cm1…tla1…a1…)
        foreach ($surveys as $survey) {
            foreach ($survey->questions as $q) {
                $questionsOrdered[$q->id] = strtolower($q->category) . $q->order;
            }
        }

        // Sort items CM1–CM10 → TLA1–TLA10 → A1–A7
        asort($questionsOrdered);

        // Build rows per evaluator
        $groupedByEvaluator = $allResponses->groupBy('evaluator_id');
        foreach ($groupedByEvaluator as $evaluatorId => $responsesGroup) {
            $row = [];
            foreach ($questionsOrdered as $qid => $code) {
                $resp = $responsesGroup->firstWhere('question_id', $qid);
                $row[$code] = $resp ? intval($resp->response) : null;
            }
            $matrix[] = $row;
        }

        // DESCRIPTIVE STATS PER QUESTION -------------------------------------
        $questionStats = [];

        foreach ($questionsOrdered as $questionId => $code) {
            $responses = $allResponses->where('question_id', $questionId)->pluck('response')->map(fn($v) => intval($v));

            if ($responses->isEmpty()) continue;

            $mean = round($responses->avg(), 2);
            $median = $responses->median();
            $modeVal = $this->computeMode($responses->toArray());
            $stdDev = round($this->computeStdDev($responses->toArray()), 2);

            $questionStats[$code] = [
                'mean' => $mean,
                'median' => $median,
                'mode' => $modeVal,
                'std_dev' => $stdDev,
                'category' => strtoupper(preg_replace('/\d+/', '', $code)),
            ];
        }

        // CATEGORY SUMMARY ----------------------------------------------------
        $categories = $allResponses->groupBy(fn($r) => strtoupper($r->question->category));
        $summaryData = [];
        $target = 4.5;

        foreach ($categories as $category => $catResponses) {
            $questions = $catResponses->groupBy('question_id');
            $means = $medians = $modes = $stdDevs = [];

            foreach ($questions as $qResponses) {
                $vals = $qResponses->pluck('response')->map(fn($v) => intval($v));
                $means[] = $vals->avg();
                $medians[] = $vals->median();
                $modes[] = $this->computeMode($vals->toArray());
                $stdDevs[] = $this->computeStdDev($vals->toArray());
            }

            $avgMean = round(collect($means)->avg(), 2);
            $avgMedian = round(collect($medians)->avg(), 2);
            $mostCommonMode = collect($modes)->countBy()->sortDesc()->keys()->first();
            $avgStdDev = round(collect($stdDevs)->avg(), 2);
            $gap = round($target - $avgMean, 2);
            $priority = $this->computePriority($gap);

            $summaryData[$category] = [
                'avg_mean' => $avgMean,
                'avg_median' => $avgMedian,
                'most_common_mode' => $mostCommonMode,
                'avg_std_dev' => $avgStdDev,
                'gap' => $gap,
                'priority' => $priority,
                'interpretation' => $this->interpretCategory($avgMean, $avgStdDev),
            ];
        }

        // SAVE THE REPORT -----------------------------------------------------
        $report = CQIReport::create([
            'title' => 'CQI Summary Report',
            'description' => 'Automatically generated CQI summary analysis.',
            'survey_id' => $surveys->first()->id,
            'generated_by' => Auth::id(),
            'data' => [
                'matrix' => $matrix,
                'question_stats' => $questionStats,
                'category_summary' => $summaryData,
            ]
        ]);

        // EXPORT PDF ----------------------------------------------------------
        $pdf = Pdf::loadView('admin.reports.cqi', [
            'matrix' => $matrix,
            'questionsOrdered' => $questionsOrdered,
            'questionStats' => $questionStats,
            'summaryData' => $summaryData,
            'startDate' => $startDate->format('F Y'),
            'endDate' => $endDate->format('F Y'),
        ])->setPaper('A4', 'portrait');

        $fileName = 'CQI_Summary_Report_' .
            $startDate->format('M_Y') . '_to_' . $endDate->format('M_Y') . '.pdf';

        return $pdf->download($fileName);
    }

    // COMPUTE MODE -----------------------------------------------------------
    private function computeMode(array $values)
    {
        $counts = array_count_values($values);
        arsort($counts);
        return array_key_first($counts);
    }

    // COMPUTE STANDARD DEVIATION --------------------------------------------
    private function computeStdDev(array $values)
    {
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $values)) / count($values);
        return sqrt($variance);
    }

    // PRIORITY LEVEL (Based on DOCX logic) ----------------------------------
    private function computePriority($gap)
    {
        if ($gap >= 1.80) return 3;  // Highest
        if ($gap >= 1.60) return 2;  // Medium
        return 1;                    // Low
    }

    // CATEGORY INTERPRETATION ----------------------------------------------
    private function interpretCategory($mean, $stdDev)
    {
        if ($mean < 3) return "Below average perception; improvement needed.";
        if ($mean >= 3 && $mean < 4) return "Neutral to slightly positive feedback.";
        return "Positive perception, close to desired standard.";
    }
}
