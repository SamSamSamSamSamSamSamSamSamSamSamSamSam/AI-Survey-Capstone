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

            $table->float('sentiment_score', 8, 4); // e.g., 0.7532
            $table->string('model_name');
            $table->string('model_version');

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