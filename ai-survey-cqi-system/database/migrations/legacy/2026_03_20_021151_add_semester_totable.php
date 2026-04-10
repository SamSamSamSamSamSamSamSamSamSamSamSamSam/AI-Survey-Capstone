<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add semester_id to surveys
        Schema::table('surveys', function (Blueprint $table) {
            $table->foreignId('semester_id')->nullable()->after('id')
                  ->constrained('semesters')->onDelete('set null');
        });

        // Add semester_id to responses
        Schema::table('responses', function (Blueprint $table) {
            $table->foreignId('semester_id')->nullable()->after('id')
                  ->constrained('semesters')->onDelete('set null');
        });

        // Add semester_id to cqi_reports
        Schema::table('cqi_reports', function (Blueprint $table) {
            $table->foreignId('semester_id')->nullable()->after('id')
                  ->constrained('semesters')->onDelete('set null');
        });

        // Add semester_id to subject_teacher pivot
        Schema::table('subject_teacher', function (Blueprint $table) {
            $table->foreignId('semester_id')->nullable()->after('group')
                  ->constrained('semesters')->onDelete('set null');
        });

        // Add semester_id to subject_student pivot
        Schema::table('subject_student', function (Blueprint $table) {
            $table->foreignId('semester_id')->nullable()->after('group')
                  ->constrained('semesters')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });

        Schema::table('responses', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });

        Schema::table('cqi_reports', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });

        Schema::table('subject_teacher', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });

        Schema::table('subject_student', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });
    }
};