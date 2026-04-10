<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentimentType extends Model
{
    protected $fillable = ['label'];

    public function sentiments()
    {
        return $this->hasMany(ResponseSentiment::class);
    }
}
