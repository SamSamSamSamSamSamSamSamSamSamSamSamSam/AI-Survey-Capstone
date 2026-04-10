<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyAnalytic extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'offering_id',
        'survey_id',
        'avg_rating',
        'response_count',
        'positive_sentiment_percent',
        'neutral_sentiment_percent',
        'negative_sentiment_percent',
        'top_keywords',
        'category_scores',
        'last_computed_at',
    ];

    protected $casts = [
        'avg_rating'                  => 'decimal:2',
        'response_count'              => 'integer',
        'positive_sentiment_percent'  => 'decimal:2',
        'neutral_sentiment_percent'   => 'decimal:2',
        'negative_sentiment_percent'  => 'decimal:2',
        'top_keywords'                => 'array',
        'category_scores'             => 'array',
        'last_computed_at'            => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function offering()
    {
        return $this->belongsTo(CourseOffering::class, 'offering_id');
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}