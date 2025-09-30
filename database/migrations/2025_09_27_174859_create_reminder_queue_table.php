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
        Schema::create('reminder_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('call_id');
            $table->string('recipient');
            $table->text('message');
            $table->string('conversation_urn_id')->nullable();
            $table->string('linkedin_id');
            $table->enum('status', ['pending', 'processing', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index('linkedin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_queue');
    }
};