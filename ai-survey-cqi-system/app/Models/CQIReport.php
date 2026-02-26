<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CQIReport extends Model
{
    use HasFactory;
    
    protected $table = 'cqi_reports';

    protected $fillable = ['title', 'description', 'survey_id', 'generated_by', 'data', 'file_path', 'data'];

    protected $casts = [
        'data' => 'array'
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}