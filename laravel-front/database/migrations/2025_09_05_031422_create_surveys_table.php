// database/migrations/XXXX_XX_XX_XXXXXX_create_surveys_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('evaluatee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            $table->string('group')->nullable();
            $table->string('target_role');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->enum('type', ['rating', 'text'])->default('rating');
            $table->json('options')->nullable(); // For multiple choice questions
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('evaluatee_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('response');
            $table->timestamps();
            
            $table->unique(['survey_id', 'evaluator_id', 'question_id', 'evaluatee_id', 'subject_id'], 'response_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('responses');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('surveys');
    }
};