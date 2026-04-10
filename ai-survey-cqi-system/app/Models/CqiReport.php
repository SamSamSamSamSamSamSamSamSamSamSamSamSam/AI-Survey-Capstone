<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CqiReport extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'scope_type',
        'survey_id',
        'offering_id',
        'faculty_id',
        'generated_by',
        'title',
        'report_text',
        'statistics',
        'model_name',
        'model_version',
        'pdf_path',
        'is_regenerated',
    ];

    protected $casts = [
        'statistics'    => 'array',
        'is_regenerated'=> 'boolean',
        'report_text'   => 'array', // stored as JSON, parsed as array
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->{$m->getKeyName()})) {
                $m->{$m->getKeyName()} = (string) \Illuminate\Support\Str::ulid();
            }
        });
    }

    public function survey()    { return $this->belongsTo(Survey::class, 'survey_id'); }
    public function offering()  { return $this->belongsTo(CourseOffering::class, 'offering_id'); }
    public function faculty()   { return $this->belongsTo(User::class, 'faculty_id'); }
    public function generatedBy(){ return $this->belongsTo(User::class, 'generated_by'); }
    public function logs()      { return $this->hasMany(CqiReportLog::class, 'report_id')->latest(); }
}
