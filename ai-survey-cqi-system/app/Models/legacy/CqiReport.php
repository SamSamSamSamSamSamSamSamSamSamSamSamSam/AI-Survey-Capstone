<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CqiReport extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'cqi_reports';

    protected $fillable = [
        'offering_id',
        'generated_by',
        'title',
        'report_text',
        'statistics',
        'pdf_path',
    ];

    protected $casts = [
        'statistics' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * The course offering this report covers.
     */
    public function offering()
    {
        return $this->belongsTo(CourseOffering::class, 'offering_id');
    }

    /**
     * The user (admin/teacher) who generated this report.
     */
    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}