<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_official',
        'is_active',
    ];

    protected $casts = [
        'is_official' => 'boolean',
        'is_active'   => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function questions()
    {
        return $this->hasMany(SurveyTemplateQuestion::class)->orderBy('order_number');
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class, 'template_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Copy all template questions into a given survey as survey_questions.
     */
    public function copyQuestionsTo(Survey $survey): void
    {
        foreach ($this->questions as $tq) {
            $survey->questions()->create([
                'question_text' => $tq->question_text,
                'question_type' => $tq->question_type,
                'category_id'   => $tq->category_id,
                'scale_id'      => $tq->scale_id,
                'order_number'  => $tq->order_number,
            ]);
        }
    }
}
