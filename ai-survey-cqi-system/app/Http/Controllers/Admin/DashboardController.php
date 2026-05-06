<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CqiReport;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ── KPI counts ──────────────────────────────────────────────────────
        $totalSurveys   = Survey::count();
        $totalResponses = SurveyAttempt::whereNotNull('submitted_at')->count();
        $totalReports   = CqiReport::count();
        $totalUsers     = User::count();

        // ── Recent surveys (last 5, newest first) ───────────────────────────
        $recentSurveys = Survey::with('creator')
            ->latest()
            ->take(5)
            ->get();

        // ── Responses over the last 12 months (for the chart) ───────────────
        // Returns rows: { month: "Jan 2025", count: 42 }
        $responsesChart = SurveyAttempt::query()
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(submitted_at, '%b %Y') as month"),
                DB::raw("DATE_FORMAT(submitted_at, '%Y-%m') as sort_key"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month', 'sort_key')
            ->orderBy('sort_key')
            ->get()
            ->map(fn ($r) => ['month' => $r->month, 'count' => $r->count]);

        // Fill in any missing months so the chart always shows 12 points
        $filledChart = $this->fillMissingMonths($responsesChart->toArray());

        // ── Surveys created over the last 12 months ──────────────────────────
        $surveysChart = Survey::query()
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%b %Y') as month"),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as sort_key"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month', 'sort_key')
            ->orderBy('sort_key')
            ->get()
            ->keyBy('month');

        $surveysChartFilled = collect($filledChart)->map(function ($point) use ($surveysChart) {
            return [
                'month' => $point['month'],
                'count' => $surveysChart->get($point['month'])?->count ?? 0,
            ];
        })->values()->toArray();

        // ── Live surveys count ───────────────────────────────────────────────
        $liveSurveys = Survey::live()->count();

        // ── CQI reports generated this month ────────────────────────────────
        $reportsThisMonth = CqiReport::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $dbConnected = true;
            try {
                \DB::connection()->getPdo();
            } catch (\Exception $e) {
                $dbConnected = false;
            }
        $isProcessing = DB::table('jobs')
            ->where(function($q) {
                $q->where('payload', 'like', '%AnalyzeSentimentJob%')
                ->orWhere('payload', 'like', '%GenerateCqiReportJob%');
            })
            ->exists();

        return view('admin.dashboard', compact(
            'totalSurveys',
            'totalResponses',
            'totalReports',
            'totalUsers',
            'recentSurveys',
            'filledChart',
            'surveysChartFilled',
            'liveSurveys',
            'reportsThisMonth',
            'dbConnected',
            'isProcessing',
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