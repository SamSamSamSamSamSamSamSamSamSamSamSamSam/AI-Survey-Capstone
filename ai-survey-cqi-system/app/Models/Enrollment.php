<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'offering_id',
        'student_id',
        'student_status_id',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Enrollment $model) {
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

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function studentStatus()
    {
        return $this->belongsTo(StudentStatus::class);
    }
}
