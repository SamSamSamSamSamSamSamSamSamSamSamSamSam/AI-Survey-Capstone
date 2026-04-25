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
        Schema::create('survey_questions', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->ulid('survey_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('scale_id')->nullable();
            $table->text('question_text');
            $table->enum('question_type', ['rating', 'text']);
            $table->tinyInteger('order_number')->default(1);

            $table->timestamps();
            $table->softDeletes(); // deleted_at

            // Foreign key constraint
            $table->foreign('survey_id')
                  ->references('id')
                  ->on('surveys')
                  ->cascadeOnDelete();

            $table->foreign('category_id')
                  ->references('id')->on('question_categories')
                  ->nullOnDelete();

            $table->foreign('scale_id')
                  ->references('id')->on('scales')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};