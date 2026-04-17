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
        // ------------------------------------------------------------------
        // settings  — key/value store with grouping and typing metadata
        // ------------------------------------------------------------------
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();            // dot-notation: app.name
            $table->string('group');                    // app | ai | survey | locale | mail | security | maintenance
            $table->text('value')->nullable();          // stored as string; cast by type
            $table->string('type')->default('string');  // string | boolean | integer | json | file
            $table->string('label');                    // human-readable label
            $table->text('description')->nullable();    // optional help text shown in UI
            $table->boolean('is_sensitive')->default(false); // masks value in audit log
            $table->boolean('is_readonly')->default(false);  // cannot be changed via UI (shown but locked)
            $table->timestamps();
        });
        // ------------------------------------------------------------------
        // setting_logs  — audit trail for every change
        // ------------------------------------------------------------------
        Schema::create('setting_logs', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('group');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('changed_by_name');          // denormalised — survives user deletion
            $table->ulid('changed_by_id')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index('key');
            $table->index('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_logs');
        Schema::dropIfExists('settings');
    }
};