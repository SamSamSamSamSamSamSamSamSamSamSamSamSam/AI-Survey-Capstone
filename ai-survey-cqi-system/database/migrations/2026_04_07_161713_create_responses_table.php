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
        Schema::create('responses', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->ulid('attempt_id')->index();
            $table->unsignedBigInteger('survey_question_id')->index();

            $table->integer('scale_value')->nullable();
            $table->text('text_response')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('attempt_id')
                  ->references('id')
                  ->on('survey_attempts')
                  ->cascadeOnDelete();

            $table->foreign('survey_question_id')
                  ->references('id')
                  ->on('survey_questions')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};