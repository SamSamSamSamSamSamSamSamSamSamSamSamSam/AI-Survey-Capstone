<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    protected $fillable = ['program_code', 'name'];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function curricula()
    {
        return $this->hasMany(Curriculum::class);
    }

    public function activeCurricula()
    {
        return $this->hasMany(Curriculum::class)->where('is_active', true);
    }

    // public function prospectuses()
    // {
    //     return $this->hasMany(Prospectus::class);
    // }

    public function offerings()
    {
        return $this->hasManyThrough(
            CourseOffering::class,
            // Prospectus::class,
            'program_id',
            'subject_id',
            'id',
            'subject_id'
        );
    }
}
