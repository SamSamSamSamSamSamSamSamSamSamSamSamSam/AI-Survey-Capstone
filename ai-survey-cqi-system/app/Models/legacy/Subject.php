<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_code',
        'name',
        'description',
        'units',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * Prospectus entries that include this subject.
     */
    public function prospectuses()
    {
        return $this->hasMany(Prospectus::class);
    }

    /**
     * Programs this subject appears in (through prospectus).
     */
    public function programs()
    {
        return $this->hasManyThrough(
            Program::class,
            Prospectus::class,
            'subject_id',  // FK on prospectuses
            'id',          // FK on programs
            'id',          // local key on subjects
            'program_id'   // local key on prospectuses
        );
    }

    /**
     * All course offerings (sections) for this subject.
     */
    public function courseOfferings()
    {
        return $this->hasMany(CourseOffering::class);
    }
}