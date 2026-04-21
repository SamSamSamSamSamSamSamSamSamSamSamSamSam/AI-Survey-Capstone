<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\EnrollmentType;
use App\Models\OfferingType;
use App\Models\Program;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Services\CsvValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SemesterSetupController extends Controller
{
    public const STEPS = [
        1 => ['key' => 'students',   'label' => 'Register Students',  'icon' => 'bi-person-fill-add'],
        2 => ['key' => 'blocks',     'label' => 'Create Blocks',      'icon' => 'bi-grid-3x3-gap-fill'],
        3 => ['key' => 'offerings',  'label' => 'Import Offerings',   'icon' => 'bi-card-list'],
        4 => ['key' => 'enrollments','label' => 'Import Enrollments', 'icon' => 'bi-journal-plus'],
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $activeSemester = Semester::current();

        if (! $activeSemester) {
            return redirect()->route('admin.semesters.index')
                             ->with('error', 'Please set an active semester before running the setup wizard.');
        }

        $step = max(1, min(4, (int) $request->input('step', 1)));

        return view('admin.semester-setup.wizard', [
            'activeSemester' => $activeSemester,
            'steps'          => self::STEPS,
            'step'           => $step, // current active step
            'stats'          => $this->getStepStats($activeSemester),
        ]);
    }

    public function previewValidation(Request $request): JsonResponse
    {
        $this->validateCsvUpload($request);

        $step = (int) $request->input('step', 1);
        $rows = $this->parseCsv($request);

        if (empty($rows)) {
            return response()->json(['can_proceed' => false, 'errors' => [['line' => 0, 'message' => 'CSV is empty.']]], 422);
        }

        $activeSemester = Semester::current();
        $validator = new CsvValidationService($activeSemester);

        $result = match ($step) {
            1 => $validator->validateStudents($rows),
            2 => $validator->validateBlocks($rows),
            3 => $validator->validateOfferings($rows),
            4 => $validator->validateEnrollments($rows),
            default => ['can_proceed' => false, 'errors' => [['line' => 0, 'message' => 'Invalid step.']]],
        };

        return response()->json($result);
    }

    public function importStudents(Request $request): RedirectResponse
    {
        $this->validateCsvUpload($request);
        $rows = $this->parseCsv($request);
        $activeSemester = Semester::current();
        
        $validator = new CsvValidationService($activeSemester);
        $validation = $validator->validateStudents($rows);

        if (!$validation['can_proceed']) {
            return redirect()->back()->with('error', 'Validation failed.');
        }

        $created = 0;
        $studentRole = Role::whereName('student')->firstOrFail();

        DB::transaction(function () use ($validation, $studentRole, &$created) {
            foreach ($validation['valid_rows'] as $item) {
                $row = $item['row'];
                $user = User::create([
                    'user_id_number'    => trim($row['user_id_number']),
                    'name'              => trim($row['name']),
                    'email'             => strtolower(trim($row['email'])),
                    'password'          => Hash::make(trim($row['user_id_number'])),
                    'email_verified_at' => now(),
                ]);
                $user->roles()->syncWithoutDetaching([$studentRole->id]);
                $created++;
            }
        });

        return $this->wizardRedirect(1, $created, $validation['skipped_count'], []);
    }

    public function importBlocks(Request $request): RedirectResponse
    {
        $this->validateCsvUpload($request);
        $rows = $this->parseCsv($request);
        $activeSemester = Semester::current();

        $validator = new CsvValidationService($activeSemester);
        $validation = $validator->validateBlocks($rows);

        if (!$validation['can_proceed']) {
            return redirect()->back()->withErrors($validation['errors']);
        }

        $created = 0;
        DB::transaction(function () use ($validation, $activeSemester, &$created) {
            foreach ($validation['valid_rows'] as $item) {
                $row = $item['row'];
                $program = Program::where('program_code', strtoupper(trim($row['program_code'])))->first();
                
                if ($program) {
                    // Use updateOrCreate to avoid Duplicate Entry exceptions
                    Block::updateOrCreate(
                        [
                            'program_id'  => $program->id,
                            'semester_id' => $activeSemester->id,
                            'name'        => strtoupper(trim($row['block_name'])),
                        ],
                        [
                            'year_level'  => (int) $row['year_level'],
                        ]
                    );
                    $created++;
                }
            }
        });

        return $this->wizardRedirect(2, $created, $validation['skipped_count'], []);
    }

    public function importOfferings(Request $request): RedirectResponse
    {
        $this->validateCsvUpload($request);
        $rows = $this->parseCsv($request);
        $activeSemester = Semester::current();

        $validator = new CsvValidationService($activeSemester);
        $validation = $validator->validateOfferings($rows);

        if (!$validation['can_proceed']) return redirect()->back();

        $created = 0;
        DB::transaction(function () use ($validation, $activeSemester, &$created) {
            foreach ($validation['valid_rows'] as $item) {
                $row = $item['row'];
                $subject = Subject::where('course_code', strtoupper(trim($row['subject_code'])))->first();
                $teacher = User::where('user_id_number', trim($row['teacher_id_number']))->first();
                    
                $blockId = !empty($row['block_name']) 
                    ? Block::where('name', strtoupper(trim($row['block_name'])))->where('semester_id', $activeSemester->id)->first()?->id 
                    : null;

                $offeringTypeId = !empty($row['offering_type'])
                    ? OfferingType::where('name', 'like', trim($row['offering_type']))->first()?->id
                    : null;

                CourseOffering::create([
                    'subject_id'       => $subject->id,
                    'semester_id'      => $activeSemester->id,
                    'teacher_id'       => $teacher->id,
                    'block_id'         => $blockId,
                    'group_number'     => $row['group_number'] ?? null,
                    'offering_type_id' => $offeringTypeId,
                ]);
                $created++;
            }
        });

        return $this->wizardRedirect(3, $created, $validation['skipped_count'], []);
    }

    public function importEnrollments(Request $request): RedirectResponse
    {
        $this->validateCsvUpload($request);
        $rows = $this->parseCsv($request);
        $activeSemester = Semester::current();

        $validator = new CsvValidationService($activeSemester);
        $validation = $validator->validateEnrollments($rows);

        if (!$validation['can_proceed']) return redirect()->back();

        $created = 0;
        $defaultType = EnrollmentType::whereName('Block-Enrolled')->first() ?? EnrollmentType::first();

        DB::transaction(function () use ($validation, $activeSemester, $defaultType, &$created) {
            foreach ($validation['valid_rows'] as $item) {
                $row = $item['row'];
                $student = User::where('user_id_number', trim($row['student_id_number']))->first();
                $subject = Subject::where('course_code', strtoupper(trim($row['subject_code'])))->first();
                
                $offering = CourseOffering::where([
                    'subject_id'   => $subject->id,
                    'semester_id'  => $activeSemester->id,
                    'group_number' => $row['group_number'],
                ])->first();

                Enrollment::create([
                    'offering_id'        => $offering->id,
                    'student_id'         => $student->id,
                    'enrollment_type_id' => $defaultType->id,
                ]);
                $created++;
            }
        });

        return $this->wizardRedirect(4, $created, $validation['skipped_count'], []);
    }

    private function validateCsvUpload(Request $request): void
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);
    }

    private function parseCsv(Request $request): array
    {
        $path = $request->file('csv_file')->getRealPath();
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');
            $headers = array_map(fn($h) => strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h))), $headers);
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) === count($headers)) {
                    $rows[] = array_combine($headers, array_map('trim', $data));
                }
            }
            fclose($handle);
        }
        return $rows;
    }

    private function wizardRedirect(int $step, int $created, int $skipped, array $errors): RedirectResponse
    {
        $nextStep = min($step + 1, 4);
        return redirect()->route('admin.semester-setup.index', ['step' => $nextStep])
                         ->with('success', "Step {$step} complete: {$created} created.");
    }

    private function getStepStats(Semester $semester): array
    {
        return [
            1 => User::whereHas('roles', fn ($q) => $q->where('name', 'student'))->count(),
            2 => Block::where('semester_id', $semester->id)->count(),
            3 => CourseOffering::where('semester_id', $semester->id)->count(),
            4 => Enrollment::whereHas('offering', fn ($q) => $q->where('semester_id', $semester->id))->count(),
        ];
    }
}