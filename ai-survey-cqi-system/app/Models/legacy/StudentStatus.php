<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * Enrollments currently under this status.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}