<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseSentiment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'response_id',
        'sentiment_type_id',
        'sentiment_score',
        'model_name',
        'model_version',
    ];

    protected $casts = [
        'sentiment_score' => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function response()
    {
        return $this->belongsTo(Response::class);
    }

    public function sentimentType()
    {
        return $this->belongsTo(SentimentType::class);
    }
}