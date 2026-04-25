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
        Schema::create('course_offerings', function (Blueprint $table) {

            $table->ulid('id')->primary(); 

            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('semester_id')->index(); 
            $table->ulid('teacher_id')->index();       
            $table->unsignedBigInteger('block_id')->nullable()->index();       
            $table->unsignedInteger('group_number')->nullable();
            $table->unsignedBigInteger('offering_type_id')->nullable();

            $table->timestamps();
            $table->softDeletes(); // deleted_at

            // Foreign key constraints
            $table->foreign('subject_id')
                  ->references('id')
                  ->on('subjects')
                  ->cascadeOnDelete();

            $table->foreign('semester_id')
                  ->references('id')
                  ->on('semesters')
                  ->cascadeOnDelete();

            $table->foreign('teacher_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('offering_type_id')
                  ->references('id')
                  ->on('offering_types')
                  ->nullOnDelete();

            $table->foreign('block_id')
                  ->references('id')->on('blocks')
                  ->cascadeOnDelete();

            $table->unique(['group_number', 'semester_id', 'subject_id', 'deleted_at'], 'uniq_offering');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_offerings');
    }
};