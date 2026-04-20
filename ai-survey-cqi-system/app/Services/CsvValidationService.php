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
    protected array $errors = [];
    protected array $warnings = [];
    protected array $validRows = [];
    protected int $skippedCount = 0;

    public function __construct(Semester $semester)
    {
        $this->semester = $semester;
    }

    public function validateStudents(array $rows): array
    {
        $this->reset();
        foreach ($rows as $i => $row) {
            $line = $i + 2;
            $v = Validator::make($row, [
                'user_id_number' => 'required',
                'name'           => 'required',
                'email'          => 'required|email'
            ]);

            if ($v->fails()) {
                $this->errors[] = ['line' => $line, 'message' => implode(', ', $v->errors()->all())];
                continue;
            }

            // Check if the student exists in the database
            $user = User::where('user_id_number', $row['user_id_number'])->first();

            if (!$user) {
                // If student doesn't exist, it's a hard error. 
                // Admin must add them via User Management first.
                $this->errors[] = [
                    'line' => $line, 
                    'message' => "Student ID {$row['user_id_number']} not found. Please register this student in User Management first."
                ];
                continue;
            }

            // If they exist, they are valid for this operation
            $this->validRows[] = ['row' => $row, 'line' => $line];
        }
        return $this->report();
    }

    public function validateBlocks(array $rows): array
    {
        $this->reset();
        foreach ($rows as $i => $row) {
            $line = $i + 2;
            
            $v = Validator::make($row, [
                'block_name'   => 'required',
                'program_code' => 'required',
                'year_level'   => 'required|integer'
            ]);

            if ($v->fails()) {
                $this->errors[] = ['line' => $line, 'message' => "Invalid format at line $line."];
                continue;
            }

            $program = Program::where('program_code', strtoupper(trim($row['program_code'])))->first();
            if (!$program) {
                $this->errors[] = ['line' => $line, 'message' => "Program '{$row['program_code']}' not found."];
                continue;
            }

            // Exact match check
            $exists = Block::where([
                'program_id'  => $program->id,
                'semester_id' => $this->semester->id,
                'name'        => strtoupper(trim($row['block_name']))
            ])->exists();

            if ($exists) {
                $this->skippedCount++;
                // We still consider this a "valid row" for the loop if you want to update it,
                // or omit it from valid_rows if you truly want to skip it.
                continue; 
            }

            $this->validRows[] = ['row' => $row, 'line' => $line];
        }
        return $this->report();
    }

    public function validateOfferings(array $rows): array
    {
        $this->reset();
        foreach ($rows as $i => $row) {
            $line = $i + 2;
            $subject = Subject::where('course_code', strtoupper($row['subject_code'] ?? ''))->first();
            $teacher = User::where('user_id_number', $row['teacher_id_number'] ?? '')->first();

            if (!$subject || !$teacher) {
                $this->errors[] = ['line' => $line, 'message' => "Subject or Teacher not found."];
                continue;
            }

            $this->validRows[] = ['row' => $row, 'line' => $line];
        }
        return $this->report();
    }

    public function validateEnrollments(array $rows): array
    {
        $this->reset();
        foreach ($rows as $i => $row) {
            $line = $i + 2;
            $student = User::where('user_id_number', $row['student_id_number'] ?? '')->first();
            $subject = Subject::where('course_code', strtoupper($row['subject_code'] ?? ''))->first();

            if (!$student || !$subject) {
                $this->errors[] = ['line' => $line, 'message' => "Student or Subject reference missing."];
                continue;
            }

            $offering = CourseOffering::where(['subject_id' => $subject->id, 'semester_id' => $this->semester->id])->first();
            if (!$offering) {
                $this->errors[] = ['line' => $line, 'message' => "No offering found for {$row['subject_code']}."];
                continue;
            }

            $this->validRows[] = ['row' => $row, 'line' => $line];
        }
        return $this->report();
    }

    protected function reset()
    {
        $this->errors = []; $this->warnings = []; $this->validRows = []; $this->skippedCount = 0;
    }

    protected function report(): array
    {
        return [
            'can_proceed' => empty($this->errors),
            'valid_count' => count($this->validRows),
            'skipped_count' => $this->skippedCount,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'valid_rows' => $this->validRows
        ];
    }
}