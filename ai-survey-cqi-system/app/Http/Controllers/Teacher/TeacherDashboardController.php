<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\User;


class TeacherDashboardController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();

        // Classes this teacher handles
        $classes = $teacher->teachingSubjects()->withCount('students')->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'code' => $s->course_code,
                'title' => $s->name,
                'group' => $s->pivot->group,
                'students' => $s->students()->count(),
            ];
        });

        // Active surveys for teacher's classes
        $activeSurveys = Survey::whereIn('subject_id', $teacher->teachingSubjects->pluck('id'))
                            ->where('is_active', 1)
                            ->get();

        // Top-performing teachers (min 3 responses)
        $topPerformersQuery = \DB::table('responses')
            ->join('questions', 'responses.question_id', '=', 'questions.id')
            ->select('responses.evaluatee_id', \DB::raw('AVG(CAST(responses.response AS DECIMAL(8,3))) as avg_rating'), \DB::raw('COUNT(*) as cnt'))
            ->where('questions.type', 'rating')
            ->groupBy('responses.evaluatee_id')
            ->having('cnt', '>=', 3)
            ->orderByDesc('avg_rating')
            ->limit(10)
            ->get();

        $topPerformers = $topPerformersQuery->map(function ($row) {
            $user = \App\Models\User::find($row->evaluatee_id);
            return [
                'name' => $user?->name ?? "User {$row->evaluatee_id}",
                'avg_rating' => round((float)$row->avg_rating, 2),
                'responses_count' => $row->cnt,
            ];
        });

        // First role
        $role = $teacher->roles->first()?->name ?? 'N/A';

        return view('teacher.dashboard', compact('classes', 'activeSurveys', 'topPerformers', 'role'));
    }

    public function reviews()
    {
        // return view('teacher.reviews');
        return "Teacher Reviews Page - Under Construction line 62 in TeacherDashboardController.php";
    }


}
