<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
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

    public function assignedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher', 'teacher_id');
    }

    public function studyLoads()
    {
        return $this->hasMany(StudyLoad::class, 'student_id');
    }

    public function currentSubjects()
    {
        return $this->belongsToMany(Subject::class, 'study_loads', 'student_id', 'subject_id')
                    ->withPivot('semester', 'academic_year')
                    ->wherePivot('semester', $this->currentSemester())
                    ->wherePivot('academic_year', $this->currentAcademicYear());
    }

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

    private function currentSemester()
    {
        // Implement logic to determine current semester
        return 'First'; // Example
    }

    private function currentAcademicYear()
    {
        // Implement logic to determine current academic year
        return date('Y'); // Example
    }
}