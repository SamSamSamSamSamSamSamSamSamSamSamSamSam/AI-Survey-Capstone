<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'offering_id',
        'created_by',
        'target_role_id',
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * The course offering this survey is attached to.
     */
    public function offering()
    {
        return $this->belongsTo(CourseOffering::class, 'offering_id');
    }

    /**
     * The user (admin/teacher) who created this survey.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The role that should respond to this survey (e.g. student).
     */
    public function targetRole()
    {
        return $this->belongsTo(Role::class, 'target_role_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Attempt records submitted for this survey.
     */
    public function attempts()
    {
        return $this->hasMany(SurveyAttempt::class);
    }

    /**
     * Faculty analytics computed from this survey.
     */
    public function facultyAnalytics()
    {
        return $this->hasMany(FacultyAnalytic::class);
    }
}