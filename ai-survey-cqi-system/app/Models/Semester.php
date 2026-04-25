<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $fillable = [
        'name',
        'academic_start_year',
        'semester_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Activate this semester and deactivate all others.
     * Only one semester can be active at a time.
     */
    public function activate(): void
    {
        static::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Get the currently active semester, or null if none.
     */
    public static function current(): ?self
    {
        return static::active()->first();
    }

    public function getFullLabelAttribute(): string
    {
        $sem = match ((int) $this->semester_number) {
            1 => '1st Semester',
            2 => '2nd Semester',
            3 => 'Summer',
            default => "Semester {$this->semester_number}",
        };

        // return "{$sem}";
        return "{$sem} S.Y. {$this->academic_start_year}–" . ($this->academic_start_year + 1);  
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function offerings()
    {
        return $this->hasMany(CourseOffering::class);
    }
}
