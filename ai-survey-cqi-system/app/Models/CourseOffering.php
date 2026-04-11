<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseOffering extends Model
{
    use SoftDeletes, HasFactory;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'subject_id',
        'semester_id',
        'teacher_id',
        'block_id',
        'group_number',
        'offering_type_id',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (CourseOffering $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::ulid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function subject()      { return $this->belongsTo(Subject::class); }
    public function semester()     { return $this->belongsTo(Semester::class); }
    public function teacher()      { return $this->belongsTo(User::class, 'teacher_id'); }
    public function block()        { return $this->belongsTo(Block::class); }
    public function offeringType() { return $this->belongsTo(OfferingType::class); }
    public function enrollments()  { return $this->hasMany(Enrollment::class, 'offering_id'); }
    public function surveys()      { return $this->hasMany(Survey::class, 'offering_id'); }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeCurrentSemester($query)
    {
        $active = Semester::current();
        return $active
            ? $query->where('semester_id', $active->id)
            : $query->whereRaw('1 = 0');
    }

    public function scopeForSemester($query, int|string $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function getDisplayNameAttribute(): string
    {
        $group = $this->group_number ? " (Group {$this->group_number})" : '';
        $block = $this->block ? " [{$this->block->name}]" : '';
        return "{$this->subject->course_code} — {$this->subject->name}{$group}{$block}";
    }

    public function isBlockOffering(): bool
    {
        return $this->block_id !== null;
    }
}
