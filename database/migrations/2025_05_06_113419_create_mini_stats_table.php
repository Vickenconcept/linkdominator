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
        Schema::create('mini_stats', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('connections')->default(0);
            $table->bigInteger('pending_invites')->default(0);
            $table->string('profile_views')->default(0);
            $table->bigInteger('search_appearance')->default(0);
            $table->unSignedBigInteger('user_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mini_stats');
    }
};
