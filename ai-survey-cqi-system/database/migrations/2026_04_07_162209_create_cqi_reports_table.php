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
        Schema::create('cqi_reports', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->enum('scope_type', ['survey', 'offering', 'faculty']);

            $table->ulid('survey_id')->nullable();
            $table->ulid('faculty_id')->nullable();
            $table->ulid('offering_id')->nullable();
            $table->ulid('generated_by')->index(); // user who generated the report

            $table->string('title');
            $table->longText('report_text'); // Full AI-generated structured content (JSON)
            $table->json('statistics')->nullable(); // Snapshot of analytics at generation time
            $table->string('model_name')->nullable();
            $table->string('model_version')->nullable();
            $table->string('pdf_path')->nullable();
            $table->boolean('is_regenerated')->default(false);

            $table->timestamps();
            $table->softDeletes(); // deleted_at

            // Foreign key constraints
            $table->foreign('survey_id')
                  ->references('id')->on('surveys')
                  ->nullOnDelete();

            $table->foreign('offering_id')
                  ->references('id')->on('course_offerings')
                  ->nullOnDelete();

            $table->foreign('faculty_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            $table->foreign('generated_by')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->index(['survey_id', 'deleted_at'], 'idx_reports_survey_active');
            $table->index(['faculty_id', 'deleted_at'], 'idx_reports_faculty_active');
            $table->index(['offering_id', 'deleted_at'], 'idx_reports_offering_active');
        });

        Schema::create('cqi_report_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('report_id')->index();
            $table->ulid('performed_by');
            $table->string('action'); // generated, regenerated, downloaded, sent_to_faculty
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('report_id')
                  ->references('id')->on('cqi_reports')
                  ->cascadeOnDelete();

            $table->foreign('performed_by')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cqi_reports');
        Schema::dropIfExists('cqi_report_logs');
    }
};