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
        Schema::create('programs', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->string('program_code')->unique();
            $table->string('name');

            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });

        Schema::create('curricula', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('program_id');
            $table->string('curriculum_code');
            $table->string('description')->nullable();
            $table->year('effective_year');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('program_id')
                ->references('id')
                ->on('programs')
                ->cascadeOnDelete();

            $table->unique(['program_id', 'curriculum_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
        Schema::dropIfExists('curricula');
    }
};