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
        Schema::create('sn_leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('headline')->nullable();
            $table->string('email')->nullable();
            $table->string('lid')->nullable();
            $table->string('sn_lid')->nullable();
            $table->text('picture')->nullable();
            $table->text('geolocation')->nullable();
            $table->string('degree',15)->nullable();
            $table->string('object_urn',50)->nullable();
            $table->text('jobs')->nullable();
            $table->unSignedBigInteger('sn_list_id');
            $table->timestamps();
            $table->index('lid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sn_leads');
    }
};
