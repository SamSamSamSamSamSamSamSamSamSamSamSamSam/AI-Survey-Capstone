<?php

namespace App\Services;

use App\Models\Block;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\EnrollmentType;
use App\Models\OfferingType;
use App\Models\Program;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class CsvValidationService
{
    protected Semester $semester;
    protected array $errors   = [];
    protected array $warnings = [];
    protected array $validRows = [];
    protected int $skippedCount = 0;

    public function __construct(Semester $semester)
    {
        $this->semester = $semester;
    }

    // -------------------------------------------------------------------------
    // Students
    // -------------------------------------------------------------------------

    public function validateStudents(array $rows): array
    {
        $this->reset();

        foreach ($rows as $i => $row) {
            $line = $i + 2;

            $v = Validator::make($row, [
                'user_id_number' => 'required',
                'name'           => 'required',
                'email'          => 'required|email',
            ]);

            if ($v->fails()) {
                $this->errors[] = [
                    'line'    => $line,
                    'message' => implode(', ', $v->errors()->all()),
                ];
                continue;
            }

            $user = User::where('user_id_number', $row['user_id_number'])->first();

            if (! $user) {
                $this->errors[] = [
                    'line'    => $line,
                    'message' => "Student ID {$row['user_id_number']} not found. Register this student in User Management first.",
                ];
                continue;
            }

            $this->validRows[] = ['row' => $row, 'line' => $line];
        }

        return $this->report();
    }

    // -------------------------------------------------------------------------
    // Blocks
    // -------------------------------------------------------------------------

    public function validateBlocks(array $rows): array
    {
        $this->reset();

        foreach ($rows as $i => $row) {
            $line = $i + 2;

            $v = Validator::make($row, [
                'block_name'   => 'required',
                'program_code' => 'required',
                'year_level'   => 'required|integer',
            ]);

            if ($v->fails()) {
                $this->errors[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: " . implode(', ', $v->errors()->all()),
                ];
                continue;
            }

            $program = Program::where('program_code', strtoupper(trim($row['program_code'])))->first();

            if (! $program) {
                $this->errors[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: Program '{$row['program_code']}' not found.",
                ];
                continue;
            }

            // ── Duplicate check → warning, not error ─────────────────────────
            $exists = Block::where([
                'program_id'  => $program->id,
                'semester_id' => $this->semester->id,
                'name'        => strtoupper(trim($row['block_name'])),
            ])->exists();

            if ($exists) {
                $this->skippedCount++;
                $this->warnings[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: Block '{$row['block_name']}' for program '{$row['program_code']}' already exists — skipped.",
                ];
                continue;
            }

            $this->validRows[] = ['row' => $row, 'line' => $line];
        }

        return $this->report();
    }

    // -------------------------------------------------------------------------
    // Course Offerings
    // -------------------------------------------------------------------------

    public function validateOfferings(array $rows): array
    {
        $this->reset();

        foreach ($rows as $i => $row) {
            $line = $i + 2;

            // Required field check
            $v = Validator::make($row, [
                'subject_code'      => 'required',
                'teacher_id_number' => 'required',
            ]);

            if ($v->fails()) {
                $this->errors[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: " . implode(', ', $v->errors()->all()),
                ];
                continue;
            }

            $subject = Subject::where('course_code', strtoupper(trim($row['subject_code'])))->first();
            $teacher = User::where('user_id_number', trim($row['teacher_id_number']))->first();

            if (! $subject) {
                $this->errors[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: Subject '{$row['subject_code']}' not found.",
                ];
                continue;
            }

            if (! $teacher) {
                $this->errors[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: Teacher with ID '{$row['teacher_id_number']}' not found.",
                ];
                continue;
            }

            // ── Duplicate check → warning, not error ─────────────────────────
            $groupNumber = isset($row['group_number']) && $row['group_number'] !== ''
                ? (int) $row['group_number']
                : null;

            $exists = CourseOffering::where([
                'subject_id'   => $subject->id,
                'semester_id'  => $this->semester->id,
                'group_number' => $groupNumber,
            ])->exists();

            if ($exists) {
                $this->skippedCount++;
                $this->warnings[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: Offering for '{$row['subject_code']}' (Group: " . ($groupNumber ?? 'N/A') . ") already exists — skipped.",
                ];
                continue;
            }

            $this->validRows[] = ['row' => $row, 'line' => $line];
        }

        return $this->report();
    }

    // -------------------------------------------------------------------------
    // Enrollments
    // -------------------------------------------------------------------------

    public function validateEnrollments(array $rows): array
    {
        $this->reset();

        foreach ($rows as $i => $row) {
            $line        = $i + 2;
            $groupNumber = isset($row['group_number']) && $row['group_number'] !== ''
                ? (int) $row['group_number']
                : null;

            // Required field check
            $v = Validator::make($row, [
                'student_id_number' => 'required',
                'subject_code'      => 'required',
            ]);

            if ($v->fails()) {
                $this->errors[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: " . implode(', ', $v->errors()->all()),
                ];
                continue;
            }

            $student = User::where('user_id_number', trim($row['student_id_number']))->first();
            $subject = Subject::where('course_code', strtoupper(trim($row['subject_code'])))->first();

            if (! $student) {
                $this->errors[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: Student ID '{$row['student_id_number']}' not found.",
                ];
                continue;
            }

            if (! $subject) {
                $this->errors[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: Subject '{$row['subject_code']}' not found.",
                ];
                continue;
            }

            $offering = CourseOffering::where([
                'subject_id'   => $subject->id,
                'semester_id'  => $this->semester->id,
                'group_number' => $groupNumber,
            ])->first();

            if (! $offering) {
                $this->errors[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: No offering found for '{$row['subject_code']}' (Group: " . ($groupNumber ?? 'N/A') . ").",
                ];
                continue;
            }

            // ── Duplicate check → warning, not error ─────────────────────────
            $alreadyEnrolled = Enrollment::where([
                'offering_id' => $offering->id,
                'student_id'  => $student->id,
            ])->exists();

            if ($alreadyEnrolled) {
                $this->skippedCount++;
                $this->warnings[] = [
                    'line'    => $line,
                    'message' => "Line {$line}: Student '{$row['student_id_number']}' is already enrolled in '{$row['subject_code']}' — skipped.",
                ];
                continue;
            }

            $this->validRows[] = ['row' => $row, 'line' => $line];
        }

        return $this->report();
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    protected function reset(): void
    {
        $this->errors      = [];
        $this->warnings    = [];
        $this->validRows   = [];
        $this->skippedCount = 0;
    }

    protected function report(): array
    {
        return [
            'can_proceed'   => empty($this->errors),   // only hard errors block import
            'valid_count'   => count($this->validRows),
            'skipped_count' => $this->skippedCount,
            'errors'        => $this->errors,           // hard errors (missing refs, bad format)
            'warnings'      => $this->warnings,         // soft warnings (duplicates skipped)
            'valid_rows'    => $this->validRows,
        ];
    }
}