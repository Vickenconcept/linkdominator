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
        Schema::create('campaign_leadgen_running', function (Blueprint $table) {
            $table->id();
            $table->unSignedBigInteger('lead_id');
            $table->string('lead_src', 10);
            $table->bigInteger('lead_list');
            $table->unSignedBigInteger('campaign_id');
            $table->bigInteger('current_node_key');
            $table->bigInteger('next_node_key');
            $table->boolean('accept_status')->default(false);
            $table->integer('status_last_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_leadgen_running');
    }
};
