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
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary(); // Use ULID for better performance and scalability
            $table->string('user_id_number')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });
        
        Schema::create('roles', function (Blueprint $table) {

            $table->id();

            $table->string('name')->unique();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('role_id');
            $table->ulid('user_id');

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->cascadeOnDelete();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            // Optional: prevent duplicate role-user combinations
            $table->unique(['role_id', 'user_id']);
        });

        Schema::create('programs', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->string('program_code')->unique();
            $table->string('name');

            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });

        Schema::create('subjects', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->string('course_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('units');

            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });

        Schema::create('semesters', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->string('name');
            $table->year('academic_start_year');
            $table->tinyInteger('semester_number'); // 1, 2, or 3
            $table->boolean('is_active')->default(false);

            $table->timestamps();
        });

        Schema::create('offering_types', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->string('name')->unique();

            $table->timestamps();
        });

        Schema::create('student_statuses', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->string('name')->unique();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        Schema::create('prospectuses', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->unsignedBigInteger('program_id'); // FK
            $table->unsignedBigInteger('subject_id'); // FK
            $table->tinyInteger('year_level');        // UQ part
            $table->tinyInteger('semester_number');   // UQ part
            $table->unsignedBigInteger('offered_type_id')->nullable(); // FK

            $table->timestamps();
            $table->softDeletes(); // deleted_at

            // Foreign key constraints
            $table->foreign('program_id')
                  ->references('id')
                  ->on('programs')
                  ->cascadeOnDelete();

            $table->foreign('subject_id')
                  ->references('id')
                  ->on('subjects')
                  ->cascadeOnDelete();

            $table->foreign('offered_type_id')
                  ->references('id')
                  ->on('offering_types')
                  ->nullOnDelete();

            // Unique constraint for subject/year_level/semester combination
            $table->unique(['program_id', 'subject_id', 'year_level', 'semester_number']);
        });

        Schema::create('course_offerings', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('semester_id')->index(); // INDEX(semester_id)
            $table->ulid('teacher_id')->index();              // INDEX(teacher_id)
            $table->unsignedInteger('group_number')->nullable(); // e.g. 1, 2, 3...
            $table->unsignedBigInteger('offering_type_id')->nullable();

            $table->timestamps();
            $table->softDeletes(); // deleted_at

            // Foreign key constraints
            $table->foreign('subject_id')
                  ->references('id')
                  ->on('subjects')
                  ->cascadeOnDelete();

            $table->foreign('semester_id')
                  ->references('id')
                  ->on('semesters')
                  ->cascadeOnDelete();

            $table->foreign('teacher_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('offering_type_id')
                  ->references('id')
                  ->on('offering_types')
                  ->nullOnDelete();
        });

        Schema::create('enrollments', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->ulid('offering_id')->index(); // UQ + INDEX
            $table->ulid('student_id')->index();  // UQ + INDEX
            $table->unsignedBigInteger('student_status_id');

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('offering_id')
                  ->references('id')
                  ->on('course_offerings')
                  ->cascadeOnDelete();

            $table->foreign('student_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('student_status_id')
                  ->references('id')
                  ->on('student_statuses')
                  ->restrictOnDelete();

            // Unique constraint to prevent duplicate enrollments
            $table->unique(['offering_id', 'student_id']);
        });

        Schema::create('surveys', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->ulid('offering_id');
            $table->ulid('created_by');       // FK to users
            $table->unsignedBigInteger('target_role_id');   // FK to roles

            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);

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

            $table->foreign('target_role_id')
                  ->references('id')
                  ->on('roles')
                  ->cascadeOnDelete();
        });

        Schema::create('questions', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->ulid('survey_id');

            $table->text('question_text');
            $table->string('category')->nullable();
            $table->enum('type', ['rating', 'text']);
            $table->tinyInteger('order')->default(1);

            $table->timestamps();
            $table->softDeletes(); // deleted_at

            // Foreign key constraint
            $table->foreign('survey_id')
                  ->references('id')
                  ->on('surveys')
                  ->cascadeOnDelete();
        });

        Schema::create('survey_attempts', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->ulid('survey_id')->index();   // UQ + INDEX
            $table->ulid('student_id')->index();  // UQ + INDEX
            $table->timestamp('submitted_at')->nullable();

            // Foreign key constraints
            $table->foreign('survey_id')
                  ->references('id')
                  ->on('surveys')
                  ->cascadeOnDelete();

            $table->foreign('student_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            // Unique constraint to prevent multiple attempts by the same student on the same survey
            $table->unique(['survey_id', 'student_id']);
        });

        Schema::create('responses', function (Blueprint $table) {

            $table->ulid('id')->primary(); // ULID PK

            $table->ulid('attempt_id')->index();
            $table->unsignedBigInteger('question_id')->index();

            $table->integer('rating_value')->nullable();
            $table->text('text_response')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('attempt_id')
                  ->references('id')
                  ->on('survey_attempts')
                  ->cascadeOnDelete();

            $table->foreign('question_id')
                  ->references('id')
                  ->on('questions')
                  ->cascadeOnDelete();
        });

        Schema::create('sentiment_types', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->string('label')->unique();

            $table->timestamps();
        });

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

        Schema::create('settings', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->string('key')->unique();
            $table->text('value')->nullable();

            $table->timestamps();
        });
    }
};
