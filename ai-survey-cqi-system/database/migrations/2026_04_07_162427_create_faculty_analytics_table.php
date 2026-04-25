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

            $table->ulid('offering_id')->index();
            $table->ulid('survey_id')->index();
            $table->ulid('faculty_id')->index();
            
            $table->decimal('avg_rating', 4, 2)->nullable(); // e.g., 4.75
            $table->integer('response_count')->default(0);
            $table->decimal('positive_sentiment_percent', 5, 2)->nullable();
            $table->decimal('neutral_sentiment_percent', 5, 2)->nullable();
            $table->decimal('negative_sentiment_percent', 5, 2)->nullable();
            
            $table->json('top_keywords')->nullable();
            $table->json('category_scores')->nullable();

            $table->timestamp('last_computed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();// Add soft deletes

            $table->unique(
                ['survey_id', 'deleted_at'], 
                'uniq_faculty_analytics_survey'
            );

            // Foreign key constraints
            $table->foreign('offering_id')
                  ->references('id')
                  ->on('course_offerings')
                  ->cascadeOnDelete();

            $table->foreign('survey_id')
                  ->references('id')
                  ->on('surveys')
                  ->cascadeOnDelete();
            
            $table->foreign('faculty_id')
                  ->references('id')->on('users')
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