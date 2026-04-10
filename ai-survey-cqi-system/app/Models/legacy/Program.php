<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_code',
        'name',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * Prospectus entries belonging to this program.
     */
    public function prospectuses()
    {
        return $this->hasMany(Prospectus::class);
    }

    /**
     * Subjects offered under this program (through prospectus).
     */
    public function subjects()
    {
        return $this->hasManyThrough(
            Subject::class,
            Prospectus::class,
            'program_id',  // FK on prospectuses
            'id',          // FK on subjects
            'id',          // local key on programs
            'subject_id'   // local key on prospectuses
        );
    }
}