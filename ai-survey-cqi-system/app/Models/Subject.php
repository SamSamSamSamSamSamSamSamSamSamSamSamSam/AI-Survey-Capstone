<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = ['course_code', 'name', 'description', 'units'];

    public function prospectuses()
    {
        return $this->hasMany(Prospectus::class);
    }

    public function offerings()
    {
        return $this->hasMany(CourseOffering::class);
    }
}
