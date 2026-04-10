<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prospectuses', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->unsignedBigInteger('curriculum_id'); // FK
            $table->unsignedBigInteger('subject_id'); // FK
            $table->tinyInteger('year_level');        // UQ part
            $table->tinyInteger('semester_number');   // UQ part

            $table->timestamps();
            $table->softDeletes(); // deleted_at

            // Foreign key constraints
            $table->foreign('curriculum_id')
                  ->references('id')
                  ->on('curricula')
                  ->cascadeOnDelete();

            $table->foreign('subject_id')
                  ->references('id')
                  ->on('subjects')
                  ->cascadeOnDelete();

            // Unique constraint for subject/year_level/semester combination
            $table->unique(['curriculum_id', 'subject_id', 'year_level', 'semester_number'], 
                    'prospectus_curriculum_subj_year_sem_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospectuses');
    }
};