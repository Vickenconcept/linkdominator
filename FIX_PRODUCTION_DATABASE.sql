-- ============================================================================
-- FIX PRODUCTION DATABASE - Carousel Images Column
-- ============================================================================
-- Run this SQL directly on your production database to fix the constraint issue
-- ============================================================================

-- Step 1: Remove any CHECK constraints on carousel_images column
ALTER TABLE linkedin_posts DROP CONSTRAINT IF EXISTS `linkedin_posts.carousel_images`;
ALTER TABLE linkedin_posts DROP CONSTRAINT IF EXISTS `carousel_images`;

-- Step 2: Check current column structure
SHOW CREATE TABLE linkedin_posts;

-- Step 3: Drop and recreate carousel_images as TEXT (no constraints)
ALTER TABLE linkedin_posts DROP COLUMN IF EXISTS carousel_images;
ALTER TABLE linkedin_posts ADD COLUMN carousel_images TEXT NULL AFTER video_url;

-- Step 4: Update status enum to include 'ready_to_publish'
ALTER TABLE linkedin_posts MODIFY COLUMN status ENUM('draft', 'scheduled', 'ready_to_publish', 'published', 'failed') DEFAULT 'draft';

-- Step 5: Increase image_url length to support JSON arrays
ALTER TABLE linkedin_posts MODIFY COLUMN image_url VARCHAR(2000) NULL;

-- Step 6: Verify changes
DESCRIBE linkedin_posts;

-- ============================================================================
-- ALTERNATIVE: If above doesn't work, try this manual approach
-- ============================================================================

-- Get constraint name first:
SELECT CONSTRAINT_NAME 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_NAME = 'linkedin_posts' 
  AND TABLE_SCHEMA = 'tubetargeterapp_linkdominator_v2'
  AND CONSTRAINT_TYPE = 'CHECK';

-- Then drop it using the actual name shown:
-- ALTER TABLE linkedin_posts DROP CONSTRAINT `actual_constraint_name_here`;

-- Then recreate the column:
-- ALTER TABLE linkedin_posts DROP COLUMN carousel_images;
-- ALTER TABLE linkedin_posts ADD COLUMN carousel_images TEXT NULL AFTER video_url;

