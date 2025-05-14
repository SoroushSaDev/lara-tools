<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('music', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('path');

            $table->string('title')->nullable();
            $table->string('artist')->nullable();
            $table->string('album')->nullable();
            $table->integer('duration')->nullable(); // in seconds
            $table->string('cover_image')->nullable(); // stored path to album art

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('music');
    }
};
