<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory, HasUlids;

    // Composite unique: ['offering_id', 'student_id'] — enforced at DB level.
    // One student can be enrolled in many offerings; one offering can have many students.

    protected $fillable = [
        'offering_id',
        'student_id',
        'student_status_id',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function offering()
    {
        return $this->belongsTo(CourseOffering::class, 'offering_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function studentStatus()
    {
        return $this->belongsTo(StudentStatus::class);
    }
}