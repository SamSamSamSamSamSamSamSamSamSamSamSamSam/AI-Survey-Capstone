<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'academic_start_year',
        'semester_number',
        'is_active',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'academic_start_year' => 'integer',
        'semester_number'     => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('academic_start_year', $year);
    }

    // -------------------------------------------------------------------------
    // Helpers / Service methods
    // -------------------------------------------------------------------------

    /**
     * Activate this semester and automatically deactivate all others.
     * Only one semester can be active at a time.
     */
    public function activate(): void
    {
        static::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate this semester (no active semester state afterward).
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Get the currently active semester, or null if none is set.
     */
    public static function current(): ?self
    {
        return static::active()->first();
    }

    /**
     * Auto-generate semesters for a given school year.
     *
     * Skips any semester_number that already exists for the year to prevent duplicates.
     *
     * @param  int   $startYear     e.g. 2026
     * @param  bool  $includeSummer Whether to also create Summer (semester_number = 3)
     * @return \Illuminate\Support\Collection  Collection of created Semester models
     */
    public static function generateForYear(int $startYear, bool $includeSummer = false): \Illuminate\Support\Collection
    {
        $endYear    = $startYear + 1;
        $ayLabel    = "S.Y. {$startYear}–{$endYear}";
        $toCreate   = [
            1 => "1st Semester {$ayLabel}",
            2 => "2nd Semester {$ayLabel}",
        ];

        if ($includeSummer) {
            $toCreate[3] = "Summer {$ayLabel}";
        }

        $existing = static::withTrashed()
            ->forYear($startYear)
            ->pluck('semester_number')
            ->all();

        $created = collect();

        foreach ($toCreate as $number => $name) {
            if (in_array($number, $existing, true)) {
                continue; // skip duplicates (even soft-deleted)
            }

            $created->push(static::create([
                'name'                => $name,
                'academic_start_year' => $startYear,
                'semester_number'     => $number,
                'is_active'           => false,
            ]));
        }

        return $created;
    }

    /**
     * Check whether a given school year already has semesters (including soft-deleted).
     */
    public static function schoolYearExists(int $startYear): bool
    {
        return static::withTrashed()->forYear($startYear)->exists();
    }

    /**
     * Return all distinct academic_start_year values (desc), each bundled with
     * its semesters — useful for the grouped accordion UI.
     */
    public static function groupedByYear(): \Illuminate\Support\Collection
    {
        return static::orderByDesc('academic_start_year')
            ->orderBy('semester_number')
            ->get()
            ->groupBy('academic_start_year');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getFullLabelAttribute(): string
    {
        $sem = match ($this->semester_number) {
            1       => '1st Semester',
            2       => '2nd Semester',
            3       => 'Summer',
            default => "Semester {$this->semester_number}",
        };

        return "{$sem} S.Y. {$this->academic_start_year}–" . ($this->academic_start_year + 1);
    }

    public function getAcademicYearLabelAttribute(): string
    {
        return "S.Y. {$this->academic_start_year}–" . ($this->academic_start_year + 1);
    }

    public function getSemesterLabelAttribute(): string
    {
        return match ($this->semester_number) {
            1       => '1st Semester',
            2       => '2nd Semester',
            3       => 'Summer',
            default => "Semester {$this->semester_number}",
        };
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function offerings()
    {
        return $this->hasMany(CourseOffering::class);
    }
}