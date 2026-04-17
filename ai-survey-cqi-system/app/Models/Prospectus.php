<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospectus extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'curriculum_id',
        'subject_id',
        'year_level',
        'semester_id',      // ← was semester_number
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForCurriculum($query, int $curriculumId)
    {
        return $query->where('curriculum_id', $curriculumId);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function getYearLevelLabelAttribute(): string
    {
        return match ((int) $this->year_level) {
            1 => '1st Year',
            2 => '2nd Year',
            3 => '3rd Year',
            4 => '4th Year',
            5 => '5th Year',
            default => "Year {$this->year_level}",
        };
    }

    /**
     * Semester label — derived from the related Semester record.
     * Falls back gracefully if relation not loaded.
     */
    public function getSemesterLabelAttribute(): string
    {
        if ($this->relationLoaded('semester') && $this->semester) {
            return $this->semester->full_label;
        }

        return "Semester {$this->semester_id}";
    }

    /**
     * Short label for grouping — e.g. "1st Semester" without the A.Y.
     * Uses the semester_number field on the related Semester.
     */
    public function getSemesterShortLabelAttribute(): string
    {
        if ($this->relationLoaded('semester') && $this->semester) {
            return match ((int) $this->semester->semester_number) {
                1 => '1st Semester',
                2 => '2nd Semester',
                3 => 'Summer',
                default => $this->semester->name,
            };
        }

        return "Semester";
    }
}
