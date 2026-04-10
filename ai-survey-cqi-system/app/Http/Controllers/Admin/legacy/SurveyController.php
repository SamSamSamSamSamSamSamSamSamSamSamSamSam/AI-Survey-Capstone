<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Question;
use App\Models\CourseOffering;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────────

    public function index()
    {
        $surveys = Survey::with(['offering.subject', 'offering.teacher', 'offering.semester', 'creator', 'targetRole'])
            ->withCount('questions')
            ->latest()
            ->get();

        $activeSemester = Semester::getActive();

        return view('admin.surveys.index', compact('surveys', 'activeSemester'));
    }

    // ── Create ─────────────────────────────────────────────────────────────────

    public function create()
    {
        $activeSemester = Semester::getActive();

        // Course offerings in the active semester, with teacher + subject for display
        $offerings = $activeSemester
            ? CourseOffering::with(['subject', 'teacher'])
                ->where('semester_id', $activeSemester->id)
                ->orderBy('section_name')
                ->get()
            : collect();

        // Roles available as survey targets
        $roles = Role::orderBy('name')->get();

        return view('admin.surveys.create', compact('offerings', 'roles', 'activeSemester'));
    }

    // ── Store ──────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'offering_id'    => 'required|ulid|exists:course_offerings,id',
            'target_role_id' => 'required|integer|exists:roles,id',
            'questions'               => 'required|array|min:1',
            'questions.*'             => 'required|array|min:1',
            'questions.*.*'           => 'required|string',
            'question_types'          => 'required|array|min:1',
            'question_types.*'        => 'required|array|min:1',
            'question_types.*.*'      => 'required|string|in:rating,text',
        ]);

        $survey = Survey::create([
            'title'          => $request->title,
            'description'    => $request->description,
            'offering_id'    => $request->offering_id,
            'target_role_id' => $request->target_role_id,
            'created_by'     => auth()->id(),
            'is_active'      => true,
        ]);

        $this->syncQuestions($survey, $request->questions, $request->question_types);

        return redirect()->route('admin.surveys.index')
            ->with('success', 'Survey created successfully!');
    }

    // ── Show ───────────────────────────────────────────────────────────────────

    public function show(Survey $survey)
    {
        $survey->load(['creator', 'questions', 'targetRole', 'offering.subject', 'offering.teacher', 'offering.semester']);

        return view('admin.surveys.show', compact('survey'));
    }

    // ── Edit ───────────────────────────────────────────────────────────────────

    public function edit(Survey $survey)
    {
        $survey->load(['questions', 'offering.subject', 'offering.teacher']);

        $activeSemester = Semester::getActive();

        $offerings = $activeSemester
            ? CourseOffering::with(['subject', 'teacher'])
                ->where('semester_id', $activeSemester->id)
                ->orderBy('section_name')
                ->get()
            : collect();

        $roles = Role::orderBy('name')->get();

        return view('admin.surveys.edit', compact('survey', 'offerings', 'roles', 'activeSemester'));
    }

    // ── Update ─────────────────────────────────────────────────────────────────

    public function update(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'offering_id'    => 'required|ulid|exists:course_offerings,id',
            'target_role_id' => 'required|integer|exists:roles,id',
            'questions'               => 'required|array|min:1',
            'questions.*'             => 'required|array|min:1',
            'questions.*.*'           => 'required|string',
            'question_types'          => 'required|array|min:1',
            'question_types.*'        => 'required|array|min:1',
            'question_types.*.*'      => 'required|string|in:rating,text',
        ]);

        $survey->update([
            'title'          => $validated['title'],
            'description'    => $validated['description'] ?? null,
            'offering_id'    => $validated['offering_id'],
            'target_role_id' => $validated['target_role_id'],
        ]);

        // Soft-delete old questions, recreate fresh
        $survey->questions()->delete();
        $this->syncQuestions($survey, $validated['questions'], $validated['question_types']);

        return redirect()->route('admin.surveys.index')
            ->with('success', 'Survey updated successfully!');
    }

    // ── Destroy ────────────────────────────────────────────────────────────────

    public function destroy(Survey $survey)
    {
        $survey->questions()->delete();
        $survey->delete();

        return redirect()->route('admin.surveys.index')
            ->with('success', 'Survey deleted successfully!');
    }

    // ── Toggle Status ──────────────────────────────────────────────────────────

    public function toggleStatus(Survey $survey)
    {
        $survey->update(['is_active' => ! $survey->is_active]);
        $status = $survey->is_active ? 'activated' : 'deactivated';

        return redirect()->back()->with('success', "Survey {$status} successfully!");
    }

    // ── Duplicate ──────────────────────────────────────────────────────────────

    public function duplicate(Survey $survey)
    {
        $survey->load('questions');
        $activeSemester = Semester::getActive();

        // Find the equivalent offering in the active semester for the same subject+teacher.
        // Falls back to the original offering_id if no match is found.
        $newOfferingId = $survey->offering_id;

        if ($activeSemester) {
            $original = $survey->offering;
            $match    = CourseOffering::where('semester_id', $activeSemester->id)
                ->where('subject_id', $original->subject_id)
                ->where('teacher_id', $original->teacher_id)
                ->first();

            if ($match) {
                $newOfferingId = $match->id;
            }
        }

        $newSurvey = Survey::create([
            'title'          => $survey->title . ' (Copy)',
            'description'    => $survey->description,
            'offering_id'    => $newOfferingId,
            'target_role_id' => $survey->target_role_id,
            'created_by'     => auth()->id(),
            'is_active'      => false,
        ]);

        foreach ($survey->questions as $question) {
            $newSurvey->questions()->create([
                'question_text' => $question->question_text,
                'type'          => $question->type,
                'category'      => $question->category,
                'order'         => $question->order,
            ]);
        }

        $semesterLabel = $activeSemester?->name ?? 'no active semester';

        return redirect()->route('admin.surveys.index')
            ->with('success', "Survey duplicated into {$semesterLabel}. It is inactive — review and activate when ready.");
    }

    // ── AJAX: get offerings by teacher ─────────────────────────────────────────

    /**
     * Returns course offerings in the active semester filtered by teacher.
     * Used to populate the offering dropdown after selecting a faculty member.
     */
    public function getOfferingsByTeacher(int $teacherId)
    {
        $activeSemester = Semester::getActive();

        $offerings = CourseOffering::with('subject')
            ->where('teacher_id', $teacherId)
            ->when($activeSemester, fn($q) => $q->where('semester_id', $activeSemester->id))
            ->orderBy('section_name')
            ->get()
            ->map(fn($o) => [
                'id'           => $o->id,
                'section_name' => $o->section_name,
                'course_code'  => $o->subject->course_code,
                'subject_name' => $o->subject->name,
                'label'        => "{$o->subject->course_code} — {$o->section_name}",
            ]);

        return response()->json($offerings);
    }

    // ── Official questionnaire ─────────────────────────────────────────────────

    public function useOfficialQuestionnaire()
    {
        $questions = [
            "Classroom Management" => [
                ["The teacher motivates us to participate in the activities.", "rating"],
                ["The teacher provides us opportunities to express our ideas.", "rating"],
                ["The teacher deals with our questions and comments.", "rating"],
                ["The teacher clarified directions on assignments and other requirements when needed.", "rating"],
                ["The teacher engages her students actively during class meetings.", "rating"],
                ["The teacher's teaching presence can be felt in asynchronous online class activities.", "rating"],
                ["The teacher responds to correspondence sent via email, through the LMS or through official channels within a reasonable time.", "rating"],
                ["How has the classroom environment, teaching practices, or structure influenced your learning experience, and what improvements would you recommend?", "text"],
            ],
            "Teaching and Learning" => [
                ["The teacher's teaching presence in regular class meetings (synchronous or face-to-face) motivates me to actively participate in this course.", "rating"],
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

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * Create questions for a survey from the nested category → text array
     * and parallel category → type array submitted by the form.
     */
    private function syncQuestions(Survey $survey, array $questions, array $questionTypes): void
    {
        foreach ($questions as $category => $questionsInCategory) {
            foreach ($questionsInCategory as $index => $text) {
                $survey->questions()->create([
                    'question_text' => $text,
                    'type'          => $questionTypes[$category][$index] ?? 'text',
                    'category'      => $category,
                    'order'         => $index,
                ]);
            }
        }
    }
}