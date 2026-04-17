<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingLog extends Model
{
    protected $fillable = [
        'key', 'group', 'old_value', 'new_value',
        'changed_by_name', 'changed_by_id', 'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }
}
