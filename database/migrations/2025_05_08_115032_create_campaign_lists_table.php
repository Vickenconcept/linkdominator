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
        Schema::create('campaign_lists', function (Blueprint $table) {
            $table->id();
            $table->unSignedBigInteger('campaign_id');
            $table->bigInteger('list_hash');
            $table->string('list_source', 3);
            $table->timestamps();
            $table->index('campaign_id');
            $table->index('list_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_lists');
    }
};
