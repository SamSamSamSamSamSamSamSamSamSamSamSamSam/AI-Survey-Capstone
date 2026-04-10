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

            $table->ulid('offering_id');
            $table->ulid('generated_by'); // FK to users

            $table->string('title');
            $table->text('report_text')->nullable();
            $table->json('statistics')->nullable();
            $table->string('pdf_path')->nullable();

            $table->timestamps();
            $table->softDeletes(); // deleted_at

            // Foreign key constraints
            $table->foreign('offering_id')
                  ->references('id')
                  ->on('course_offerings')
                  ->cascadeOnDelete();

            $table->foreign('generated_by')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cqi_reports');
    }
};