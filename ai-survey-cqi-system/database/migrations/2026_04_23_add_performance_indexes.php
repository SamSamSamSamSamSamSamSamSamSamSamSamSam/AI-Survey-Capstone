<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * PERFORMANCE: Add indexes for critical query patterns used in:
     * - ComputeFacultyAnalyticsJob
     * - Faculty Dashboard
     * - Analytics API endpoints
     * - Survey retrieval operations
     */
    public function up(): void
    {
        // Responses table: improve queries filtering by attempt_id + question_type
        if (Schema::hasTable('responses')) {
            Schema::table('responses', function (Blueprint $table) {
                // Composite index for N+1 query elimination
                if (!$this->hasIndex('responses', 'attempt_id_survey_question_id_idx')) {
                    $table->index(['attempt_id', 'survey_question_id'], 'attempt_id_survey_question_id_idx');
                }
                
                // Index for rating aggregations
                if (!$this->hasIndex('responses', 'scale_value_idx')) {
                    $table->index('scale_value', 'scale_value_idx');
                }
                
                // Index for text response searches
                if (!$this->hasIndex('responses', 'text_response_idx')) {
                    $table->fullText('text_response', 'text_response_idx');
                }
            });
        }

        // Faculty Analytics table: improve semester filtering
        if (Schema::hasTable('faculty_analytics')) {
            Schema::table('faculty_analytics', function (Blueprint $table) {
                if (!$this->hasIndex('faculty_analytics', 'faculty_id_created_idx')) {
                    $table->index(['faculty_id', 'created_at'], 'faculty_id_created_idx');
                }
                
                if (!$this->hasIndex('faculty_analytics', 'survey_id_computed_idx')) {
                    $table->index(['survey_id', 'last_computed_at'], 'survey_id_computed_idx');
                }
            });
        }

        // Survey Attempts table: filter by student and submission status
        if (Schema::hasTable('survey_attempts')) {
            Schema::table('survey_attempts', function (Blueprint $table) {
                if (!$this->hasIndex('survey_attempts', 'student_id_submitted_idx')) {
                    $table->index(['student_id', 'submitted_at'], 'student_id_submitted_idx');
                }
            });
        }

        // Response Sentiments table: improve sentiment aggregation queries
        if (Schema::hasTable('response_sentiments')) {
            Schema::table('response_sentiments', function (Blueprint $table) {
                if (!$this->hasIndex('response_sentiments', 'response_id_sentiment_idx')) {
                    $table->index(['response_id', 'sentiment_type_id'], 'response_id_sentiment_idx');
                }
            });
        }

        // Course Offerings table: filter by teacher and semester
        if (Schema::hasTable('course_offerings')) {
            Schema::table('course_offerings', function (Blueprint $table) {
                if (!$this->hasIndex('course_offerings', 'teacher_id_semester_idx')) {
                    $table->index(['teacher_id', 'semester_id'], 'teacher_id_semester_idx');
                }
            });
        }

        // Enrollments table: check student enrollment efficiency
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                if (!$this->hasIndex('enrollments', 'offering_id_student_idx')) {
                    $table->index(['offering_id', 'student_id'], 'offering_id_student_idx');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('responses')) {
            Schema::table('responses', function (Blueprint $table) {
                $table->dropIndexIfExists(['attempt_id', 'survey_question_id']);
                $table->dropIndexIfExists(['scale_value']);
                // Note: fullText indexes need special handling
            });
        }

        if (Schema::hasTable('faculty_analytics')) {
            Schema::table('faculty_analytics', function (Blueprint $table) {
                $table->dropIndexIfExists(['faculty_id', 'created_at']);
                $table->dropIndexIfExists(['survey_id', 'last_computed_at']);
            });
        }

        if (Schema::hasTable('survey_attempts')) {
            Schema::table('survey_attempts', function (Blueprint $table) {
                $table->dropIndexIfExists(['student_id', 'submitted_at']);
            });
        }

        if (Schema::hasTable('response_sentiments')) {
            Schema::table('response_sentiments', function (Blueprint $table) {
                $table->dropIndexIfExists(['response_id', 'sentiment_type_id']);
            });
        }

        if (Schema::hasTable('course_offerings')) {
            Schema::table('course_offerings', function (Blueprint $table) {
                $table->dropIndexIfExists(['teacher_id', 'semester_id']);
            });
        }

        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->dropIndexIfExists(['offering_id', 'student_id']);
            });
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);
        return isset($indexes[strtolower($indexName)]);
    }
};
