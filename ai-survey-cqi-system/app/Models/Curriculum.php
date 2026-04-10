<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curriculum extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'program_id',
        'curriculum_code',
        'description',
        'effective_year',
        'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'effective_year' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function prospectuses()
    {
        return $this->hasMany(Prospectus::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function getDisplayLabelAttribute(): string
    {
        return "{$this->curriculum_code} (Effective {$this->effective_year})";
    }

    public function toggleActive(): void
    {
        $this->update(['is_active' => ! $this->is_active]);
    }
}
