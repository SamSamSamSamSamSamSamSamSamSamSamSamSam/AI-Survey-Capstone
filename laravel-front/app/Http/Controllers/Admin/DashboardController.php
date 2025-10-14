<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $surveyId = $request->query('survey_id');

        // Cache results for short time to avoid heavy recalcs on every page load
        $cacheKey = 'admin_dashboard_' . ($surveyId ?? 'all');
        $data = Cache::remember($cacheKey, 60, function () use ($surveyId) {

            // Rating responses only (questions.type = 'rating')
            $ratingQuery = Response::whereHas('question', fn($q) => $q->where('type', 'rating'))
                ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId));

            // Pull numeric responses as collection of floats
            $ratingValues = $ratingQuery->pluck('response')
                ->map(fn($v) => is_numeric($v) ? (float) $v : null)
                ->filter();

            $count = $ratingValues->count();
            $mean = $count ? round($ratingValues->avg(), 3) : null;

            // median
            $median = null;
            if ($count) {
                $sorted = $ratingValues->sort()->values();
                $mid = (int) floor(($count - 1) / 2);
                $median = ($count % 2) ? $sorted[$mid] : round((($sorted[$mid] + $sorted[$mid + 1]) / 2), 3);
            }

            // mode
            $mode = null;
            if ($count) {
                $freq = $ratingValues->countBy()->sortDesc();
                $mode = $freq->keys()->first();
            }

            // population standard deviation
            $stddev = null;
            if ($count) {
                $avg = $mean;
                $variance = $ratingValues->reduce(fn($carry, $x) => $carry + pow($x - $avg, 2), 0) / $count;
                $stddev = round(sqrt($variance), 3);
            }

            // Sentiment aggregates per evaluatee
            $sentimentRows = Response::select('evaluatee_id', 'sentiment_label', DB::raw('count(*) as cnt'))
                ->when($surveyId, fn($q) => $q->where('survey_id', $surveyId))
                ->groupBy('evaluatee_id', 'sentiment_label')
                ->get()
                ->groupBy('evaluatee_id');

            $sentimentPerPerson = [];
            foreach ($sentimentRows as $evaluateeId => $group) {
                $total = $group->sum('cnt');
                $labels = $group->pluck('cnt', 'sentiment_label')->toArray();
                $positive = $labels['positive'] ?? 0;
                $negative = $labels['negative'] ?? 0;
                $neutral = $labels['neutral'] ?? 0;
                $user = User::find($evaluateeId);
                $sentimentPerPerson[] = [
                    'evaluatee_id' => $evaluateeId,
                    'name' => $user?->name ?? "User {$evaluateeId}",
                    'total' => $total,
                    'positive' => $positive,
                    'negative' => $negative,
                    'neutral' => $neutral,
                    'positive_pct' => $total ? round($positive / $total * 100, 1) : 0,
                    'negative_pct' => $total ? round($negative / $total * 100, 1) : 0,
                    'neutral_pct' => $total ? round($neutral / $total * 100, 1) : 0,
                ];
            }

            // Top performing faculty by average rating (require at least 3 ratings)
            $topPerformersQuery = DB::table('responses')
                ->join('questions', 'responses.question_id', '=', 'questions.id')
                ->select('responses.evaluatee_id', DB::raw('avg(CAST(responses.response AS DECIMAL(8,3))) as avg_rating'), DB::raw('count(*) as cnt'))
                ->where('questions.type', 'rating')
                ->when($surveyId, fn($q) => $q->where('responses.survey_id', $surveyId))
                ->groupBy('responses.evaluatee_id')
                ->having('cnt', '>=', 3)
                ->orderByDesc('avg_rating')
                ->limit(10)
                ->get();

            $topPerformers = $topPerformersQuery->map(function ($row) {
                $user = User::find($row->evaluatee_id);
                return [
                    'evaluatee_id' => $row->evaluatee_id,
                    'name' => $user?->name ?? "User {$row->evaluatee_id}",
                    'avg_rating' => round((float)$row->avg_rating, 3),
                    'count' => $row->cnt,
                ];
            })->toArray();

            // Monthly average rating time series (YYYY-MM)
            $monthly = DB::table('responses')
                ->join('questions', 'responses.question_id', '=', 'questions.id')
                ->select(DB::raw("DATE_FORMAT(responses.created_at, '%Y-%m') as month"), DB::raw('avg(CAST(responses.response AS DECIMAL(8,3))) as avg_rating'))
                ->where('questions.type', 'rating')
                ->when($surveyId, fn($q) => $q->where('responses.survey_id', $surveyId))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $monthlyLabels = $monthly->pluck('month')->toArray();
            $monthlyAvg = $monthly->pluck('avg_rating')->map(fn($v) => round((float)$v, 3))->toArray();

            return [
                'mean' => $mean,
                'median' => $median,
                'mode' => $mode,
                'stddev' => $stddev,
                'rating_count' => $count,
                'sentimentPerPerson' => $sentimentPerPerson,
                'topPerformers' => $topPerformers,
                'monthlyLabels' => $monthlyLabels,
                'monthlyAvg' => $monthlyAvg,
            ];
        });

        // Pass to view
        return view('admin.dashboard', $data);
    }
}