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
        Schema::create('post_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('category'); // e.g., 'story', 'listicle', 'value_drop', 'question'
            $table->string('industry')->nullable(); // e.g., 'tech', 'marketing', 'finance'
            $table->integer('engagement_score')->default(0); // For sorting by performance
            $table->json('variables')->nullable(); // Placeholders like {topic}, {industry}
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category', 'industry']);
            $table->index(['engagement_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_templates');
    }
};
