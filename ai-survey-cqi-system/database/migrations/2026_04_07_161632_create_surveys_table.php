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
        Schema::create('question_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('scales', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->tinyInteger('min_value');
            $table->tinyInteger('max_value');
            $table->timestamps();
        });

        Schema::create('scale_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scale_id');
            $table->tinyInteger('value');
            $table->string('label');
            $table->tinyInteger('order_number');
            $table->timestamps();

            $table->foreign('scale_id')
                  ->references('id')->on('scales')
                  ->cascadeOnDelete();
        });

        Schema::create('survey_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_official')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('survey_template_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('survey_template_id');
            $table->text('question_text');
            $table->enum('question_type', ['rating', 'text']);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('scale_id')->nullable();
            $table->unsignedSmallInteger('order_number')->default(1);
            $table->timestamps();

            $table->foreign('survey_template_id')
                  ->references('id')->on('survey_templates')
                  ->cascadeOnDelete();

            $table->foreign('category_id')
                  ->references('id')->on('question_categories')
                  ->nullOnDelete();

            $table->foreign('scale_id')
                  ->references('id')->on('scales')
                  ->nullOnDelete();
        });


        Schema::create('surveys', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->ulid('offering_id');
            $table->ulid('created_by');       // FK to users
            $table->unsignedBigInteger('template_id')->nullable(); // FK to survey_templates
            $table->unsignedBigInteger('target_role_id');   // FK to roles

            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);

            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            $table->timestamps();
            $table->softDeletes(); // deleted_at

            // Foreign key constraints
            $table->foreign('offering_id')
                  ->references('id')
                  ->on('course_offerings')
                  ->cascadeOnDelete();

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('template_id')
                  ->references('id')->on('survey_templates')
                  ->nullOnDelete();

            $table->foreign('target_role_id')
                  ->references('id')
                  ->on('roles')
                  ->cascadeOnDelete();
            $table->unique(
                ['offering_id', 'target_role_id', 'deleted_at'], 
                'uniq_offering_target_survey'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('scale_options');
        Schema::dropIfExists('scales');
        Schema::dropIfExists('question_categories');
        Schema::dropIfExists('survey_templates');    
        Schema::dropIfExists('survey_template_questions');
        Schema::dropIfExists('surveys');
    }
};