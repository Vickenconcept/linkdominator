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
        Schema::create('campaign_sequence_endorse', function (Blueprint $table) {
            $table->id();
            $table->unSignedBigInteger('campaign_id');
            $table->longText('node_model')->nullable();
            $table->longText('link_model')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_sequence_endorse');
    }
};
