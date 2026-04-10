<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospectus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_id',
        'subject_id',
        'year_level',
        'semester_number',
        'offered_type_id',
    ];

    protected $casts = [
        'year_level'      => 'integer',
        'semester_number' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * The offering type for this prospectus entry (e.g. lecture, lab).
     * Nullable — nullOnDelete() in migration.
     */
    public function offeringType()
    {
        return $this->belongsTo(OfferingType::class, 'offered_type_id');
    }
}