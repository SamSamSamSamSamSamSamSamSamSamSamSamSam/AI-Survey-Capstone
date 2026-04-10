<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionCategory extends Model
{
    protected $fillable = ['name', 'description'];

    public function surveyQuestions()
    {
        return $this->hasMany(SurveyQuestion::class, 'category_id');
    }

    public function templateQuestions()
    {
        return $this->hasMany(SurveyTemplateQuestion::class, 'category_id');
    }
}
