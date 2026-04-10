<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'academic_start_year',
        'semester_number',
        'is_active',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'academic_start_year'=> 'integer',
        'semester_number'    => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * All course offerings scheduled in this semester.
     */
    public function courseOfferings()
    {
        return $this->hasMany(CourseOffering::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Get the currently active semester, or null if none is set.
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Human-readable label e.g. "1st Semester 2024-2025"
     */
    public function getLabelAttribute(): string
    {
        $suffixes = [1 => '1st', 2 => '2nd', 3 => '3rd'];
        $suffix   = $suffixes[$this->semester_number] ?? "{$this->semester_number}th";
        $endYear  = $this->academic_start_year + 1;

        return "{$suffix} Semester {$this->academic_start_year}-{$endYear}";
    }
}