<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audience_lists', function (Blueprint $table) {
            $table->string('con_company_name')->nullable()->after('con_company_url');
            $table->string('con_profile_url')->nullable()->after('con_member_urn');
            $table->dateTime('con_last_activity')->nullable()->after('con_profile_url');
        });
    }

    public function down(): void
    {
        Schema::table('audience_lists', function (Blueprint $table) {
            $table->dropColumn(['con_company_name', 'con_profile_url', 'con_last_activity']);
        });
    }
};


