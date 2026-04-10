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
        Schema::create('response_sentiments', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->ulid('response_id')->index();
            $table->unsignedBigInteger('sentiment_type_id')->index();

            $table->decimal('sentiment_score', 5, 4);
            $table->string('model_name');
            $table->string('model_version');
            $table->integer('processing_time_ms')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('response_id')
                  ->references('id')
                  ->on('responses')
                  ->cascadeOnDelete();

            $table->foreign('sentiment_type_id')
                  ->references('id')
                  ->on('sentiment_types')
                  ->restrictOnDelete();
            
            $table->unique(['response_id', 'model_version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_sentiments');
    }
};