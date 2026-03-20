<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // e.g. "1st Semester 2024-2025"
            $table->string('academic_year');               // e.g. "2024-2025"
            $table->tinyInteger('semester_number');        // 1 or 2
            $table->boolean('is_active')->default(false);  // only one active at a time
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};