<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gives the events table its real columns so user-created calendar
 * events can actually be persisted (previously the table was empty).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->dateTime('starts_at')->index();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'address', 'starts_at']);
            $table->dropSoftDeletes();
        });
    }
};
