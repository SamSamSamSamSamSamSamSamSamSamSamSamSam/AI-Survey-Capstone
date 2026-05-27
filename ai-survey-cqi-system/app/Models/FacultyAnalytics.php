<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacultyAnalytics extends Model
{
    use SoftDeletes;
    public $incrementing = false;
    protected $keyType   = 'string';

    // Fillable fields for mass assignment
    protected $fillable = [
        'survey_id',
        'offering_id',
        'faculty_id',
        'avg_rating',
        'response_count',
        'positive_sentiment_percent',
        'neutral_sentiment_percent',
        'negative_sentiment_percent',
        'category_scores',
        'top_keywords',
        'last_computed_at',
    ];

    protected $casts = [
        'avg_rating'                  => 'float',
        'positive_sentiment_percent'  => 'float',
        'neutral_sentiment_percent'   => 'float',
        'negative_sentiment_percent'  => 'float',
        'category_scores'             => 'array',
        'top_keywords'                => 'array',
        'last_computed_at'            => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->{$m->getKeyName()})) {
                $m->{$m->getKeyName()} = (string) \Illuminate\Support\Str::ulid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function survey()   { return $this->belongsTo(Survey::class, 'survey_id'); }
    public function offering() { return $this->belongsTo(CourseOffering::class, 'offering_id'); }
    public function faculty()  { return $this->belongsTo(User::class, 'faculty_id'); }

        // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForFaculty($query, string $facultyId)
    {
        return $query->where('faculty_id', $facultyId);
    }

    public function scopeForSemester($query, int $semesterId)
    {
        return $query->whereHas('survey.offering', fn ($q) =>
            $q->where('semester_id', $semesterId)
        );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function getInterpretationAttribute(): string
    {
        $pct = $this->avg_rating ? ($this->avg_rating / 5) * 100 : 0;

        /** @var \App\Services\CategoryWeightService $svc */
        $svc = app(\App\Services\CategoryWeightService::class);

        return $svc->interpret($pct)['label'];
    }

    public function getPassesThresholdAttribute(): bool
    {
        $threshold = (float) setting('survey.passing_threshold', 3.0);
        return ($this->avg_rating ?? 0) >= $threshold;
    }

}