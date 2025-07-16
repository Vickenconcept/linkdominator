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
        Schema::create('auto_message_response', function (Blueprint $table) {
            $table->id();
            $table->string('message_type');
            $table->text('message_keywords')->nullable();
            $table->bigInteger('total_endorse_skills')->default(1);
            $table->text('message_body')->nullable();
            $table->text('attachement')->nullable();
            $table->unSignedBigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_message_response');
    }
};
