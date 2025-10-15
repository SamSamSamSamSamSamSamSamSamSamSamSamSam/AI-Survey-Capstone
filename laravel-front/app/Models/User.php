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

    // === Roles ===
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

    // === Subjects ===
    // Subjects this user teaches
    public function teachingSubjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher', 'teacher_id', 'subject_id')
                    ->withPivot('group');
    }

    // Subjects this user is enrolled in (as a student)
    public function enrolledSubjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_student', 'student_id', 'subject_id')
                    ->withPivot('group');
    }

    // === Surveys and Responses ===
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
