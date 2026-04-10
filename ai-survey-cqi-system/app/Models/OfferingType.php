<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferingType extends Model
{
    protected $fillable = ['name'];

    public function offerings()
    {
        return $this->hasMany(CourseOffering::class);
    }

    public function prospectuses()
    {
        return $this->hasMany(Prospectus::class, 'offered_type_id');
    }
}
