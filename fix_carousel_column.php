<?php

/**
 * Quick Fix Script for Production Database
 * Run this on production to fix the carousel_images column constraint
 * 
 * Usage: php fix_carousel_column.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "🔧 Fixing carousel_images column constraint...\n";
echo str_repeat('=', 70) . "\n\n";

try {
    // Step 1: Check if column exists and has constraints
    echo "Step 1: Checking current column structure...\n";
    $columns = DB::select("SHOW COLUMNS FROM linkedin_posts WHERE Field = 'carousel_images'");
    
    if (empty($columns)) {
        echo "❌ carousel_images column does not exist\n";
        echo "✅ Adding carousel_images as TEXT column...\n";
        DB::statement("ALTER TABLE linkedin_posts ADD COLUMN carousel_images TEXT NULL AFTER video_url");
        echo "✅ Column added successfully!\n";
    } else {
        echo "✅ carousel_images column exists\n";
        print_r($columns[0]);
        echo "\n";
        
        // Step 2: Try to find and drop constraints
        echo "\nStep 2: Looking for constraints...\n";
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_NAME = 'linkedin_posts' 
              AND TABLE_SCHEMA = DATABASE()
              AND CONSTRAINT_TYPE = 'CHECK'
        ");
        
        if (!empty($constraints)) {
            echo "Found " . count($constraints) . " CHECK constraint(s):\n";
            foreach ($constraints as $constraint) {
                echo "  - {$constraint->CONSTRAINT_NAME}\n";
                
                // Try to drop each constraint
                try {
                    DB::statement("ALTER TABLE linkedin_posts DROP CONSTRAINT `{$constraint->CONSTRAINT_NAME}`");
                    echo "    ✅ Dropped constraint: {$constraint->CONSTRAINT_NAME}\n";
                } catch (\Exception $e) {
                    echo "    ⚠️ Could not drop: " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "No CHECK constraints found\n";
        }
        
        // Step 3: Drop and recreate column
        echo "\nStep 3: Recreating column as TEXT...\n";
        DB::statement("ALTER TABLE linkedin_posts DROP COLUMN carousel_images");
        echo "✅ Old column dropped\n";
        
        DB::statement("ALTER TABLE linkedin_posts ADD COLUMN carousel_images TEXT NULL AFTER video_url");
        echo "✅ New column created as TEXT (no constraints)\n";
    }
    
    // Step 4: Update other columns
    echo "\nStep 4: Updating other columns...\n";
    
    // Update status enum
    DB::statement("ALTER TABLE linkedin_posts MODIFY COLUMN status ENUM('draft', 'scheduled', 'ready_to_publish', 'published', 'failed') DEFAULT 'draft'");
    echo "✅ Status enum updated (added 'ready_to_publish')\n";
    
    // Update image_url length
    DB::statement("ALTER TABLE linkedin_posts MODIFY COLUMN image_url VARCHAR(2000) NULL");
    echo "✅ image_url length increased to 2000\n";
    
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "✅ ALL FIXES APPLIED SUCCESSFULLY!\n";
    echo "\n";
    echo "You can now create carousel posts with PDF/PowerPoint files.\n";
    echo str_repeat('=', 70) . "\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n\n";
    
    echo "Manual fix needed. Run this SQL directly:\n";
    echo str_repeat('-', 70) . "\n";
    echo "ALTER TABLE linkedin_posts DROP COLUMN carousel_images;\n";
    echo "ALTER TABLE linkedin_posts ADD COLUMN carousel_images TEXT NULL AFTER video_url;\n";
    echo "ALTER TABLE linkedin_posts MODIFY COLUMN status ENUM('draft', 'scheduled', 'ready_to_publish', 'published', 'failed') DEFAULT 'draft';\n";
    echo "ALTER TABLE linkedin_posts MODIFY COLUMN image_url VARCHAR(2000) NULL;\n";
    echo str_repeat('-', 70) . "\n\n";
}

