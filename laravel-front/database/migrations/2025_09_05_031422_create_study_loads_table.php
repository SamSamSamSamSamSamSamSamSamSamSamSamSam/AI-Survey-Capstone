// database/migrations/XXXX_XX_XX_XXXXXX_create_study_loads_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('study_loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('semester');
            $table->year('academic_year');
            $table->timestamps();
            
            $table->unique(['student_id', 'subject_id', 'semester', 'academic_year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('study_loads');
    }
};