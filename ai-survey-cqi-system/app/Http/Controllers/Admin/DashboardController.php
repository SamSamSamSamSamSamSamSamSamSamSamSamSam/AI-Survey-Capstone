<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CqiReport;
use App\Models\FacultyAnalytics;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ── Core KPIs ───────────────────────────────────────────────────────
        $totalSurveys   = Survey::count();
        $totalResponses = SurveyAttempt::whereNotNull('submitted_at')->count();
        $totalReports   = CqiReport::count();
        $totalUsers     = User::count();
        $liveSurveys    = Survey::where('is_active', true)->count();

        // ── Completion rate (submitted / started) ────────────────────────────
        $started        = SurveyAttempt::count();
        $completionRate = $started > 0 ? round(($totalResponses / $started) * 100, 1) : 0;

        // ── Reports this calendar month ──────────────────────────────────────
        $reportsThisMonth = CqiReport::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // ── System health ────────────────────────────────────────────────────
        $dbConnected = true;
        try {
            DB::connection()->getPdo();
        } catch (\Exception) {
            $dbConnected = false;
        }

        $isProcessing = DB::table('jobs')
            ->where(function ($q) {
                $q->where('payload', 'like', '%AnalyzeSentimentJob%')
                  ->orWhere('payload', 'like', '%GenerateCqiReportJob%')
                  ->orWhere('payload', 'like', '%ComputeFacultyAnalyticsJob%')
                  ->orWhere('payload', 'like', '%SendSurveySubmittedNotificationJob%');
            })
            ->exists();

        // ── System-wide quality aggregates (from faculty_analytics) ──────────
        $systemAnalytics = DB::table('faculty_analytics')
            ->whereNull('deleted_at')
            ->selectRaw('
                AVG(avg_rating)                  AS global_avg,
                AVG(positive_sentiment_percent)  AS pos_avg,
                AVG(neutral_sentiment_percent)   AS neu_avg,
                AVG(negative_sentiment_percent)  AS neg_avg
            ')
            ->first();

        // Sentiment data shaped for Chart.js doughnut
        $sentimentData = [
            'positive' => round($systemAnalytics->pos_avg ?? 0, 1),
            'neutral'  => round($systemAnalytics->neu_avg ?? 0, 1),
            'negative' => round($systemAnalytics->neg_avg ?? 0, 1),
        ];

        // ── Top 5 faculty by avg_rating ──────────────────────────────────────
        // Eager-load the relationships actually used in the blade
        $topPerformers = FacultyAnalytics::with([
                'faculty',
                'survey.offering.subject',
            ])
            ->whereNotNull('avg_rating')
            ->orderByDesc('avg_rating')
            ->take(5)
            ->get();

        // ── Recent surveys (last 5, newest first) ────────────────────────────
        $recentSurveys = Survey::with(['creator', 'offering.subject'])
            ->latest()
            ->take(5)
            ->get();

        // ── Responses over the last 12 months (activity chart) ───────────────
        $responsesChart = SurveyAttempt::query()
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(submitted_at, '%b %Y') AS month")
            ->selectRaw("DATE_FORMAT(submitted_at, '%Y-%m') AS sort_key")
            ->selectRaw('COUNT(*) AS count')
            ->groupBy('month', 'sort_key')
            ->orderBy('sort_key')
            ->get()
            ->keyBy('month');

        // ── Surveys created over the last 12 months ──────────────────────────
        $surveysCreatedChart = Survey::query()
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%b %Y') AS month")
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS sort_key")
            ->selectRaw('COUNT(*) AS count')
            ->groupBy('month', 'sort_key')
            ->orderBy('sort_key')
            ->get()
            ->keyBy('month');

        // Fill missing months so charts always show exactly 12 points
        $filledChart        = [];
        $surveysChartFilled = [];

        for ($i = 11; $i >= 0; $i--) {
            $label = now()->subMonths($i)->format('M Y');
            $filledChart[]        = ['month' => $label, 'count' => $responsesChart->get($label)?->count ?? 0];
            $surveysChartFilled[] = ['month' => $label, 'count' => $surveysCreatedChart->get($label)?->count ?? 0];
        }

        return view('admin.dashboard', compact(
            // KPIs
            'totalSurveys',
            'totalResponses',
            'totalReports',
            'totalUsers',
            'liveSurveys',
            'completionRate',
            'reportsThisMonth',
            // System
            'dbConnected',
            'isProcessing',
            // Quality
            'systemAnalytics',
            'sentimentData',
            'topPerformers',
            // Lists
            'recentSurveys',
            // Charts
            'filledChart',
            'surveysChartFilled',
        ));
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Ensure every month in the last 12 months has a data point (fill 0s).
     */
    private function fillMissingMonths(array $data): array
    {
        $keyed = collect($data)->keyBy('month');
        $result = [];

        for ($i = 11; $i >= 0; $i--) {
            $label = now()->subMonths($i)->format('M Y');
            $result[] = [
                'month' => $label,
                'count' => $keyed->get($label)['count'] ?? 0,
            ];
        }

        return $result;
    }
}