<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentimentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * All sentiment records of this type.
     */
    public function responseSentiments()
    {
        return $this->hasMany(ResponseSentiment::class);
    }
}