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
        Schema::create('audience_lists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('audience_id');
            $table->string('con_first_name')->nullable();
            $table->string('con_last_name')->nullable();
            $table->string('con_email')->nullable();
            $table->string('con_job_title')->nullable();
            $table->string('con_location')->nullable();
            $table->string('con_distance')->nullable();
            $table->string('con_public_identifier')->nullable();
            $table->string('con_id')->nullable();
            $table->string('con_tracking_id')->nullable();
            $table->tinyInteger('con_premium')->nullable();
            $table->tinyInteger('con_influencer')->nullable();
            $table->tinyInteger('con_jobseeker')->nullable();
            $table->string('con_company_url')->nullable();
            $table->string('con_member_urn')->nullable();
            $table->timestamps();
            $table->index('audience_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audience_lists');
    }
};
