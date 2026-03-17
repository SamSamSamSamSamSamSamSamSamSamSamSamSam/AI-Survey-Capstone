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
        return $this->belongsToMany(User::class, 'subject_teacher', 'subject_id', 'teacher_id')
                    ->withPivot('group');
    }


    public function students()
    {
        return $this->belongsToMany(User::class, 'subject_student', 'subject_id', 'student_id')
                    ->withPivot('group');
    }


    public function surveys()
    {
        return $this->hasMany(Survey::class, 'subject_id');
    }

 
    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
