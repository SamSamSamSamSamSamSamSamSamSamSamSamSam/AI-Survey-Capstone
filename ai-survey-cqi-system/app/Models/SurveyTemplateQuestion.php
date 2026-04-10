<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyTemplateQuestion extends Model
{
    protected $fillable = [
        'survey_template_id',
        'question_text',
        'question_type',
        'category_id',
        'scale_id',
        'order_number',
    ];

    public function template()
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    public function category()
    {
        return $this->belongsTo(QuestionCategory::class, 'category_id');
    }

    public function scale()
    {
        return $this->belongsTo(Scale::class);
    }

    public function isRating(): bool { return $this->question_type === 'rating'; }
    public function isText(): bool   { return $this->question_type === 'text'; }
}
