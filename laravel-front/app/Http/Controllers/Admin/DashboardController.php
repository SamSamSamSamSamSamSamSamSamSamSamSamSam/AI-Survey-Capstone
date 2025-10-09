<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\User;
use App\Models\Response;
use App\Models\CqiReport;

class DashboardController extends Controller
{
    public function index()
    {
        // --- Summary Metrics ---
        $totalSurveys = Survey::count();
        $facultyCount = User::whereHas('roles', fn($q) => $q->where('name', 'faculty'))->count();
        $studentCount = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();
        $avgRating    = round(Response::avg('response'), 2) ?: 0;
        $cqiReports   = CqiReport::count();

        // --- Chart Data ---
        $departmentPerformance = Response::selectRaw('subject_id, AVG(response) as avg_rating')
            ->groupBy('subject_id')
            ->pluck('avg_rating', 'subject_id')
            ->toArray();

        if (empty($departmentPerformance)) {
            $departmentPerformance = ['Math' => 4.2, 'Science' => 3.8, 'English' => 4.5];
        }

        $participation = Survey::selectRaw('title, (SELECT COUNT(*) FROM responses WHERE responses.survey_id = surveys.id) as participant_count')
            ->pluck('participant_count', 'title')
            ->toArray();

        if (empty($participation)) {
            $participation = ['Midterm Survey' => 45, 'Finals Survey' => 52, 'Course Eval' => 38];
        }

        $sentimentCounts = Response::selectRaw('response, COUNT(*) as count')
            ->groupBy('response')
            ->pluck('count', 'response')
            ->toArray();

        if (empty($sentimentCounts)) {
            $sentimentCounts = ['Positive' => 60, 'Neutral' => 25, 'Negative' => 15];
        }

        $facultyPerformance = Response::selectRaw('subject_id, AVG(response) as avg_rating')
            ->groupBy('subject_id')
            ->pluck('avg_rating', 'subject_id')
            ->toArray();

        if (empty($facultyPerformance)) {
            $facultyPerformance = ['Prof. A' => 4.3, 'Prof. B' => 3.9, 'Prof. C' => 4.6];
        }

        $topFaculty = User::whereHas('roles', fn($q) => $q->where('name', 'faculty'))
            ->withAvg('evaluationsReceived as avg_rating', 'response')
            ->orderByDesc('avg_rating')
            ->take(5)
            ->pluck('avg_rating', 'name')
            ->toArray();

        if (empty($topFaculty)) {
            $topFaculty = ['Prof. Reyes' => 4.7, 'Prof. Santos' => 4.6, 'Prof. Cruz' => 4.4];
        }

        $sentimentTrend = Response::selectRaw('survey_id, 
                SUM(response >= 4)*100/COUNT(*) as positive,
                SUM(response <= 2)*100/COUNT(*) as negative')
            ->groupBy('survey_id')
            ->orderBy('survey_id')
            ->get()
            ->map(fn($item) => [
                'semester' => 'Survey '.$item->survey_id,
                'positive' => round($item->positive, 2),
                'negative' => round($item->negative, 2)
            ])
            ->toArray();

        if (empty($sentimentTrend)) {
            $sentimentTrend = [
                ['semester' => 'Midterm 2025', 'positive' => 72, 'negative' => 15],
                ['semester' => 'Finals 2025', 'positive' => 68, 'negative' => 20],
                ['semester' => '1st Sem 2026', 'positive' => 80, 'negative' => 10],
            ];
        }

        $cqiReportsList = CqiReport::latest()->take(5)->get()->toArray();
        if (empty($cqiReportsList)) {
            $cqiReportsList = [
                ['title' => 'Q1 Faculty Evaluation', 'survey' => 'Midterm 2025', 'author' => 'Admin', 'date' => '2025-05-15'],
                ['title' => 'Engineering Dept Review', 'survey' => 'Finals 2025', 'author' => 'QA Office', 'date' => '2025-06-05'],
            ];
        }

        $comments = Response::latest()->take(6)->get(['evaluator_id', 'response'])->toArray();
        if (empty($comments)) {
            $comments = [
                ['author' => 'Student A', 'text' => 'The instructor provides detailed feedback.'],
                ['author' => 'Student B', 'text' => 'Lectures are engaging and well-prepared.'],
                ['author' => 'Student C', 'text' => 'Would appreciate faster grading turnaround.'],
            ];
        }

        return view('admin.dashboard', compact(
            'totalSurveys', 'facultyCount', 'studentCount', 'avgRating', 'cqiReports',
            'departmentPerformance', 'participation', 'sentimentCounts',
            'facultyPerformance', 'topFaculty', 'sentimentTrend', 'cqiReportsList', 'comments'
        ));
    }
}
