<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettingLog extends Model
{
    protected $fillable = [
        'key', 'group', 'old_value', 'new_value',
        'changed_by_name', 'changed_by_id', 'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function setting(): BelongsTo
    {
        // 'key' is the foreign key on SettingLog
        // 'key' is the owner key on Setting
        return $this->belongsTo(Setting::class, 'key', 'key');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }

public function getMaskedValue(string $field): ?string
{
    $value = $this->{$field};
    if (empty($value)) return '—';
    if ($this->setting?->is_sensitive) {
        if (preg_match('/^•+$/u', $value)) return $value;
        return '••••••••' . mb_substr($value, -4);
    }
    return $value;
}
}
