<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'academic_year', 'semester_number', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    public function cqiReports()
    {
        return $this->hasMany(CQIReport::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    /**
     * Get the currently active semester, or null if none is set.
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Returns a human-readable label e.g. "1st Semester 2024-2025"
     */
    public function getLabelAttribute(): string
    {
        $suffix = $this->semester_number === 1 ? '1st' : '2nd';
        return "{$suffix} Semester {$this->academic_year}";
    }
}