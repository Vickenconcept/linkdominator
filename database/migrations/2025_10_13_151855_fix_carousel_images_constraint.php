<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing carousel_images column if it has JSON constraints
        // and recreate it as a simple TEXT column for storing document URLs
        if (Schema::hasColumn('linkedin_posts', 'carousel_images')) {
            // First, try to find and remove any CHECK constraints on the column
            try {
                $constraints = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE TABLE_NAME = 'linkedin_posts' 
                      AND TABLE_SCHEMA = DATABASE()
                      AND CONSTRAINT_TYPE = 'CHECK'
                      AND CONSTRAINT_NAME LIKE '%carousel_images%'
                ");
                
                foreach ($constraints as $constraint) {
                    try {
                        DB::statement("ALTER TABLE linkedin_posts DROP CONSTRAINT `{$constraint->CONSTRAINT_NAME}`");
                        echo "✅ Dropped constraint: {$constraint->CONSTRAINT_NAME}\n";
                    } catch (\Exception $e) {
                        echo "⚠️ Could not drop constraint {$constraint->CONSTRAINT_NAME}: " . $e->getMessage() . "\n";
                    }
                }
            } catch (\Exception $e) {
                // Constraints query failed, continue anyway
                echo "⚠️ Could not query constraints: " . $e->getMessage() . "\n";
            }
            
            // Drop and recreate the column
            try {
                Schema::table('linkedin_posts', function (Blueprint $table) {
                    $table->dropColumn('carousel_images');
                });
                echo "✅ Dropped carousel_images column\n";
            } catch (\Exception $e) {
                echo "⚠️ Could not drop column: " . $e->getMessage() . "\n";
            }
        }
        
        // Add carousel_images as TEXT column (no constraints)
        if (!Schema::hasColumn('linkedin_posts', 'carousel_images')) {
            Schema::table('linkedin_posts', function (Blueprint $table) {
                $table->text('carousel_images')->nullable()->after('video_url');
            });
            echo "✅ Added carousel_images as TEXT column\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('linkedin_posts', function (Blueprint $table) {
            $table->dropColumn('carousel_images');
        });
        
        // Restore as JSON column if needed
        Schema::table('linkedin_posts', function (Blueprint $table) {
            $table->json('carousel_images')->nullable()->after('video_url');
        });
    }
};
