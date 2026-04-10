<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'survey_id',
        'question_text',
        'category',
        'type',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * All responses submitted for this question.
     */
    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}