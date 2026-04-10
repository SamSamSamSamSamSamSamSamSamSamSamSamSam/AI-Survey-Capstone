<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\Semester;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherReviewController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        // Semester Filtering Logic
        $selectedSemesterId = $request->get('semester_id', Semester::getActive()?->id);
        $selectedSemester = Semester::find($selectedSemesterId);
        $semesters = Semester::orderBy('academic_year', 'desc')->orderBy('semester_number', 'desc')->get();

        if (!$selectedSemester) {
            return view('teacher.reviews', ['error' => 'No semester context available.']);
        }

        // 1. Sentiment Aggregation
        $sentimentStats = Response::where('evaluatee_id', $teacher->id)
            ->where('semester_id', $selectedSemesterId)
            ->whereNotNull('sentiment_label')
            ->select('sentiment_label', DB::raw('count(*) as count'))
            ->groupBy('sentiment_label')
            ->pluck('count', 'sentiment_label');

        // 2. Quantitative Category Scores
        // Assuming 'rating' type questions have numeric responses (1-5)
        $categoryScores = Response::join('questions', 'responses.question_id', '=', 'questions.id')
            ->where('responses.evaluatee_id', $teacher->id)
            ->where('responses.semester_id', $selectedSemesterId)
            ->where('questions.type', 'rating')
            ->select('questions.category', DB::raw('AVG(CAST(responses.response AS DECIMAL(10,2))) as average'))
            ->groupBy('questions.category')
            ->get();

        // 3. Qualitative Comments (Student Feedback) Grouped by Category
        $reviewsByCategory = Response::with(['subject', 'question'])
            ->where('evaluatee_id', $teacher->id)
            ->where('semester_id', $selectedSemesterId)
            ->whereHas('question', function($q) {
                $q->where('type', 'text');
            })
            ->get()
            ->groupBy(function($review) {
                return $review->question->category ?? 'General Feedback';
            });

        return view('teacher.reviews', compact(
            'sentimentStats', 
            'categoryScores', 
            'reviewsByCategory', // Pass the grouped collection
            'selectedSemester', 
            'semesters'
        ));
    }
}