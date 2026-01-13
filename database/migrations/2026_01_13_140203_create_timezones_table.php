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
        Schema::create('timezones', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('country_code', 2);
            $table->string('country_name', 255);
            $table->string('time_zone', 255);
            $table->string('gmt_offset', 15);
            $table->string('timezone_link', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timezones');
    }
};
