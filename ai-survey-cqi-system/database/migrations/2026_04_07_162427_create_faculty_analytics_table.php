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
        Schema::create('faculty_analytics', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->ulid('offering_id');
            $table->ulid('survey_id');

            $table->decimal('avg_rating', 5, 2)->nullable(); // e.g., 4.75
            $table->integer('response_count')->default(0);
            $table->decimal('positive_sentiment_percent', 5, 2)->default(0.00);
            $table->decimal('neutral_sentiment_percent', 5, 2)->default(0.00);
            $table->decimal('negative_sentiment_percent', 5, 2)->default(0.00);

            $table->json('top_keywords')->nullable();
            $table->json('category_scores')->nullable();

            $table->timestamp('last_computed_at')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('offering_id')
                  ->references('id')
                  ->on('course_offerings')
                  ->cascadeOnDelete();

            $table->foreign('survey_id')
                  ->references('id')
                  ->on('surveys')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_analytics');
    }
};