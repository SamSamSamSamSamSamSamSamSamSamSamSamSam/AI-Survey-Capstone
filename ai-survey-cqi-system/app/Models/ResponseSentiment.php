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
    ];

    protected $casts = [
        'sentiment_score' => 'float',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (ResponseSentiment $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::ulid();
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
