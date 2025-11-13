<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audiences', function (Blueprint $table) {
            $table->string('tag')->nullable()->after('audience_type');
            $table->string('source')->nullable()->after('tag');
            $table->json('source_meta')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('audiences', function (Blueprint $table) {
            $table->dropColumn(['tag', 'source', 'source_meta']);
        });
    }
};


