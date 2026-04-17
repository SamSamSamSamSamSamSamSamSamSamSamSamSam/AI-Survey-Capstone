<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key', 'group', 'value', 'type',
        'label', 'description', 'is_sensitive', 'is_readonly',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
        'is_readonly'  => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Value casting helpers
    // -------------------------------------------------------------------------

    /**
     * Return value cast to its declared type.
     */
    public function getCastValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }

    /**
     * Cast a raw input value for storage.
     */
    public static function castForStorage(string $type, mixed $value): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json'    => is_string($value) ? $value : json_encode($value),
            default   => (string) ($value ?? ''),
        };
    }
}
