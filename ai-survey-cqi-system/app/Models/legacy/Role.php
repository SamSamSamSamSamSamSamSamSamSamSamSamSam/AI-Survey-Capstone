<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * Surveys targeting this role.
     */
    public function surveys()
    {
        return $this->hasMany(Survey::class, 'target_role_id');
    }
}