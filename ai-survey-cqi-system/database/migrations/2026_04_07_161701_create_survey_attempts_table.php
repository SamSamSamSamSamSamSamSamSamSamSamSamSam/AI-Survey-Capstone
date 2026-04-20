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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('notifiable_type');
            $table->string('notifiable_id', 26);
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('survey_attempts', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->ulid('survey_id')->index();   // UQ + INDEX
            $table->ulid('student_id')->index();  // UQ + INDEX
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('notify_email')->default(false);
            $table->boolean('notify_dashboard')->default(false);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('survey_id')
                  ->references('id')
                  ->on('surveys')
                  ->cascadeOnDelete();

            $table->foreign('student_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            // Unique constraint to prevent multiple attempts by the same student on the same survey
            $table->unique(['survey_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_attempts');
        Schema::dropIfExists('notifications');

    }
};