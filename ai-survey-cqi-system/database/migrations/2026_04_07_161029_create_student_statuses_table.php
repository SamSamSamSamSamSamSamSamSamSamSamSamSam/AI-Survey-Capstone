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
        Schema::create('enrollment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();       // Block-Enrolled, Individually-Enrolled
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('semester_id');
            $table->string('name', 50);             // e.g. BSIT-2A
            $table->tinyInteger('year_level')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('semester_id')->references('id')->on('semesters')->cascadeOnDelete();

            $table->unique(['program_id', 'semester_id', 'name'], 'uniq_block');
            $table->index('program_id',  'idx_block_program');
            $table->index('semester_id', 'idx_block_semester');
        });

        Schema::create('block_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('block_id');
            $table->unsignedBigInteger('student_id');   // FK → users.id (ULID stored as string)
            $table->timestamps();

            $table->foreign('block_id')->references('id')->on('blocks')->cascadeOnDelete();
            // student_id references users.id which is a ULID (string)
            $table->string('student_id', 26)->change(); // ensure correct type after add

            $table->unique(['block_id', 'student_id'], 'uniq_block_student');
            $table->index('block_id',    'idx_block_student_block');
            $table->index('student_id',  'idx_block_student_user');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_types');
        Schema::dropIfExists('block_students');
        Schema::dropIfExists('blocks');
    }
};