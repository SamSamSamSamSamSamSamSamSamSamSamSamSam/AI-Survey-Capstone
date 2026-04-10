<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseOffering extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'semester_id',
        'teacher_id',
        'group_number',
        'offering_type_id',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * The faculty member assigned to this offering.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function offeringType()
    {
        return $this->belongsTo(OfferingType::class);
    }

    /**
     * Students enrolled in this offering.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'offering_id');
    }

    /**
     * Surveys created for this offering.
     */
    public function surveys()
    {
        return $this->hasMany(Survey::class, 'offering_id');
    }

    /**
     * CQI reports generated for this offering.
     */
    public function cqiReports()
    {
        return $this->hasMany(CqiReport::class, 'offering_id');
    }

    /**
     * Faculty analytics computed for this offering.
     */
    public function facultyAnalytics()
    {
        return $this->hasMany(FacultyAnalytic::class, 'offering_id');
    }
}