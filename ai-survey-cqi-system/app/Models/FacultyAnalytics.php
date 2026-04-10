<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyAnalytics extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

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

    public function survey()   { return $this->belongsTo(Survey::class, 'survey_id'); }
    public function offering() { return $this->belongsTo(CourseOffering::class, 'offering_id'); }
    public function faculty()  { return $this->belongsTo(User::class, 'faculty_id'); }
}
