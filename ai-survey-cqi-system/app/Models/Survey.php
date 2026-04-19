<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Survey extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'offering_id',
        'created_by',
        'template_id',
        'target_role_id',
        'title',
        'description',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Survey $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::ulid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function offering()   { return $this->belongsTo(CourseOffering::class, 'offering_id'); }
    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }
    public function targetRole() { return $this->belongsTo(Role::class, 'target_role_id'); }
    public function template()   { return $this->belongsTo(SurveyTemplate::class, 'template_id'); }
    public function questions()  { return $this->hasMany(SurveyQuestion::class)->orderBy('order_number'); }
    public function attempts()   { return $this->hasMany(SurveyAttempt::class); }
    public function analytics()  { return $this->hasMany(FacultyAnalytics::class, 'survey_id'); }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Surveys that are currently within their active period.
     */
    public function scopeLive($query)
    {
        return $query->where('is_active', true)
                     ->where(fn ($q) =>
                         $q->whereNull('start_date')->orWhere('start_date', '<=', now())
                     )
                     ->where(fn ($q) =>
                         $q->whereNull('end_date')->orWhere('end_date', '>=', now())
                     );
    }

    /**
     * Surveys the given user is eligible to take — role-aware scope.
     *
     * student → enrolled in offering
     * faculty → has faculty role AND is NOT the teacher of the offering
     * admin   → has admin role
     */
    public function scopeEligibleFor($query, User $user)
    {
        $role = $user->primaryRole()?->name;

        $query->scopeLive($query);

        return match ($role) {

            'student' => $query
                ->whereHas('targetRole', fn ($q) => $q->where('name', 'student'))
                ->whereHas('offering.enrollments', fn ($q) =>
                    $q->where('student_id', $user->id)
                ),

            'faculty' => $query
                ->whereHas('targetRole', fn ($q) => $q->where('name', 'faculty'))
                ->whereDoesntHave('offering', fn ($q) =>
                    $q->where('teacher_id', $user->id)
                ),

            'admin' => $query
                ->whereHas('targetRole', fn ($q) => $q->where('name', 'admin')),

            default => $query->whereRaw('1 = 0'), // no matches
        };
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isLive(): bool
    {
        if (! $this->is_active) return false;
        if ($this->start_date && $this->start_date->isFuture()) return false;
        if ($this->end_date   && $this->end_date->isPast())    return false;
        return true;
    }

    public function hasBeenAttemptedBy(string $userId): bool
    {
        return $this->attempts()->where('student_id', $userId)->whereNotNull('submitted_at')->exists();
    }

    /**
     * Check if a user is eligible to take this survey based on role logic.
     *
     * student → must be enrolled in the offering
     * faculty → must have faculty role AND must NOT be the teacher of this offering
     * admin   → must have admin role
     */
    public function isEligibleFor(User $user): bool
    {
        if (! $this->isLive()) return false;

        $targetRole = $this->targetRole?->name ?? null;

        return match ($targetRole) {

            'student' => $user->hasRole('student')
                && Enrollment::where('offering_id', $this->offering_id)
                             ->where('student_id', $user->id)
                             ->exists(),

            'faculty' => $user->hasRole('faculty')
                && ($this->offering?->teacher_id !== $user->id),

            'admin' => $user->hasRole('admin'),

            default => false,
        };
    }

    /**
     * Get count of eligible respondents for this survey.
     * Useful for admin survey management view.
     */
    public function getEligibleCountAttribute(): int
    {
        $targetRole = $this->targetRole?->name ?? null;

        return match ($targetRole) {

            'student' => Enrollment::where('offering_id', $this->offering_id)->count(),

            'faculty' => User::whereHas('roles', fn ($q) => $q->where('name', 'faculty'))
                             ->where('id', '!=', $this->offering?->teacher_id)
                             ->count(),

            'admin' => User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->count(),

            default => 0,
        };
    }

    public function isTargetedAt(User $user): bool
    {
        return $user->roles->contains('id', $this->target_role_id);
    }

    public function getPeriodLabelAttribute(): string
    {
        if (! $this->start_date && ! $this->end_date) return 'No period set';
        $start = $this->start_date?->format('M d, Y h:i A') ?? '—';
        $end   = $this->end_date?->format('M d, Y h:i A') ?? '—';
        return "{$start} → {$end}";
    }
}
