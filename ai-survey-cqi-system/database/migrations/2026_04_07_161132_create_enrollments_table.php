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
        Schema::create('enrollments', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->ulid('offering_id')->index(); // UQ + INDEX
            $table->ulid('student_id')->index();  // UQ + INDEX
            $table->unsignedBigInteger('enrollment_type_id')->index();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('offering_id')
                  ->references('id')
                  ->on('course_offerings')
                  ->cascadeOnDelete();

            $table->foreign('student_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('enrollment_type_id')
                  ->references('id')->on('enrollment_types')
                  ->restrictOnDelete();

            // Unique constraint to prevent duplicate enrollments
            $table->unique(['offering_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};