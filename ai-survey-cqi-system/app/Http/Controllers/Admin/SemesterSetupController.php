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
        1 => ['key' => 'students',   'label' => 'Register Students',  'icon' => '👤'],
        2 => ['key' => 'blocks',     'label' => 'Create Blocks',      'icon' => '🏫'],
        3 => ['key' => 'offerings',  'label' => 'Import Offerings',   'icon' => '📚'],
        4 => ['key' => 'enrollments','label' => 'Import Enrollments', 'icon' => '📋'],
    ];

    // -------------------------------------------------------------------------
    // Main wizard view
    // -------------------------------------------------------------------------

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
            'currentStep'    => $step,
            'stepStats'      => $this->getStepStats($activeSemester),
        ]);
    }

    // -------------------------------------------------------------------------
    // STEP 1 — Students
    // -------------------------------------------------------------------------

    public function importStudents(Request $request): RedirectResponse
    {
        $this->validateCsvUpload($request);

        $rows    = $this->parseCsv($request->file('csv_file'));
        $errors  = [];
        $created = 0;
        $skipped = 0;

        $studentRole = Role::whereName('student')->firstOrFail();

        DB::transaction(function () use ($rows, $studentRole, &$created, &$skipped, &$errors) {
            foreach ($rows as $i => $row) {
                $line = $i + 2;
                $v = Validator::make($row, [
                    'user_id_number' => ['required', 'string'],
                    'name'           => ['required', 'string'],
                    'email'          => ['required', 'email'],
                ]);

                if ($v->fails()) {
                    $errors[] = "Row {$line}: " . implode(', ', $v->errors()->all());
                    continue;
                }

                if (User::where('user_id_number', $row['user_id_number'])->exists()) {
                    $skipped++;
                    continue;
                }

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

        return $this->wizardRedirect(1, $created, $skipped, $errors);
    }

    // -------------------------------------------------------------------------
    // STEP 2 — Blocks
    // -------------------------------------------------------------------------

    public function importBlocks(Request $request): RedirectResponse
    {
        $this->validateCsvUpload($request);

        $activeSemester = Semester::current();
        $rows    = $this->parseCsv($request->file('csv_file'));
        $errors  = [];
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $activeSemester, &$created, &$skipped, &$errors) {
            foreach ($rows as $i => $row) {
                $line = $i + 2;
                $v = Validator::make($row, [
                    'block_name'   => ['required', 'string', 'max:50'],
                    'program_code' => ['required', 'string'],
                    'year_level'   => ['required', 'integer', 'min:1', 'max:5'],
                ]);

                if ($v->fails()) {
                    $errors[] = "Row {$line}: " . implode(', ', $v->errors()->all());
                    continue;
                }

                $program = Program::where('program_code', strtoupper(trim($row['program_code'])))->first();
                if (! $program) {
                    $errors[] = "Row {$line}: Program '{$row['program_code']}' not found.";
                    continue;
                }

                $exists = Block::where([
                    'program_id'  => $program->id,
                    'semester_id' => $activeSemester->id,
                    'name'        => strtoupper(trim($row['block_name'])),
                ])->exists();

                if ($exists) { $skipped++; continue; }

                Block::create([
                    'program_id'  => $program->id,
                    'semester_id' => $activeSemester->id,
                    'name'        => strtoupper(trim($row['block_name'])),
                    'year_level'  => (int) $row['year_level'],
                ]);
                $created++;
            }
        });

        return $this->wizardRedirect(2, $created, $skipped, $errors);
    }

    // -------------------------------------------------------------------------
    // STEP 3 — Course Offerings
    // -------------------------------------------------------------------------

    public function importOfferings(Request $request): RedirectResponse
    {
        $this->validateCsvUpload($request);

        $activeSemester = Semester::current();
        $rows    = $this->parseCsv($request->file('csv_file'));
        $errors  = [];
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $activeSemester, &$created, &$skipped, &$errors) {
            foreach ($rows as $i => $row) {
                $line = $i + 2;
                $v = Validator::make($row, [
                    'subject_code'      => ['required', 'string'],
                    'teacher_id_number' => ['required', 'string'],
                    'group_number'      => ['nullable'],
                    'block_name'        => ['nullable', 'string'],
                    'offering_type'     => ['nullable', 'string'],
                ]);

                if ($v->fails()) {
                    $errors[] = "Row {$line}: " . implode(', ', $v->errors()->all());
                    continue;
                }

                $subject = Subject::where('course_code', strtoupper(trim($row['subject_code'])))->first();
                if (! $subject) { $errors[] = "Row {$line}: Subject '{$row['subject_code']}' not found."; continue; }

                $teacher = User::where('user_id_number', trim($row['teacher_id_number']))->first();
                if (! $teacher) { $errors[] = "Row {$line}: Teacher '{$row['teacher_id_number']}' not found."; continue; }

                $blockId = null;
                if (! empty($row['block_name'])) {
                    $block = Block::where('name', strtoupper(trim($row['block_name'])))
                                  ->where('semester_id', $activeSemester->id)->first();
                    if (! $block) { $errors[] = "Row {$line}: Block '{$row['block_name']}' not found."; continue; }
                    $blockId = $block->id;
                }

                $offeringTypeId = null;
                if (! empty($row['offering_type'])) {
                    $offeringTypeId = OfferingType::where('name', 'like', trim($row['offering_type']))->first()?->id;
                }

                $groupNumber = isset($row['group_number']) && $row['group_number'] !== ''
                    ? (int) $row['group_number'] : null;

                $exists = CourseOffering::where([
                    'subject_id'   => $subject->id,
                    'semester_id'  => $activeSemester->id,
                    'group_number' => $groupNumber,
                ])->whereNull('deleted_at')->exists();

                if ($exists) { $skipped++; continue; }

                CourseOffering::create([
                    'subject_id'       => $subject->id,
                    'semester_id'      => $activeSemester->id,
                    'teacher_id'       => $teacher->id,
                    'block_id'         => $blockId,
                    'group_number'     => $groupNumber,
                    'offering_type_id' => $offeringTypeId,
                ]);
                $created++;
            }
        });

        return $this->wizardRedirect(3, $created, $skipped, $errors);
    }

    // -------------------------------------------------------------------------
    // STEP 4 — Enrollments
    // -------------------------------------------------------------------------

    public function importEnrollments(Request $request): RedirectResponse
    {
        $this->validateCsvUpload($request);

        $activeSemester = Semester::current();
        $rows    = $this->parseCsv($request->file('csv_file'));
        $errors  = [];
        $created = 0;
        $skipped = 0;

        $defaultType = EnrollmentType::whereName('Block-Enrolled')->first()
                    ?? EnrollmentType::first();

        DB::transaction(function () use ($rows, $activeSemester, $defaultType, &$created, &$skipped, &$errors) {
            foreach ($rows as $i => $row) {
                $line = $i + 2;
                $v = Validator::make($row, [
                    'student_id_number' => ['required', 'string'],
                    'subject_code'      => ['required', 'string'],
                    'group_number'      => ['nullable'],
                    'enrollment_type'   => ['nullable', 'string'],
                ]);

                if ($v->fails()) {
                    $errors[] = "Row {$line}: " . implode(', ', $v->errors()->all());
                    continue;
                }

                $student = User::where('user_id_number', trim($row['student_id_number']))->first();
                if (! $student) { $errors[] = "Row {$line}: Student '{$row['student_id_number']}' not found."; continue; }

                $subject = Subject::where('course_code', strtoupper(trim($row['subject_code'])))->first();
                if (! $subject) { $errors[] = "Row {$line}: Subject '{$row['subject_code']}' not found."; continue; }

                $groupNumber = isset($row['group_number']) && $row['group_number'] !== ''
                    ? (int) $row['group_number'] : null;

                $offering = CourseOffering::where([
                    'subject_id'   => $subject->id,
                    'semester_id'  => $activeSemester->id,
                    'group_number' => $groupNumber,
                ])->whereNull('deleted_at')->first();

                if (! $offering) {
                    $errors[] = "Row {$line}: No offering found for '{$row['subject_code']}' group {$groupNumber}.";
                    continue;
                }

                $enrollmentType = $defaultType;
                if (! empty($row['enrollment_type'])) {
                    $et = EnrollmentType::where('name', 'like', '%' . trim($row['enrollment_type']) . '%')->first();
                    if ($et) $enrollmentType = $et;
                }

                $exists = Enrollment::where([
                    'offering_id' => $offering->id,
                    'student_id'  => $student->id,
                ])->exists();

                if ($exists) { $skipped++; continue; }

                Enrollment::create([
                    'offering_id'        => $offering->id,
                    'student_id'         => $student->id,
                    'enrollment_type_id' => $enrollmentType->id,
                ]);
                $created++;
            }
        });

        return $this->wizardRedirect(4, $created, $skipped, $errors);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Validate the uploaded CSV file.
     * Detects PHP silent upload failures caused by php.ini limits.
     */
    private function validateCsvUpload(Request $request): void
    {
        // Detect PHP silent upload failure (post_max_size exceeded)
        if (empty($_FILES) && empty($_POST) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            abort(413, 'The uploaded file exceeds the server limit. Check post_max_size in php.ini.');
        }

        $request->validate([
            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:10240', // 10 MB in kilobytes
            ],
        ], [
            'csv_file.required' => 'Please select a CSV file.',
            'csv_file.mimes'    => 'The file must be a .csv file.',
            'csv_file.max'      => 'The file must not exceed 10 MB. For larger imports, split the file.',
        ]);
    }

    private function parseCsv(\Illuminate\Http\UploadedFile $file): array
    {
        $rows    = [];
        $handle  = fopen($file->getRealPath(), 'r');
        $headers = null;

        while (($data = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                // Normalize: lowercase, trim whitespace, strip UTF-8 BOM
                $headers = array_map(
                    fn ($h) => strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h))),
                    $data
                );
                continue;
            }
            // Only process rows with matching column count
            if (count($data) === count($headers)) {
                $rows[] = array_combine($headers, array_map('trim', $data));
            }
        }

        fclose($handle);
        return $rows;
    }

    private function wizardRedirect(int $step, int $created, int $skipped, array $errors): RedirectResponse
    {
        $nextStep = min($step + 1, 4);
        $message  = "Step {$step} complete — {$created} created, {$skipped} skipped.";

        if (! empty($errors)) {
            session()->flash('import_errors', array_slice($errors, 0, 50)); // cap at 50 shown
        }

        return redirect()->route('admin.semester-setup.index', ['step' => $nextStep])
                         ->with('success', $message);
    }

    private function getStepStats(Semester $semester): array
    {
        return [
            1 => User::whereHas('roles', fn ($q) => $q->where('name', 'student'))->count(),
            2 => Block::where('semester_id', $semester->id)->whereNull('deleted_at')->count(),
            3 => CourseOffering::where('semester_id', $semester->id)->whereNull('deleted_at')->count(),
            4 => Enrollment::whereHas('offering', fn ($q) => $q->where('semester_id', $semester->id))->count(),
        ];
    }
}
