<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScaleOption extends Model
{
    protected $fillable = ['scale_id', 'value', 'label', 'order_number'];

    public function scale()
    {
        return $this->belongsTo(Scale::class);
    }
}
