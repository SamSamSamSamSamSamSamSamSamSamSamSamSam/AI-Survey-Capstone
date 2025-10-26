<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    /** Display all surveys */
    public function index()
    {
        $surveys = Survey::with(['questions', 'creator', 'subject'])
            ->withCount('questions')
            ->latest()
            ->get();

        return view('admin.surveys.index', compact('surveys'));
    }

    /** Show create form */
    public function create()
    {
        $faculty = User::whereHas('roles', fn($q) => 
            $q->whereIn('name', ['teacher', 'admin'])
        )->orderBy('name')->get();

        return view('admin.surveys.create', compact('faculty'));
    }

    /** Store a new survey */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'evaluatee_id' => 'required|exists:users,id',
            'target_role' => 'required|in:admin,teacher,student,both',
            'subject_id' => 'required_unless:target_role,teacher|nullable|exists:subjects,id',
            'group' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*' => 'required|array|min:1',
            'questions.*.*' => 'required|string',
            'question_types' => 'required|array|min:1',
            'question_types.*' => 'required|array|min:1',
            'question_types.*.*' => 'required|string|in:rating,text',
        ]);

        $subjectId = $request->target_role === 'teacher' ? null : $request->subject_id;
        $group = $request->target_role === 'teacher' ? null : $request->group;

        $survey = Survey::create([
            'title' => $request->title,
            'description' => $request->description,
            'evaluatee_id' => $request->evaluatee_id,
            'subject_id' => $subjectId,
            'group' => $group,
            'created_by' => auth()->id(),
            'target_role' => $request->target_role,
            'is_active' => true,
        ]);

        foreach ($request->questions as $category => $questionsInCategory) {
            foreach ($questionsInCategory as $index => $text) {
                Question::create([
                    'survey_id' => $survey->id,
                    'question_text' => $text,
                    'type' => $request->question_types[$category][$index] ?? 'text',
                    'category' => $category,
                    'order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.surveys.index')->with('success', 'Survey created successfully!');
    }

    /** Fetch subjects by teacher ID (AJAX) */
    public function getSubjectsByTeacher($teacherId)
    {
        $subjects = Subject::whereHas('teachers', fn($q) => 
                $q->where('users.id', $teacherId)
            )
            ->with(['teachers' => fn($q) => $q->where('users.id', $teacherId)])
            ->get()
            ->map(function ($subject) use ($teacherId) {
                $teacher = $subject->teachers->firstWhere('id', $teacherId);
                return [
                    'id' => $subject->id,
                    'name' => "{$teacher->pivot->group} - {$subject->course_code}",
                    'group' => $teacher->pivot->group,
                    'course_code' => $subject->course_code,
                    'subject_name' => $subject->name,
                ];
            });

        return response()->json($subjects);
    }

    /** Show individual survey */
    public function show($id)
    {
        $survey = Survey::with(['creator', 'questions', 'evaluatee', 'subject'])
            ->findOrFail($id);

        return view('admin.surveys.show', compact('survey'));
    }

    /** Show edit form */
    public function edit($id)
    {
        $survey = Survey::with(['questions', 'evaluatee.teachingSubjects'])->findOrFail($id);

        $evaluatees = User::whereHas('roles', fn($q) => 
            $q->whereIn('name', ['teacher', 'admin'])
        )->orderBy('name')->get();

        $subjects = [];

        if ($survey->evaluatee) {
            $subjects = Subject::whereHas('teachers', fn($q) => 
                    $q->where('users.id', $survey->evaluatee_id)
                )
                ->with(['teachers' => fn($q) => $q->where('users.id', $survey->evaluatee_id)])
                ->get()
                ->map(function ($subject) use ($survey) {
                    $teacher = $subject->teachers->firstWhere('id', $survey->evaluatee_id);
                    return [
                        'id' => $subject->id,
                        'name' => "{$teacher->pivot->group} - {$subject->course_code}",
                        'group' => $teacher->pivot->group,
                    ];
                });
        }

        return view('admin.surveys.edit', compact('survey', 'evaluatees', 'subjects'));
    }

    /** Update existing survey */
    public function update(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_role' => 'required|string|in:admin,teacher,student,both',
            'evaluatee_id' => 'nullable|exists:users,id',
            'subject_id' => 'required_unless:target_role,teacher|nullable|exists:subjects,id',
            'group' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*' => 'required|array|min:1',
            'questions.*.*' => 'required|string',
            'question_types' => 'required|array|min:1',
            'question_types.*' => 'required|array|min:1',
            'question_types.*.*' => 'required|string|in:rating,text',
        ]);

        $subjectId = $validated['target_role'] === 'teacher' ? null : $validated['subject_id'];
        $group = $validated['target_role'] === 'teacher' ? null : $validated['group'];

        $survey->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'target_role' => $validated['target_role'],
            'evaluatee_id' => $validated['evaluatee_id'] ?? null,
            'subject_id' => $subjectId,
            'group' => $group,
        ]);

        $survey->questions()->delete();

        foreach ($validated['questions'] as $category => $questionsInCategory) {
            foreach ($questionsInCategory as $index => $text) {
                $survey->questions()->create([
                    'question_text' => $text,
                    'type' => $validated['question_types'][$category][$index] ?? 'text',
                    'category' => $category,
                    'order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.surveys.index')->with('success', 'Survey updated successfully!');
    }

    /** Delete a survey */
    public function destroy(Survey $survey)
    {
        $survey->questions()->delete();
        $survey->delete();

        return redirect()->route('admin.surveys.index')->with('success', 'Survey deleted successfully!');
    }

    /** Toggle activation status */
    public function toggleStatus(Survey $survey)
    {
        $survey->update(['is_active' => !$survey->is_active]);
        $status = $survey->is_active ? 'activated' : 'deactivated';

        return redirect()->back()->with('success', "Survey {$status} successfully!");
    }

    /** Load the official ISMIS questionnaire */
    public function useOfficialQuestionnaire()
    {
        $questions = [
            "Classroom Management" => [
                ["The teacher motivates us to participate in the activities.", "rating"],
                ["The teacher provides us opportunities to express our ideas.", "rating"],
                ["The teacher deals with our questions and comments.", "rating"],
                ["The teacher clarified directions on assignments and other requirements when needed.", "rating"],
                ["The teacher engages her students actively during class meetings.", "rating"],
                ["The teacher’s teaching presence can be felt in asynchronous online class activities.", "rating"],
                ["The teacher responds to correspondence sent via email, through the LMS or through official channels within a reasonable time.", "rating"],
                ["How has the classroom environment, teaching practices, or structure influenced your learning experience, and what improvements would you recommend?", "text"],
            ],
            "Teaching and Learning" => [
                ["The teacher’s teaching presence in regular class meetings (synchronous or face-to-face) motivates me to actively participate in this course.", "rating"],
                ["The course provided applications that relate to my program specialization and other relevant fields.", "rating"],
                ["The teacher used varied and engaging teaching strategies which facilitated my learning.", "rating"],
                ["The teacher integrated technology tools effectively which supported my learning.", "rating"],
                ["The pacing of course activities provided me adequate time to reflect and apply my learning.", "rating"],
                ["The teacher engages us with questions to deepen our learning.", "rating"],
                ["Assignments are designed to provide us opportunity to demonstrate our learning.", "rating"],
                ["The requirements are relevant to the stated unit or course outcomes.", "rating"],
                ["The requirements are well-paced to give me adequate time to work on them.", "rating"],
                ["The teacher provides us opportunities to reflect on our learning experiences.", "rating"],
                ["The syllabus is a well-organized plan that provides an overview of the course.", "rating"],
                ["The syllabus clearly describes to me what I will be able to learn and do at the end of the course.", "rating"],
                ["The syllabus provides varied learning resources that can support my learning.", "rating"],
                ["The learning plan in the syllabus shows the connections of the stated outcomes and content with learning activities and assessments.", "rating"],
                ["I can demonstrate the stated unit outcomes of the course with competence.", "rating"],
                ["I can apply the knowledge and skills I learned in this course to analyze problems, create products or perform processes.", "rating"],
                ["I can communicate my learning in this course orally or in written form.", "rating"],
                ["I can connect theory and practical knowledge of this course.", "rating"],
                ["I have improved my problem-solving, critical thinking and decision-making skills through this course.", "rating"],
                ["Which teaching strategies or activities enhanced your understanding of the lessons, and what improvements can be made to clarify confusing topics and improve lesson delivery?", "text"],
            ],
            "Assessment" => [
                ["The course syllabus provides the information that serves as the basis for our grades (e.g. requirements, rubrics and grade components).", "rating"],
                ["The results of tests, assignments and other tasks are returned with feedback on my performance.", "rating"],
                ["The varied forms of assessments in this course enable me to track my own learning progress.", "rating"],
                ["The tests and other requirements that provide the basis for my grade are clearly communicated.", "rating"],
                ["Do you feel the assessments accurately reflect what was taught, and how would you prefer your learning to be evaluated?", "text"],
            ],
            "General Open-Ended Questions" => [
                ["What do you like best about this course?", "text"],
                ["What aspects of this course would you want to be improved?", "text"],
                ["What is your overall experience in this course?", "text"],
                ["Will you recommend this course under the present instructor/professor?", "text"],
            ],
        ];

        return response()->json($questions);
    }


}
