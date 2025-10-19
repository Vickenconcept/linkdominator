<?php

namespace App\Console\Commands;

use App\Models\LinkedInPost;
use App\Models\Integration;
use App\Services\LinkedInService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdatePostAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-post-analytics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update analytics for all published LinkedIn posts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDate = Carbon::now();
        $formatedDate = $currentDate->toDateTimeString();

        Log::info("📊 Analytics updater running at {$formatedDate}");

        // Get all published posts with LinkedIn post IDs
        $publishedPosts = LinkedInPost::where('status', 'published')
            ->whereNotNull('linkedin_post_id')
            ->whereNotNull('published_at')
            ->get();

        if ($publishedPosts->count() === 0) {
            Log::info("📊 No published posts found to update analytics");
            $this->info("No published posts found to update analytics");
            return;
        }

        Log::info("📊 Found {$publishedPosts->count()} published posts to update");

        $linkedinService = new LinkedInService();
        $updatedCount = 0;
        $errorCount = 0;

        foreach ($publishedPosts as $post) {
            try {
                // Get user's LinkedIn integration
                $integration = Integration::where('user_id', $post->user_id)
                    ->where('oauth_provider', 'linkedin')
                    ->where('connected_status', 1)
                    ->first();

                if (!$integration) {
                    Log::warning("📊 No LinkedIn integration found for user {$post->user_id}");
                    continue;
                }

                // Extract LinkedIn post ID from URN format
                $linkedinPostId = $post->linkedin_post_id;
                
                // Handle different URN formats
                if (str_starts_with($linkedinPostId, 'urn:li:share:')) {
                    $linkedinPostId = str_replace('urn:li:share:', '', $linkedinPostId);
                } elseif (str_starts_with($linkedinPostId, 'urn:li:ugcPost:')) {
                    $linkedinPostId = str_replace('urn:li:ugcPost:', '', $linkedinPostId);
                }
                
                // If it's still a URN, try to extract the numeric part
                if (str_contains($linkedinPostId, ':')) {
                    $parts = explode(':', $linkedinPostId);
                    $linkedinPostId = end($parts);
                }

                Log::info("📊 Updating analytics for post {$post->id}", [
                    'linkedin_post_id' => $linkedinPostId,
                    'user_id' => $post->user_id
                ]);

                // Fetch analytics from LinkedIn
                $analytics = $linkedinService->fetchPostAnalytics($linkedinPostId, $integration->access_token);

                if ($analytics) {
                    // Update post with new analytics
                    $post->updateAnalytics($analytics);
                    $updatedCount++;
                    
                    Log::info("✅ Updated analytics for post {$post->id}", [
                        'likes' => $analytics['likes'],
                        'comments' => $analytics['comments'],
                        'shares' => $analytics['shares'],
                        'views' => $analytics['views']
                    ]);
                    
                    $this->info("✅ Updated post {$post->id} - Likes: {$analytics['likes']}, Comments: {$analytics['comments']}, Shares: {$analytics['shares']}, Views: {$analytics['views']}");
                } else {
                    // LinkedIn Analytics API is not available - provide manual input option
                    Log::warning("⚠️ LinkedIn Analytics API not available for post {$post->id}");
                    
                    if ($this->confirm("Would you like to manually enter analytics for post {$post->id}?", false)) {
                        $likes = $this->ask('Enter number of likes:', 0);
                        $comments = $this->ask('Enter number of comments:', 0);
                        $shares = $this->ask('Enter number of shares:', 0);
                        $views = $this->ask('Enter number of views:', 0);
                        
                        $manualAnalytics = [
                            'likes' => (int)$likes,
                            'comments' => (int)$comments,
                            'shares' => (int)$shares,
                            'views' => (int)$views,
                            'clicks' => 0,
                            'last_updated' => now()->toISOString(),
                            'source' => 'manual'
                        ];
                        
                        $post->updateAnalytics($manualAnalytics);
                        $updatedCount++;
                        
                        $this->info("✅ Manually updated post {$post->id} analytics");
                    }
                    
                    $errorCount++;
                }

                // Add small delay to avoid rate limiting
                usleep(500000); // 0.5 seconds

            } catch (\Exception $e) {
                Log::error("❌ Error updating analytics for post {$post->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $errorCount++;
            }
        }

        Log::info("📊 Analytics update completed", [
            'total_posts' => $publishedPosts->count(),
            'updated' => $updatedCount,
            'errors' => $errorCount
        ]);

        $this->info("📊 Analytics update completed: {$updatedCount} updated, {$errorCount} errors");
    }
}
