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
