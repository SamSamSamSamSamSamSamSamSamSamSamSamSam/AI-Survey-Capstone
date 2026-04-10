<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyQuestion extends Model
{
    use SoftDeletes;

    protected $table = 'survey_questions';

    protected $fillable = [
        'survey_id',
        'question_text',
        'question_type',
        'category_id',
        'scale_id',
        'order_number',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function category()
    {
        return $this->belongsTo(QuestionCategory::class, 'category_id');
    }

    public function scale()
    {
        return $this->belongsTo(Scale::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class, 'survey_question_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isRating(): bool { return $this->question_type === 'rating'; }
    public function isText(): bool   { return $this->question_type === 'text'; }
}
