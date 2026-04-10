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
        'target_role_id',
        'template_id',
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    public function offering()
    {
        return $this->belongsTo(CourseOffering::class, 'offering_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetRole()
    {
        return $this->belongsTo(Role::class, 'target_role_id');
    }

    public function template()
    {
        return $this->belongsTo(SurveyTemplate::class, 'template_id');
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order_number');
    }

    public function attempts()
    {
        return $this->hasMany(SurveyAttempt::class);
    }

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

    public function hasBeenAttemptedBy(string $userId): bool
    {
        return $this->attempts()->where('student_id', $userId)->whereNotNull('submitted_at')->exists();
    }

    public function isTargetedAt(User $user): bool
    {
        return $user->roles->contains('id', $this->target_role_id);
    }
}
