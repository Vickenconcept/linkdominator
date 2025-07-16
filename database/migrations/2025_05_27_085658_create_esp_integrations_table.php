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
        Schema::create('esp_integrations', function (Blueprint $table) {
            $table->id();
            $table->unSignedBigInteger('user_id');
            $table->text('mailchimp')->nullable();
            $table->text('getresponse')->nullable();
            $table->text('emailoctopus')->nullable();
            $table->text('converterkit')->nullable();
            $table->text('mailerlite')->nullable();
            $table->text('webhook')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('esp_integrations');
    }
};
