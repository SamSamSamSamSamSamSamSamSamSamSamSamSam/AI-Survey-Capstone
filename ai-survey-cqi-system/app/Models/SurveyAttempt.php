<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SurveyAttempt extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'survey_id',
        'student_id',
        'submitted_at',
        'notify_email',
        'notify_dashboard',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'notify_email'     => 'boolean',
        'notify_dashboard' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (SurveyAttempt $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::ulid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function survey()
    {
        return $this->belongsTo(Survey::class)->withTrashed();
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function respondent()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function responses()
    {
        return $this->hasMany(Response::class, 'attempt_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function submit(): void
    {
        $this->update(['submitted_at' => now()]);
    }
}
