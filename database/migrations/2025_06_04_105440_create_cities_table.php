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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('main');
            $table->string('icon');
            $table->string('description');
            $table->json('coordinates');
            $table->float('temperature');
            $table->string('country_code');
            $table->string('feels_like');
            $table->float('humidity');
            $table->integer('pressure');
            $table->float('wind_speed');
            $table->integer('wind_direction');
            $table->dateTime('sunrise');
            $table->dateTime('sunset');
            $table->integer('sea_level');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
