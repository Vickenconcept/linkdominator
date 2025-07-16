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
        Schema::create('sn_leads_companies', function (Blueprint $table) {
            $table->id();
            $table->unSignedBigInteger('sn_lead_id');
            $table->string('company_name')->nullable();
            $table->string('company_specialities')->nullable();
            $table->string('company_tagline')->nullable();
            $table->text('company_description')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_phone', 15)->nullable();
            $table->string('company_staff_range')->nullable();
            $table->string('company_staff_count')->nullable();
            $table->string('company_headquaters')->nullable();
            $table->string('company_revenue')->nullable();
            $table->text('company_industries')->nullable();
            $table->text('company_logo')->nullable();
            $table->string('company_founded')->nullable();
            $table->string('company_lid')->nullable();
            $table->timestamps();
            $table->index('sn_lead_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sn_leads_companies');
    }
};
