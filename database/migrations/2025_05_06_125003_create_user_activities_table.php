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
        Schema::create('users_activity', function (Blueprint $table) {
            $table->id();
            $table->string('module_name', 100)->nullable();
            $table->bigInteger('stats')->default(0);
            $table->unSignedBigInteger('user_id');
            $table->timestamps();
            $table->index('module_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_activity');
    }
};
