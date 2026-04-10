<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * Prospectus entries with this offering type.
     */
    public function prospectuses()
    {
        return $this->hasMany(Prospectus::class, 'offered_type_id');
    }

    /**
     * Course offerings of this type.
     */
    public function courseOfferings()
    {
        return $this->hasMany(CourseOffering::class);
    }
}