<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scale extends Model
{
    protected $fillable = ['name', 'min_value', 'max_value'];

    public function options()
    {
        return $this->hasMany(ScaleOption::class)->orderBy('order_number');
    }

    public function surveyQuestions()
    {
        return $this->hasMany(SurveyQuestion::class);
    }

    public function templateQuestions()
    {
        return $this->hasMany(SurveyTemplateQuestion::class);
    }

    /**
     * Returns options as a keyed array: [value => label]
     */
    public function optionsMap(): array
    {
        return $this->options->pluck('label', 'value')->toArray();
    }
}
