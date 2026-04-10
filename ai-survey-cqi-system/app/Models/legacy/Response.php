<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'rating_value',
        'text_response',
    ];

    protected $casts = [
        'rating_value' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * The survey attempt this response belongs to.
     */
    public function attempt()
    {
        return $this->belongsTo(SurveyAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Sentiment analysis results for this response.
     */
    public function sentiments()
    {
        return $this->hasMany(ResponseSentiment::class);
    }
}