<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['course_code', 'name', 'description'];

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'subject_teacher', 'subject_id', 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'study_loads', 'subject_id', 'student_id')
                    ->withPivot('semester', 'academic_year');
    }

    public function studyLoads()
    {
        return $this->hasMany(StudyLoad::class);
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}