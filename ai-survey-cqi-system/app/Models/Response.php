<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Response extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'attempt_id',
        'survey_question_id',
        'scale_value',
        'text_response',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Response $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::ulid();
            }
        });
    }

    public function attempt()
    {
        return $this->belongsTo(SurveyAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }

    public function sentiment()
    {
        return $this->hasOne(ResponseSentiment::class, 'response_id');
    }
}
