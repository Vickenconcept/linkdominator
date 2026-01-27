<?php

namespace App\Console\Commands;

use App\Models\AutoCommentPreference;
use App\Models\AutoCommentPost;
use App\Models\User;
use App\Services\LinkedInApiService;
use App\Services\ChatGPT;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessAutoComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-auto-comments 
                            {--user= : Process for specific user only}
                            {--fetch-only : Only fetch posts, do not generate comments}
                            {--post-only : Only post scheduled comments, do not fetch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process auto-comments: fetch posts matching preferences, generate AI comments, and post them at optimal times';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = now();
        $this->info('🤖 Starting Auto-Comment Processing...');
        $this->info('Timestamp: ' . $startTime->format('Y-m-d H:i:s'));
        $this->newLine();
        
        Log::info('═══════════════════════════════════════════════');
        Log::info('🤖 AUTO-COMMENT PROCESSING STARTED');
        Log::info('═══════════════════════════════════════════════');

        $stats = [
            'posts_found' => 0,
            'comments_generated' => 0,
            'comments_scheduled' => 0,
            'comments_posted' => 0,
            'errors' => 0,
        ];

        // Step 1: Fetch new posts if not post-only mode
        if (!$this->option('post-only')) {
            $stats['posts_found'] = $this->fetchNewPosts();
        }

        // Step 2: Generate comments for pending posts if not fetch-only mode
        if (!$this->option('fetch-only')) {
            $stats['comments_generated'] = $this->generateComments();
            $postedStats = $this->scheduleAndPostComments();
            $stats['comments_scheduled'] = $postedStats['scheduled'];
            $stats['comments_posted'] = $postedStats['posted'];
            $stats['errors'] = $postedStats['errors'];
        }

        $duration = now()->diffInSeconds($startTime);
        
        $this->newLine();
        $this->info('═══════════════════════════════════════════════');
        $this->info('📊 PROCESSING SUMMARY:');
        $this->info('   Posts Found: ' . $stats['posts_found']);
        $this->info('   Comments Generated: ' . $stats['comments_generated']);
        $this->info('   Comments Scheduled: ' . $stats['comments_scheduled']);
        $this->info('   Comments Posted: ' . $stats['comments_posted']);
        $this->info('   Errors: ' . $stats['errors']);
        $this->info('   Time Elapsed: ' . $duration . ' seconds');
        $this->info('═══════════════════════════════════════════════');
        $this->info('✅ Auto-comment processing completed!');
        
        Log::info('═══════════════════════════════════════════════');
        Log::info('📊 PROCESSING SUMMARY', $stats);
        Log::info('   Time Elapsed: ' . $duration . ' seconds');
        Log::info('═══════════════════════════════════════════════');
        Log::info('✅ AUTO-COMMENT PROCESSING COMPLETED');
        Log::info('═══════════════════════════════════════════════');
    }

    /**
     * Fetch new posts based on user preferences
     */
    private function fetchNewPosts()
    {
        $this->info('📥 Fetching new posts...');
        Log::info('📥 Fetching new posts...');
        
        $totalFound = 0;
        $users = $this->option('user') 
            ? User::where('id', $this->option('user'))->get()
            : User::whereHas('autoCommentPreferences', function($q) {
                $q->where('is_active', true);
            })->get();

        $this->info("  Found {$users->count()} user(s) with active preferences");
        Log::info("Found {$users->count()} user(s) with active preferences");

        foreach ($users as $user) {
            if (empty($user->access_token)) {
                $msg = "  ⚠️  User {$user->name} (#{$user->id}) has no LinkedIn access token";
                $this->warn($msg);
                Log::warning($msg, ['user_id' => $user->id, 'user_name' => $user->name]);
                continue;
            }

            $preference = $user->autoCommentPreferences;
            if (!$preference || !$preference->is_active) {
                $this->warn("  ⚠️  User {$user->name} (#{$user->id}) has no active preferences");
                continue;
            }

            $this->info("  👤 Processing for: {$user->name} (ID: {$user->id})");
            Log::info("Processing user", ['user_id' => $user->id, 'user_name' => $user->name]);

            try {
                $linkedinService = new LinkedInApiService($user->access_token);
                
                // Fetch from followed accounts
                if (!empty($preference->followed_accounts)) {
                    $found = $this->fetchFromFollowedAccounts($user, $preference, $linkedinService);
                    $totalFound += $found;
                } else {
                    $this->info("    ℹ️  No followed accounts configured");
                    Log::info("No followed accounts configured", ['user_id' => $user->id]);
                }

                // Fetch posts matching keywords
                // Note: Official LinkedIn API doesn't support public keyword search of all posts
                // So we use RapidAPI for keyword search (which you already have in your codebase)
                // The official API is used for posting comments (which requires official API)
                if (!empty($preference->keywords)) {
                    $this->info("  🔍 Processing keyword search: " . implode(', ', $preference->keywords));
                    $found = $this->fetchFromKeywords($user, $preference, $linkedinService);
                    $totalFound += $found;
                }
            } catch (\Throwable $th) {
                $msg = "  ❌ Error processing user {$user->name}: " . $th->getMessage();
                $this->error($msg);
                Log::error('Auto-comment fetch error', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'error' => $th->getMessage(),
                    'trace' => $th->getTraceAsString()
                ]);
            }
        }
        
        Log::info("Posts fetch completed", ['total_found' => $totalFound]);
        return $totalFound;
    }

    /**
     * Fetch posts from followed accounts
     */
    private function fetchFromFollowedAccounts($user, $preference, $linkedinService)
    {
        $postsFound = 0;
        
        foreach ($preference->followed_accounts as $accountUrn) {
            try {
                // Extract URN if URL provided, or use as-is
                $authorUrn = $this->extractUrnFromUrl($accountUrn);
                
                if (!$authorUrn) {
                    $msg = "    ⚠️  Invalid account URN: {$accountUrn}";
                    $this->warn($msg);
                    Log::warning('Invalid account URN', [
                        'user_id' => $user->id,
                        'account_urn' => $accountUrn
                    ]);
                    continue;
                }

                $this->info("    🔍 Fetching posts from: {$accountUrn}");
                Log::info('Fetching posts from account', [
                    'user_id' => $user->id,
                    'account_urn' => $accountUrn
                ]);
                
                $response = $linkedinService->getPostsByAuthor($authorUrn, ['count' => 20]);
                
                if (empty($response['elements'])) {
                    $this->info("    ℹ️  No posts found from this account");
                    Log::info('No posts found from account', [
                        'user_id' => $user->id,
                        'account_urn' => $accountUrn
                    ]);
                    continue;
                }

                $this->info("    📋 Found " . count($response['elements']) . " posts, filtering...");

                foreach ($response['elements'] as $postData) {
                    $postUrn = $postData['id'] ?? null;
                    if (!$postUrn) continue;

                    // Check if already exists
                    $existingPost = AutoCommentPost::where('post_urn', $postUrn)
                        ->where('user_id', $user->id)
                        ->first();

                    if ($existingPost) {
                        continue;
                    }

                    // Filter by engagement
                    $engagement = $postData['specificContent']['com.linkedin.ugc.ShareContent']['socialContext']['totalSocialActivityCounts']['totalShareStatistics'] ?? 0;
                    if ($engagement < $preference->min_engagement) {
                        continue;
                    }

                    // Filter by date
                    $postDate = Carbon::parse($postData['lastModifiedTime'] ?? $postData['created']['time'] ?? now());
                    if ($preference->only_fresh_posts && $postDate->diffInHours(now()) > 24) {
                        continue;
                    }
                    if ($postDate->diffInDays(now()) > $preference->skip_posts_older_than_days) {
                        continue;
                    }

                    // Save post
                    $post = AutoCommentPost::create([
                        'preference_id' => $preference->id,
                        'user_id' => $user->id,
                        'post_urn' => $postUrn,
                        'post_content' => $postData['specificContent']['com.linkedin.ugc.ShareContent']['text']['text'] ?? '',
                        'post_date' => $postDate,
                        'status' => 'pending',
                        'match_type' => 'followed_account',
                        'likes' => $engagement,
                    ]);

                    $postsFound++;
                    $this->info("    ✅ Saved post #{$post->id}: {$postUrn} (Engagement: {$engagement})");
                    Log::info('Post saved for commenting', [
                        'user_id' => $user->id,
                        'post_id' => $post->id,
                        'post_urn' => $postUrn,
                        'engagement' => $engagement,
                        'post_date' => $postDate->toDateTimeString()
                    ]);
                }
            } catch (\Throwable $th) {
                $msg = "    ❌ Error fetching from {$accountUrn}: " . $th->getMessage();
                $this->error($msg);
                Log::error('Error fetching posts from account', [
                    'user_id' => $user->id,
                    'account_urn' => $accountUrn,
                    'error' => $th->getMessage(),
                    'trace' => $th->getTraceAsString()
                ]);
            }
        }
        
        $this->info("    📊 Total posts found from followed accounts: {$postsFound}");
        return $postsFound;
    }

    /**
     * Fetch posts matching keywords
     * Uses RapidAPI as the official LinkedIn API doesn't support keyword search of public posts
     */
    private function fetchFromKeywords($user, $preference, $linkedinService)
    {
        $postsFound = 0;
        
        // Official LinkedIn API doesn't support keyword search of public posts
        // Use RapidAPI service for keyword search (already in your codebase)
        $rapidapiService = new \App\Services\RapidApiService();
        
        foreach ($preference->keywords as $keyword) {
            try {
                $this->info("    🔍 Searching for keyword: {$keyword}");
                Log::info('Searching posts by keyword', [
                    'user_id' => $user->id,
                    'keyword' => $keyword
                ]);

                // Use RapidAPI for keyword search with engagement filters
                // This prevents fetching 1000 posts and filtering - we filter at API level when possible
                $filters = [
                    'min_likes' => $preference->min_engagement,
                    'limit' => 50 // Only fetch 50 posts max that meet criteria
                ];
                
                // RapidAPI no longer honors relative date ranges, so request all-time and filter locally.
                $response = $rapidapiService->search_posts($keyword, 1, 'any-time', $filters);
                
                if (empty($response['data']) || !is_array($response['data'])) {
                    $this->info("    ℹ️  No posts found for keyword: {$keyword}");
                    continue;
                }

                $originalCount = $response['original_count'] ?? count($response['data']);
                $filteredCount = $response['filtered_count'] ?? count($response['data']);
                
                $this->info("    📋 Found {$originalCount} posts, {$filteredCount} match criteria (min {$preference->min_engagement} likes)");
                Log::info('Posts filtered from keyword search', [
                    'user_id' => $user->id,
                    'keyword' => $keyword,
                    'original_count' => $originalCount,
                    'filtered_count' => $filteredCount,
                    'min_engagement' => $preference->min_engagement
                ]);

                foreach ($response['data'] as $postData) {
                    // Handle different response formats
                    $postUrn = $postData['urn'] ?? $postData['post_urn'] ?? null;
                    if (!$postUrn) continue;

                    // Check if already exists
                    $existingPost = AutoCommentPost::where('post_urn', $postUrn)
                        ->where('user_id', $user->id)
                        ->first();

                    if ($existingPost) {
                        continue;
                    }

                    // Extract post content
                    $postContent = $postData['text'] ?? $postData['post'] ?? '';
                    
                    // Extract engagement (already filtered by RapidAPI, but keep for logging)
                    $engagement = (int)($postData['num_likes'] ?? $postData['likes'] ?? 0);
                    // Note: Engagement filtering already done by RapidAPI service with filters

                    // Extract post date
                    $postDate = isset($postData['posted']) 
                        ? Carbon::parse($postData['posted']) 
                        : now();
                    
                    // Filter by date
                    if ($preference->only_fresh_posts && $postDate->diffInHours(now()) > 24) {
                        continue;
                    }
                    if ($postDate->diffInDays(now()) > $preference->skip_posts_older_than_days) {
                        continue;
                    }

                    // Extract author info
                    $authorName = 'Unknown';
                    $authorHeadline = '';
                    $authorUrl = '';
                    
                    if (isset($postData['poster'])) {
                        $authorName = ($postData['poster']['first'] ?? '') . ' ' . ($postData['poster']['last'] ?? '');
                        $authorHeadline = $postData['poster']['headline'] ?? '';
                        $authorUrl = $postData['poster_linkedin_url'] ?? '';
                    } elseif (isset($postData['poster_name'])) {
                        $authorName = $postData['poster_name'];
                        $authorHeadline = $postData['poster_title'] ?? '';
                        $authorUrl = $postData['poster_linkedin_url'] ?? '';
                    }

                    // Save post
                    $post = AutoCommentPost::create([
                        'preference_id' => $preference->id,
                        'user_id' => $user->id,
                        'post_urn' => $postUrn,
                        'post_url' => $postData['post_url'] ?? null,
                        'post_content' => $postContent,
                        'post_date' => $postDate,
                        'status' => 'pending',
                        'match_type' => 'keyword',
                        'matched_keywords' => $keyword,
                        'author_name' => trim($authorName),
                        'author_headline' => $authorHeadline,
                        'author_profile_url' => $authorUrl,
                        'likes' => $engagement,
                        'comments' => (int)($postData['num_comments'] ?? 0),
                        'shares' => (int)($postData['num_shares'] ?? 0),
                    ]);

                    $postsFound++;
                    $this->info("    ✅ Saved post #{$post->id}: {$postUrn} (Keyword: {$keyword}, Engagement: {$engagement})");
                    Log::info('Post saved from keyword search', [
                        'user_id' => $user->id,
                        'post_id' => $post->id,
                        'post_urn' => $postUrn,
                        'keyword' => $keyword,
                        'engagement' => $engagement,
                        'post_date' => $postDate->toDateTimeString()
                    ]);
                }

                // Rate limiting - don't hammer the API
                sleep(2);

            } catch (\Throwable $th) {
                $msg = "    ❌ Error searching keyword '{$keyword}': " . $th->getMessage();
                $this->error($msg);
                Log::error('Error searching posts by keyword', [
                    'user_id' => $user->id,
                    'keyword' => $keyword,
                    'error' => $th->getMessage(),
                    'trace' => $th->getTraceAsString()
                ]);
            }
        }

        $this->info("    📊 Total posts found from keywords: {$postsFound}");
        return $postsFound;
    }

    /**
     * Generate AI comments for pending posts
     */
    private function generateComments()
    {
        $this->info('💬 Generating AI comments...');
        Log::info('💬 Generating AI comments...');

        $posts = AutoCommentPost::where('status', 'pending')
            ->whereNull('generated_comment')
            ->whereHas('preference', function($q) {
                $q->where('is_active', true);
            })
            ->with(['preference', 'user'])
            ->limit(50)
            ->get();

        $this->info("  Found {$posts->count()} posts needing comments");
        Log::info("Found posts needing comments", ['count' => $posts->count()]);

        $generatedCount = 0;

        foreach ($posts as $post) {
            try {
                $this->info("  📝 Generating comment for post #{$post->id}");
                Log::info('Generating comment', [
                    'post_id' => $post->id,
                    'post_urn' => $post->post_urn,
                    'user_id' => $post->user_id
                ]);

                $preference = $post->preference;
                $prompt = $this->buildCommentPrompt($post, $preference);

                $chatgpt = new ChatGPT;
                $chatgpt->checkModeration($prompt);
                $result = $chatgpt->generateContent($prompt);

                $comment = $result['content'] ?? '';

                if (empty($comment)) {
                    $post->update(['status' => 'skipped', 'error_message' => 'Failed to generate comment']);
                    $this->warn("  ⚠️  Failed to generate comment for post #{$post->id}");
                    Log::warning('Failed to generate comment', [
                        'post_id' => $post->id,
                        'reason' => 'Empty comment returned'
                    ]);
                    continue;
                }

                $post->update([
                    'generated_comment' => $comment,
                    'comment_generated_at' => now(),
                    'status' => 'scheduled'
                ]);

                $generatedCount++;
                $this->info("  ✅ Comment generated for post #{$post->id} (" . strlen($comment) . " chars)");
                Log::info('Comment generated successfully', [
                    'post_id' => $post->id,
                    'comment_length' => strlen($comment),
                    'comment_preview' => substr($comment, 0, 100) . '...'
                ]);
            } catch (\Throwable $th) {
                $msg = "  ❌ Error generating comment: " . $th->getMessage();
                $this->error($msg);
                $post->update([
                    'status' => 'failed',
                    'error_message' => $th->getMessage()
                ]);
                Log::error('Error generating comment', [
                    'post_id' => $post->id,
                    'error' => $th->getMessage(),
                    'trace' => $th->getTraceAsString()
                ]);
            }
        }
        
        $this->info("  📊 Comments generated: {$generatedCount}/{$posts->count()}");
        Log::info('Comment generation completed', ['generated' => $generatedCount, 'total' => $posts->count()]);
        
        return $generatedCount;
    }

    /**
     * Schedule and post comments at optimal times
     */
    private function scheduleAndPostComments()
    {
        $this->info('📤 Scheduling and posting comments...');
        Log::info('📤 Scheduling and posting comments...');

        $posts = AutoCommentPost::where('status', 'scheduled')
            ->whereNotNull('generated_comment')
            ->whereHas('preference', function($q) {
                $q->where('is_active', true);
            })
            ->with(['preference', 'user'])
            ->get();

        $this->info("  Found {$posts->count()} scheduled comments");
        Log::info("Found scheduled comments", ['count' => $posts->count()]);

        $stats = [
            'scheduled' => 0,
            'posted' => 0,
            'errors' => 0,
        ];

        foreach ($posts as $post) {
            try {
                // Check if already commented (if preference enabled)
                if ($post->preference->skip_already_commented) {
                    // You could check LinkedIn comments API here to verify
                }

                // Check daily limit
                $todayCount = AutoCommentPost::where('user_id', $post->user_id)
                    ->where('status', 'posted')
                    ->whereDate('posted_at', today())
                    ->count();

                if ($todayCount >= $post->preference->max_comments_per_day) {
                    $msg = "  ⚠️  Daily limit reached for user #{$post->user_id} ({$todayCount}/{$post->preference->max_comments_per_day})";
                    $this->warn($msg);
                    Log::warning('Daily limit reached', [
                        'user_id' => $post->user_id,
                        'current_count' => $todayCount,
                        'limit' => $post->preference->max_comments_per_day
                    ]);
                    continue;
                }

                // Schedule at optimal time
                $optimalTime = $this->calculateOptimalTime($post->preference);
                
                if ($optimalTime->isFuture()) {
                    $post->update(['scheduled_at' => $optimalTime]);
                    $stats['scheduled']++;
                    $msg = "  ⏰ Post #{$post->id} scheduled for: {$optimalTime->format('Y-m-d H:i:s')}";
                    $this->info($msg);
                    Log::info('Comment scheduled', [
                        'post_id' => $post->id,
                        'scheduled_at' => $optimalTime->toDateTimeString(),
                        'user_id' => $post->user_id
                    ]);
                    continue;
                }

                // Post immediately if time has passed
                if ($this->postComment($post)) {
                    $stats['posted']++;
                } else {
                    $stats['errors']++;
                }

            } catch (\Throwable $th) {
                $stats['errors']++;
                $msg = "  ❌ Error scheduling post #{$post->id}: " . $th->getMessage();
                $this->error($msg);
                Log::error('Error scheduling/post comment', [
                    'post_id' => $post->id,
                    'error' => $th->getMessage(),
                    'trace' => $th->getTraceAsString()
                ]);
            }
        }
        
        $this->info("  📊 Scheduled: {$stats['scheduled']}, Posted: {$stats['posted']}, Errors: {$stats['errors']}");
        Log::info('Scheduling and posting completed', $stats);
        
        return $stats;
    }

    /**
     * Post a comment to LinkedIn
     */
    private function postComment($post)
    {
        try {
            $this->info("  📤 Posting comment for post #{$post->id}");
            Log::info('Posting comment to LinkedIn', [
                'post_id' => $post->id,
                'post_urn' => $post->post_urn,
                'user_id' => $post->user_id,
                'comment_preview' => substr($post->generated_comment, 0, 50) . '...'
            ]);

            $user = $post->user;
            if (empty($user->access_token)) {
                throw new \Exception('User has no LinkedIn access token');
            }

            $linkedinService = new LinkedInApiService($user->access_token);
            
            // Get actor URN (user's LinkedIn URN)
            // You may need to store this in user table or fetch from profile
            $actorUrn = $this->getActorUrn($user);
            
            if (!$actorUrn) {
                throw new \Exception('Could not determine actor URN');
            }

            $result = $linkedinService->postComment(
                $post->post_urn,
                $actorUrn,
                $post->generated_comment
            );

            if ($result) {
                $post->update([
                    'status' => 'posted',
                    'posted_at' => now(),
                    'comment_urn' => $result['id'] ?? null
                ]);
                
                $msg = "  ✅ Comment posted successfully! Comment URN: " . ($result['id'] ?? 'N/A');
                $this->info($msg);
                Log::info('✅ COMMENT POSTED SUCCESSFULLY', [
                    'post_id' => $post->id,
                    'post_urn' => $post->post_urn,
                    'comment_urn' => $result['id'] ?? null,
                    'user_id' => $post->user_id,
                    'posted_at' => now()->toDateTimeString()
                ]);
                return true;
            } else {
                throw new \Exception('Failed to post comment via LinkedIn API - no response');
            }
        } catch (\Throwable $th) {
            $msg = "  ❌ Error posting comment: " . $th->getMessage();
            $this->error($msg);
            $post->update([
                'status' => 'failed',
                'error_message' => $th->getMessage()
            ]);
            Log::error('❌ COMMENT POSTING FAILED', [
                'post_id' => $post->id,
                'post_urn' => $post->post_urn,
                'user_id' => $post->user_id,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Build AI comment prompt
     */
    private function buildCommentPrompt($post, $preference)
    {
        $prompt = "Generate a LinkedIn comment for the following post.\n\n";
        $prompt .= "Comment Style: {$preference->comment_style}\n";
        $prompt .= "Comment Tone: {$preference->comment_tone}\n";
        
        if ($preference->comment_instructions) {
            $prompt .= "Specific Instructions: {$preference->comment_instructions}\n";
        }
        
        if ($preference->avoid_topics) {
            $prompt .= "Avoid these topics: {$preference->avoid_topics}\n";
        }
        
        $prompt .= "\nLinkedIn Post:\n{$post->post_content}\n\n";
        $prompt .= "Generate a helpful, engaging comment that adds value to the conversation. ";
        $prompt .= "Keep it authentic and professional.";

        return $prompt;
    }

    /**
     * Calculate optimal posting time
     */
    private function calculateOptimalTime($preference)
    {
        $postingTimes = $preference->posting_times ?? [9, 14, 18];
        $now = Carbon::now();
        
        // Find next available posting time today
        foreach ($postingTimes as $hour) {
            $time = $now->copy()->setTime($hour, 0);
            if ($time->isFuture()) {
                return $time;
            }
        }
        
        // If all times passed, use first time tomorrow
        return $now->copy()->addDay()->setTime($postingTimes[0], 0);
    }

    /**
     * Extract URN from LinkedIn URL
     */
    private function extractUrnFromUrl($url)
    {
        // If already a URN, return as-is
        if (str_starts_with($url, 'urn:li:')) {
            return $url;
        }

        // Extract from URL (this is a simplified version)
        // In production, you'd need to fetch profile and get the URN
        // For now, return null to indicate we need the actual URN
        return null;
    }

    /**
     * Get actor URN for user
     */
    private function getActorUrn($user)
    {
        // You should store LinkedIn URN in user table or fetch from profile
        // This is a placeholder - implement based on your setup
        if ($user->linkedin_id) {
            return "urn:li:person:{$user->linkedin_id}";
        }
        
        return null;
    }
}
