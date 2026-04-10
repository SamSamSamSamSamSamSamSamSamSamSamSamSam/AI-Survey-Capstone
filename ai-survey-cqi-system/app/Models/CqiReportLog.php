<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CqiReportLog extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = ['report_id', 'performed_by', 'action', 'notes'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->{$m->getKeyName()})) {
                $m->{$m->getKeyName()} = (string) \Illuminate\Support\Str::ulid();
            }
        });
    }

    public function report()      { return $this->belongsTo(CqiReport::class, 'report_id'); }
    public function performedBy() { return $this->belongsTo(User::class, 'performed_by'); }
}
