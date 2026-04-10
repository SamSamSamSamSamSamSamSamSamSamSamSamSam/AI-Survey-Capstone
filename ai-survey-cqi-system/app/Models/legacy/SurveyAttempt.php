<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyAttempt extends Model
{
    use HasFactory, HasUlids;

    // Composite unique: ['survey_id', 'student_id'] — enforced at DB level.
    // One student can submit one attempt per survey.

    public $timestamps = false; // Only submitted_at is tracked; no created_at/updated_at columns.

    protected $fillable = [
        'survey_id',
        'student_id',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Individual question responses within this attempt.
     */
    public function responses()
    {
        return $this->hasMany(Response::class, 'attempt_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Whether this attempt has been submitted.
     */
    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }
}