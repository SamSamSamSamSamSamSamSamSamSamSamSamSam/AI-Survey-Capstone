<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUlids, SoftDeletes;

    protected $fillable = [
        'user_id_number',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // ── Roles ──────────────────────────────────────────────────────────────────

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Check if the user has a given role by name.
     * Accepts a string role name or a collection of roles.
     * Used throughout blade views (e.g. sidebar nav partials).
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return (bool) $role->intersect($this->roles)->count();
    }

    // ── Teaching (via CourseOffering) ──────────────────────────────────────────

    /**
     * All course offerings this teacher is assigned to.
     */
    public function courseOfferings()
    {
        return $this->hasMany(CourseOffering::class, 'teacher_id');
    }

    /**
     * Course offerings scoped to a specific semester.
     */
    public function courseOfferingsForSemester($semesterId)
    {
        return $this->courseOfferings()->where('semester_id', $semesterId);
    }

    // ── Enrollment (via Enrollment) ────────────────────────────────────────────

    /**
     * All enrollments for this student.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    /**
     * Course offerings this student is enrolled in (through enrollments).
     */
    public function enrolledOfferings()
    {
        return $this->hasManyThrough(
            CourseOffering::class,
            Enrollment::class,
            'student_id',   // FK on enrollments
            'id',           // FK on course_offerings
            'id',           // local key on users
            'offering_id'   // local key on enrollments
        );
    }

    // ── Survey & Response ──────────────────────────────────────────────────────

    public function createdSurveys()
    {
        return $this->hasMany(Survey::class, 'created_by');
    }

    /**
     * Survey attempts submitted by this student.
     */
    public function surveyAttempts()
    {
        return $this->hasMany(SurveyAttempt::class, 'student_id');
    }

    // ── CQI Reports ────────────────────────────────────────────────────────────

    public function generatedReports()
    {
        return $this->hasMany(CqiReport::class, 'generated_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Check if the user (student) has any enrollments in the given semester.
     * Teachers are checked via their course offerings.
     * Admins always pass.
     */
    public function hasSubjectsForSemester($semesterId): bool
    {
        if ($this->hasRole('student')) {
            return $this->enrolledOfferings()
                        ->where('semester_id', $semesterId)
                        ->exists();
        }

        if ($this->hasRole('teacher')) {
            return $this->courseOfferings()
                        ->where('semester_id', $semesterId)
                        ->exists();
        }

        return true; // admins always pass
    }
}