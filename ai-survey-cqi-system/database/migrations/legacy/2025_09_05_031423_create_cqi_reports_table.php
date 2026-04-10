// database/migrations/XXXX_XX_XX_XXXXXX_create_cqi_reports_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cqi_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->json('data'); // Store report data in JSON format
            $table->string('file_path')->nullable(); // Path to exported report if needed
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cqi_reports');
    }
};