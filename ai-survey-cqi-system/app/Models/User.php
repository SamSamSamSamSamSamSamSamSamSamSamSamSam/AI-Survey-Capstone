<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        return !!$role->intersect($this->roles)->count();
    }

    // ── Subjects (all time, no semester scope) ─────────────────────────────────

    public function teachingSubjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher', 'teacher_id', 'subject_id')
                    ->withPivot('group', 'semester_id');
    }

    public function enrolledSubjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_student', 'student_id', 'subject_id')
                    ->withPivot('group', 'semester_id');
    }

    // ── Semester-scoped subject helpers ────────────────────────────────────────

    /**
     * Subjects this teacher is assigned to for a specific semester.
     */
    public function teachingSubjectsForSemester($semesterId)
    {
        return $this->teachingSubjects()
                    ->wherePivot('semester_id', $semesterId);
    }

    /**
     * Subjects this student is enrolled in for a specific semester.
     */
    public function enrolledSubjectsForSemester($semesterId)
    {
        return $this->enrolledSubjects()
                    ->wherePivot('semester_id', $semesterId);
    }

    /**
     * Check if the user has subjects enrolled/assigned for the given semester.
     */
    public function hasSubjectsForSemester($semesterId): bool
    {
        if ($this->hasRole('student')) {
            return $this->enrolledSubjects()
                        ->wherePivot('semester_id', $semesterId)
                        ->exists();
        }

        if ($this->hasRole('teacher')) {
            return $this->teachingSubjects()
                        ->wherePivot('semester_id', $semesterId)
                        ->exists();
        }

        return true; // admins always pass
    }

    // ── Surveys and Responses ──────────────────────────────────────────────────

    public function createdSurveys()
    {
        return $this->hasMany(Survey::class, 'created_by');
    }

    public function evaluationsGiven()
    {
        return $this->hasMany(Response::class, 'evaluator_id');
    }

    public function evaluationsReceived()
    {
        return $this->hasMany(Response::class, 'evaluatee_id');
    }

    public function generatedReports()
    {
        return $this->hasMany(CQIReport::class, 'generated_by');
    }
}