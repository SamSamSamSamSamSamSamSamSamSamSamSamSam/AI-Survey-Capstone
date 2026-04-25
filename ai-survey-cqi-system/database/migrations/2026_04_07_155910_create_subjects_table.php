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
        Schema::create('subjects', function (Blueprint $table) {

            $table->id(); // Auto-increment PK

            $table->string('course_code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('units');

            $table->timestamps();
            $table->softDeletes(); // deleted_at

            $table->unique(
                ['course_code', 'deleted_at'], 
                'subjects_course_code_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};