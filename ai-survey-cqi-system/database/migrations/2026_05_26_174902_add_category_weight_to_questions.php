<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── survey_template_questions ─────────────────────────────────────────
        Schema::table('survey_template_questions', function (Blueprint $table) {
            // Nullable — null means "not yet distributed / will auto-distribute"
            // Only meaningful on rating-type questions
            $table->decimal('category_weight', 5, 2)
                  ->nullable()
                  ->after('order_number')
                  ->comment('Percentage weight (0-100) for this category within the template. Only applies to rating questions. All rating categories in a template must sum to 100.');
        });

        // ── survey_questions ──────────────────────────────────────────────────
        Schema::table('survey_questions', function (Blueprint $table) {
            // Copied from template at survey creation; admin can override per-survey
            $table->decimal('category_weight', 5, 2)
                  ->nullable()
                  ->after('order_number')
                  ->comment('Percentage weight inherited from template, overrideable per survey. Applies to rating questions only.');
        });
    }

    public function down(): void
    {
        Schema::table('survey_template_questions', function (Blueprint $table) {
            $table->dropColumn('category_weight');
        });

        Schema::table('survey_questions', function (Blueprint $table) {
            $table->dropColumn('category_weight');
        });
    }
};
