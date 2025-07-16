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
        Schema::create('call_reminder', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('campaign')->nullable();
            $table->bigInteger('requests')->default(0);
            $table->bigInteger('recipients')->default(0);
            $table->bigInteger('contacted')->default(0);
            $table->bigInteger('replies')->default(0);
            $table->bigInteger('interested')->default(0);
            $table->bigInteger('not_reached')->default(0);
            $table->string('industry')->nullable();
            $table->unSignedBigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_reminder');
    }
};
