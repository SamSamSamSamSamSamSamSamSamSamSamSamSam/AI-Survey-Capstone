<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $teacher        = auth()->user();
        $activeSemester = Semester::getActive();

        // Classes scoped to active semester
        $classes = $activeSemester
            ? $teacher->teachingSubjectsForSemester($activeSemester->id)
                      ->withCount('students')
                      ->get()
                      ->map(fn($s) => [
                          'id'       => $s->id,
                          'code'     => $s->course_code,
                          'title'    => $s->name,
                          'group'    => $s->pivot->group,
                          'students' => $s->students()->count(),
                      ])
            : collect();

        // Active surveys scoped to active semester
        $activeSurveys = Survey::whereIn('subject_id', $classes->pluck('id'))
            ->where('is_active', 1)
            ->when($activeSemester, fn($q) => $q->where('semester_id', $activeSemester->id))
            ->get();

        // Top-performing faculty (all time — not semester scoped, for context)
        $topPerformersQuery = DB::table('responses')
            ->join('questions', 'responses.question_id', '=', 'questions.id')
            ->select(
                'responses.evaluatee_id',
                DB::raw('AVG(CAST(responses.response AS DECIMAL(8,3))) as avg_rating'),
                DB::raw('COUNT(*) as cnt')
            )
            ->where('questions.type', 'rating')
            ->groupBy('responses.evaluatee_id')
            ->having('cnt', '>=', 3)
            ->orderByDesc('avg_rating')
            ->limit(10)
            ->get();

        $topPerformers = $topPerformersQuery->map(function ($row) {
            $user = User::find($row->evaluatee_id);
            return [
                'name'            => $user?->name ?? "User {$row->evaluatee_id}",
                'avg_rating'      => round((float) $row->avg_rating, 2),
                'responses_count' => $row->cnt,
            ];
        });

        $role = $teacher->roles->first()?->name ?? 'N/A';

        return view('teacher.dashboard', compact(
            'classes',
            'activeSurveys',
            'topPerformers',
            'role',
            'activeSemester'
        ));
    }

    public function reviews()
    {
        return "Teacher Reviews Page - Under Construction";
    }
}