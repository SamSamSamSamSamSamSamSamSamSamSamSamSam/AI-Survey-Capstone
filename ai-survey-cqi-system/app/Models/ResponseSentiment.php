<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ResponseSentiment extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'response_id',
        'sentiment_type_id',
        'sentiment_score',
        'model_name',
        'model_version',
        'processing_time_ms',
        'processed_at',
    ];

    protected $casts = [
        'sentiment_score'    => 'float',
        'processing_time_ms' => 'integer',
        'processed_at'       => 'datetime',
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

    public function response()
    {
        return $this->belongsTo(Response::class, 'response_id');
    }

    public function sentimentType()
    {
        return $this->belongsTo(SentimentType::class);
    }
}