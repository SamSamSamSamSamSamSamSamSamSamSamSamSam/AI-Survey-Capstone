<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospectus extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // 'program_id', // removed since we can get it through curriculum
        'curriculum_id',   // NEW — belongs to a specific curriculum
        'subject_id',
        'year_level',
        'semester_number',
        // 'offered_type_id' REMOVED
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    // public function program()
    // {
    //     return $this->belongsTo(Program::class);
    // }

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
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

    public function getSemesterLabelAttribute(): string
    {
        return match ((int) $this->semester_number) {
            1 => '1st Semester',
            2 => '2nd Semester',
            3 => 'Summer',
            default => "Semester {$this->semester_number}",
        };
    }
}
