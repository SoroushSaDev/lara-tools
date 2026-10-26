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
        // No index: the library is read whole into memory and filtered there,
        // so nothing in the app ever queries by `liked` alone.
        Schema::table('music', function (Blueprint $table) {
            $table->boolean('liked')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('music', function (Blueprint $table) {
            $table->dropColumn('liked');
        });
    }
};
