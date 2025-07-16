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
        Schema::create('call_reminder_messages', function (Blueprint $table) {
            $table->id();
            $table->unSignedBigInteger('call_reminder_id');
            $table->boolean('16_24_hours_before_status')->default(false);
            $table->text('16_24_hours_before_message')->nullable();
            $table->boolean('couple_hours_before_status')->default(false);
            $table->text('couple_hours_before_message')->nullable();
            $table->boolean('10_40_minutes_before_status')->default(false);
            $table->text('10_40_minutes_before_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_reminder_messages');
    }
};
