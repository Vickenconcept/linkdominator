<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CommentFeedCampaign;
use App\Models\CommentFeedCampaignPost;
use App\Services\RapidApi;
use App\Services\ChatGPT;
use App\Services\RapidApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FetchLinkedinFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-linkedin-feeds
                            {--user= : Fetch for specific user ID only}
                            {--limit=50 : Maximum posts to fetch per user}
                            {--system-wide : Fetch once and share with all users (saves API calls)}
                            {--keywords=5 : Number of keywords to search per user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch viral LinkedIn posts based on user preferences. Supports multi-user optimization.';

    // Track API requests
    private $apiRequestCount = 0;
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = now();
        $this->info("Started fetching linkedin post...");
        $this->info("Timestamp: " . $startTime->format('Y-m-d H:i:s'));
        $this->newLine();
        
        // First, fetch viral posts for inspiration library
        $this->fetchViralPostsForInspiration();
        
        // Then, check for comment campaign available
        $comment_campaign = new CommentFeedCampaign;

        $todayDate = Carbon::now()->toDateString();

        $query = "
            select a.*, b.access_token 
            from comment_feed_campaigns a 
            join users b on a.user_id = b.id
            where a.status in ('ongoing','processed') or a.total_post_found = 0;
        ";
        $campaigns = DB::select($query);

        $rapidapi_service = new RapidApiService;

        foreach ($campaigns as $item) {
            $itemUpdatedDate = date_format(date_create($item->updated_at), "Y-m-d");

            if(($item->total_post_found == 0 && $itemUpdatedDate == $todayDate) || $itemUpdatedDate < $todayDate)
            {
                if($item->campaign_type == 'keyword' && $item->keyword_list){
                    $item->keyword_list = str_replace("\r\n"," ",$item->keyword_list);

                    // Use engagement filters to save API credits
                    // Only fetch posts that already meet minimum engagement
                    $filters = [
                        'min_likes' => 50, // Minimum engagement threshold
                        'limit' => 100 // Limit results to save credits
                    ];
                    
                    $linkedin_posts = $rapidapi_service->search_posts($item->keyword_list, 1, 'past-month', $filters);
                    $linkedin_posts = $linkedin_posts['data'] ?? [];
                    $linkedin_posts = array_values(array_filter($linkedin_posts, function ($post) {
                        return $this->postWithinDateRange($post, 'past-month');
                    }));
                    $linkedin_posts = array_map(function ($post) {
                        return is_array($post) ? (object) $post : $post;
                    }, $linkedin_posts);

                }else if($item->campaign_type == 'profile' && $item->profile_list){
                    $item->profile_list = explode("\r\n", $item->profile_list);
                    $linkedin_posts = [];

                    foreach ($item->profile_list as $p_url) {
                        $profile_posts = $rapidapi_service->fetch_profile_posts($p_url);
                        $post_data = $profile_posts['data'];

                        foreach ($post_data as $li_post) {
                            if(property_exists($li_post, 'poster')){
                                array_push($linkedin_posts, [
                                    'post_type' => 'document',
                                    'post_url' => $li_post['post_url'],
                                    'posted' => $li_post['posted'],
                                    'poster_linkedin_url' => $li_post['poster_linkedin_url'],
                                    'poster_name' => str_replace("'", "\\'", $li_post['poster']['first']) .' '. str_replace("'", "\\'", $li_post['poster']['last']),
                                    'poster_title' => $li_post['poster']['headline'],
                                    'text' => $li_post['text'],
                                    'urn' => $li_post['urn']  
                                ]); 
                            }
                        }
                    }
                }

                if(count($linkedin_posts)>0){
                    $prompt = "
                        Generate a linkedin comment for a linkedin post. 
                        The comment should be constructed under the following rules,
                        comment style: $item->ai_comment_style
                        comment type: $item->ai_comment_type comment
                        product name and description: $item->product_name_description
                        product unique selling proposition: $item->product_unique_selling_point
                        persona description: $item->persona_description
                        actions to take: $item->what_ai_need_todo
                        actions to avoid: $item->what_ai_should_avoid
                        tone and style: $item->tone_style  
                    ";

                    $comment_campaign_post = new CommentFeedCampaignPost;

                    foreach ($linkedin_posts as $post) {
                        $gpt_comment = '';

                        if ($item->ai_commenter == 'common'){
                            // Get comment for each post
                            $prompt .= "here is the linkedin post: $post->text";

                            try {
                                $chatgpt = new ChatGPT;
                                $chatgpt->checkModeration($prompt);
                                $gpt_comment = $chatgpt->generateContent($prompt)['content'];
                            } catch (\Throwable $th) {
                                //throw $th;
                                $gpt_comment = '';
                            }
                        }else if($item->ai_commenter == 'custom' && $item->custom_webhook && $item->access_token){
                            // Post to webhook if set
                            $payload = [
                                "post_type" => $post->post_type,
                                "post_url" => $post->post_url,
                                "posted" => $post->posted,
                                "poster_linkedin_url" => $post->poster_linkedin_url,
                                "poster_name" => $post->poster_name,
                                "poster_title" => $post->poster_title,
                                "text" => $post->text,
                                "urn" => $post->urn
                            ];

                            $headers = [
                                "X-Api-Key" => $item->access_token
                            ];

                            Http::asForm()
                                ->withHeaders($headers)
                                ->post($item->custom_webhook, $payload)
                                ->throw()
                                ->json();
                        }

                        // Create post in DB
                        // Check if same post urn exist in campaign
                        $query = sprintf("select campaign_id, urn from comment_feed_campaign_post where campaign_id=%s and urn='%s'", $item->id, $post->urn);
                        $data = DB::select($query);

                        if(count($data)>0){
                            $comment_campaign_post->create([
                                'campaign_id' => $item->id, 
                                'num_comments' => null, 
                                'num_likes' => null, 
                                'num_shares' => null, 
                                'post_type' => $post->post_type, 
                                'post_url' => $post->post_url, 
                                'posted' => $post->posted, 
                                'poster_linkedin_url' => $post->poster_linkedin_url, 
                                'poster_name' => $post->poster_name, 
                                'poster_title' => str_replace("'", "\\'", $post->poster_title), 
                                'post' => str_replace("'", "\\'", $post->text), 
                                'urn' => $post->urn, 
                                'comment' => str_replace("'", "\\'", $gpt_comment)
                            ]);
                        }

                        $comment_campaign->where('id', $item->id)
                            ->update([
                                'total_post_found' => count($linkedin_posts),
                                'status' => 'processed'
                            ]);

                        $this->info('Post created successfully.');
                    }
                }else{
                    $this->info('No post found.');
                }
            }else{
                $this->info('No comment campaign available.');
            }
        }
        $this->newLine();
        $this->info('═══════════════════════════════════════════════');
        $this->info('📊 API USAGE SUMMARY:');
        $this->info('   API Requests Made: ' . $this->apiRequestCount);
        $this->info('   Estimated Monthly Usage: ' . ($this->apiRequestCount * 30) . ' (if run daily)');
        $this->info('   Time Elapsed: ' . now()->diffInSeconds($startTime) . ' seconds');
        $this->info('═══════════════════════════════════════════════');
        $this->info('Completed fetching linkedin post.');
    }

    /**
     * Fetch viral posts for inspiration library
     * 
     * INTELLIGENT, DOMAIN-AGNOSTIC APPROACH:
     * 
     * 1. Load user preferences (or use intelligent defaults)
     * 2. Dynamic keyword generation from user's:
     *    - Industries (tech, healthcare, real estate, etc.)
     *    - Topics (AI, marketing, sales, leadership, etc.)
     *    - Custom keywords
     * 3. Multi-page search for better results
     * 4. Optional: Fetch from user's favorite creators
     * 5. Adjustable viral threshold per user
     * 
     * Works for ANY niche/industry - not hardcoded!
     * 
     * OPTIMIZATION FOR MULTIPLE USERS:
     * - Use --limit to cap posts per user
     * - Use --keywords to limit searches per user
     * - Use --system-wide to fetch once and share (saves API calls)
     * - Use --user=X to fetch for specific user only
     */
    private function fetchViralPostsForInspiration()
    {
        $this->info("🔍 Fetching viral posts - Intelligent & Domain-Agnostic");
        $this->newLine();
        
        $rapidapi_service = new RapidApiService;
        $totalFetched = 0;
        
        // Check if system-wide mode (fetch once, share with all users)
        if ($this->option('system-wide')) {
            $this->info("🌐 SYSTEM-WIDE MODE: Fetching once and sharing with all users");
            $this->info("   This saves API calls for multi-user environments");
            $this->newLine();
            $totalFetched += $this->fetchSystemWide($rapidapi_service);
        } else {
            // Get all users with preferences (or use system defaults)
            $users = $this->getUsersToFetchFor();
            
            if ($users->isEmpty()) {
                $this->warn("No users with preferences found. Using system defaults.");
                $this->info("💡 Tip: Use --system-wide flag to fetch once for all users");
                $this->newLine();
                $totalFetched += $this->fetchWithDefaults($rapidapi_service);
            } else {
                $userCount = $users->count();
                $this->info("👥 Found {$userCount} users with preferences");
                $this->info("📊 Limits: " . $this->option('limit') . " posts/user, " . $this->option('keywords') . " keywords/user");
                $this->newLine();
                
                foreach ($users as $user) {
                    $this->info("📍 Fetching for: {$user->name}");
                    $totalFetched += $this->fetchForUser($user, $rapidapi_service);
                    $this->newLine();
                }
            }
        }
        
        $this->info("═══════════════════════════════════════════════");
        if ($totalFetched > 0) {
            $this->info("✅ Total Fetched: {$totalFetched} posts");
        } else {
            $this->warn("⚠️  No qualifying posts found");
            $this->info("💡 Users can update preferences at /inspiration");
        }
        $this->info("═══════════════════════════════════════════════");
    }
    
    /**
     * Fetch system-wide library (efficient for many users)
     * Fetches once and makes available to all users
     */
    private function fetchSystemWide($rapidapi_service)
    {
        $this->info("📍 Fetching system-wide viral library");
        $this->info("   All users will be able to browse and filter these posts");
        $this->newLine();
        
        // Comprehensive keywords covering major industries
        $systemKeywords = [
            'entrepreneurship', 'business strategy', 'startup',
            'leadership', 'management', 'team building',
            'marketing', 'sales', 'branding',
            'technology', 'AI', 'software',
            'career', 'professional development',
            'productivity', 'innovation',
        ];
        
        $limit = $this->option('limit');
        $this->info("  Target: {$limit} total posts across all categories");
        
        return $this->searchKeywordsWithMultiplePages(
            $systemKeywords, 
            1, // user_id = 1 (system-wide)
            100, // minimum 100 likes
            'past-month',
            $rapidapi_service,
            $limit
        );
    }
    
    /**
     * Get users to fetch viral posts for
     */
    private function getUsersToFetchFor()
    {
        // If specific user ID provided, fetch for that user only
        if ($this->option('user')) {
            return \App\Models\User::where('id', $this->option('user'))->get();
        }
        
        // Otherwise fetch for all users with preferences
        return \App\Models\User::whereHas('contentPreferences')->get();
    }
    
    /**
     * Fetch viral posts for a specific user based on their preferences
     */
    private function fetchForUser($user, $rapidapi_service)
    {
        $preferences = $user->contentPreferences ?? \App\Models\UserContentPreference::make(\App\Models\UserContentPreference::getDefaults());
        
        $this->info("  Industries: " . implode(', ', $preferences->industries ?? []));
        $this->info("  Topics: " . implode(', ', $preferences->topics ?? []));
        $this->info("  Min Engagement (slider): {$preferences->min_engagement} likes");
        
        // Smart fetch: optionally relax the effective threshold used during fetching
        $effectiveMinEngagement = $preferences->min_engagement;
        if ($preferences->smart_fetch ?? false) {
            // Never go below 50 (the UI minimum), and use half the slider value
            $effectiveMinEngagement = max(50, (int) floor($effectiveMinEngagement / 2));
            $this->info("  Smart Fetch: ON → Effective threshold for fetching: {$effectiveMinEngagement} likes");
        } else {
            $this->info("  Smart Fetch: OFF → Effective threshold for fetching: {$effectiveMinEngagement} likes");
        }
        
        $this->info("  Date Range: " . ($preferences->date_range ?? 'past-month'));
        $this->newLine();
        
        // Store effective threshold on the preferences object for downstream methods
        $preferences->effective_min_engagement = $effectiveMinEngagement;
        
        $totalFetched = 0;
        
        // Fetch from user's favorite creators (if enabled)
        if ($preferences->fetch_from_creators && !empty($preferences->favorite_creators)) {
            $totalFetched += $this->fetchFromUserCreators($user, $preferences, $rapidapi_service);
        }
        
        // Fetch from keywords (industries + topics + custom keywords)
        if ($preferences->fetch_from_keywords) {
            $totalFetched += $this->fetchFromUserKeywords($user, $preferences, $rapidapi_service);
        }
        
        return $totalFetched;
    }
    
    /**
     * Fetch using intelligent system defaults (domain-agnostic)
     */
    private function fetchWithDefaults($rapidapi_service)
    {
        $this->info("📍 Using System Defaults - Broad Industry Coverage");
        $this->newLine();
        
        // Broad, diverse keywords covering multiple industries
        $keywords = [
            // Business & Entrepreneurship
            'entrepreneurship', 'startup', 'business growth',
            
            // Technology
            'artificial intelligence', 'technology trends', 'software development',
            
            // Marketing & Sales  
            'digital marketing', 'content strategy', 'sales techniques',
            
            // Leadership & Career
            'leadership', 'career advice', 'professional development',
            
            // Industry-Specific
            'real estate investing', 'healthcare innovation', 'financial planning',
            'e-commerce', 'SaaS', 'consulting',
            
            // Skills & Growth
            'productivity', 'personal branding', 'networking',
        ];
        
        return $this->searchKeywordsWithMultiplePages($keywords, 1, 100, 'past-month', $rapidapi_service);
    }
    
    /**
     * Fetch posts from user's favorite creators
     */
    private function fetchFromUserCreators($user, $preferences, $rapidapi_service)
    {
        $this->info("  🎯 Fetching from favorite creators...");
        
        $totalFetched = 0;
        
        foreach ($preferences->favorite_creators as $creatorUrl) {
            try {
                $response = $rapidapi_service->fetch_profile_posts($creatorUrl);
                $this->apiRequestCount++; // Track API usage
                
                if (isset($response['data']) && is_array($response['data'])) {
                    foreach ($response['data'] as $postData) {
                        $post = $postData['post'] ?? $postData;
                        
                        // Check duplicate
                        $existingPost = \App\Models\ViralPost::where('user_id', $user->id)
                            ->where(function($query) use ($post) {
                                $query->where('linkedin_post_id', $post['urn'] ?? null)
                                      ->orWhere('post_url', $post['post_url'] ?? null);
                            })
                            ->first();
                            
                        if ($existingPost) continue;
                        
                        // Use effective threshold (may be relaxed if Smart Fetch is enabled)
                        $threshold = $preferences->effective_min_engagement ?? $preferences->min_engagement;
                        if ($this->meetsThreshold($post, $threshold)) {
                            $this->saveViralPost($post, $user->id);
                            $totalFetched++;
                        }
                    }
                }
                
                sleep(2);
                
            } catch (\Exception $e) {
                $this->error("  Error fetching creator: " . $e->getMessage());
            }
        }
        
        $this->info("  ✓ Fetched {$totalFetched} posts from creators");
        return $totalFetched;
    }
    
    /**
     * Fetch posts from user's keywords (industries + topics + custom)
     */
    private function fetchFromUserKeywords($user, $preferences, $rapidapi_service)
    {
        $this->info("  🔍 Fetching from user keywords...");
        
        $keywords = $preferences->getAllKeywords();
        
        if (empty($keywords)) {
            $this->warn("  No keywords defined for user");
            return 0;
        }
        
        $this->info("  Keywords: " . implode(', ', array_slice($keywords, 0, 5)) . (count($keywords) > 5 ? '...' : ''));
        
        $effectiveMinEngagement = $preferences->effective_min_engagement ?? $preferences->min_engagement;
        
        return $this->searchKeywordsWithMultiplePages(
            $keywords, 
            $user->id, 
            $effectiveMinEngagement,
            $preferences->date_range ?? 'past-month',
            $rapidapi_service
        );
    }
    
    /**
     * Search multiple keywords with multi-page support for better results
     */
    private function searchKeywordsWithMultiplePages($keywords, $userId, $minEngagement, $dateRange, $rapidapi_service, $maxPosts = null)
    {
        $totalFetched = 0;
        $maxPosts = $maxPosts ?? $this->option('limit') ?? 50; // Default 50 posts per user
        $maxKeywords = $this->option('keywords') ?? 5; // Default 5 keywords (saves API calls)
        $pagesPerKeyword = 2; // Search 2 pages per keyword for more results
        
        $limitedKeywords = array_slice($keywords, 0, $maxKeywords);
        
        foreach ($limitedKeywords as $keyword) {
            // Check if we've hit the limit
            if ($totalFetched >= $maxPosts) {
                $this->info("  ✓ Reached limit of {$maxPosts} posts. Stopping search.");
                break;
            }
            
            try {
                // Search multiple pages for each keyword
                for ($page = 1; $page <= $pagesPerKeyword; $page++) {
                    // Check limit again
                    if ($totalFetched >= $maxPosts) break;
                    
                    // Use filters to reduce API calls - only fetch posts that meet engagement threshold
                    $filters = [
                        'min_likes' => $minEngagement,
                        'limit' => 100 // Limit to 100 posts per page to save credits
                    ];
                    
                    $response = $rapidapi_service->search_posts($keyword, $page, $dateRange, $filters);
                    $this->apiRequestCount++; // Track API usage
                    
                    if (isset($response['data']) && is_array($response['data'])) {
                        $found = 0;
                        $checked = 0;
                        $duplicates = 0;
                        $belowThreshold = 0;
                        $outsideDateRange = 0;
                        
                        foreach ($response['data'] as $postData) {
                            // Check limit
                            if ($totalFetched >= $maxPosts) break;
                            
                            $post = $postData['post'] ?? $postData;
                            $checked++;
                            
                            // Check duplicate
                            $existingPost = \App\Models\ViralPost::where('user_id', $userId)
                                ->where(function($query) use ($post) {
                                    $query->where('linkedin_post_id', $post['urn'] ?? null)
                                          ->orWhere('post_url', $post['post_url'] ?? null);
                                })
                                ->first();
                                
                            if ($existingPost) {
                                $duplicates++;
                                continue;
                            }
                            
                            // Debug: Log engagement (first 3 posts only)
                            $likes = $post['num_likes'] ?? 0;
                            if ($checked <= 3 && $totalFetched == 0) {
                                Log::info("Post engagement check:", [
                                    'keyword' => $keyword,
                                    'likes' => $likes,
                                    'comments' => $post['num_comments'] ?? 0,
                                    'threshold' => $minEngagement
                                ]);
                            }
                            
                            // Respect user-selected date range (RapidAPI no longer filters this server-side)
                            if (!$this->postWithinDateRange($post, $dateRange)) {
                                $outsideDateRange++;
                                continue;
                            }

                            // Use custom threshold
                            if ($this->meetsThreshold($post, $minEngagement)) {
                                $this->saveViralPost($post, $userId);
                                $totalFetched++;
                                $found++;
                            } else {
                                $belowThreshold++;
                            }
                        }
                        
                        if ($found > 0) {
                            $this->info("  ✓ '{$keyword}' (page {$page}): Found {$found} posts (total: {$totalFetched}/{$maxPosts})");
                        } else if ($checked > 0) {
                            $this->info("  ○ '{$keyword}' (page {$page}): 0 qualified (below threshold: {$belowThreshold}, outside range: {$outsideDateRange}, duplicates: {$duplicates})");
                        }
                    }
                    
                    sleep(1); // Rate limiting between pages
                }
                
            } catch (\Exception $e) {
                $this->error("  Error with keyword '{$keyword}': " . $e->getMessage());
            }
        }
        
        $this->info("  ✓ Total from keywords: {$totalFetched} posts");
        return $totalFetched;
    }
    
    /**
     * Determine if a post falls within the requested date range.
     * RapidAPI stopped honoring date_posted filters, so we enforce them client-side.
     */
    private function postWithinDateRange($post, ?string $dateRange): bool
    {
        if (empty($dateRange) || $dateRange === 'any-time') {
            return true;
        }

        $postedAt = null;

        if (is_array($post)) {
            $postedAt = $post['posted'] ?? ($post['post']['posted'] ?? null);
        } elseif (is_object($post)) {
            $postedAt = $post->posted ?? ($post->post->posted ?? null);
        }

        if (!$postedAt) {
            return false;
        }

        try {
            $postDate = Carbon::parse($postedAt);
        } catch (\Throwable $th) {
            return false;
        }

        $cutoff = match ($dateRange) {
            'past-24-hours' => now()->subDay(),
            'past-week' => now()->subWeek(),
            'past-month' => now()->subMonth(),
            'past-year' => now()->subYear(),
            default => null,
        };

        if ($cutoff === null) {
            return true;
        }

        return $postDate->greaterThanOrEqualTo($cutoff);
    }
    
    /**
     * Save a viral post to database
     */
    private function saveViralPost($post, $userId = 1)
    {
        // Determine post type
        $postType = $post['post_type'] ?? 'text';
        $allowedTypes = ['text', 'image', 'carousel', 'video', 'article'];
        
        if (!in_array($postType, $allowedTypes)) {
            // Auto-detect type
            if (!empty($post['video'])) {
                $postType = 'video';
            } elseif (!empty($post['images']) && count($post['images']) > 1) {
                $postType = 'carousel';
            } elseif (!empty($post['images'])) {
                $postType = 'image';
            } else {
                $postType = 'text';
            }
        }
        
        // Extract author name - handle multiple API response structures
        $authorName = 'Unknown';
        if (isset($post['poster_name'])) {
            $authorName = $post['poster_name'];
        } elseif (isset($post['poster']['first']) && isset($post['poster']['last'])) {
            $authorName = $post['poster']['first'] . ' ' . $post['poster']['last'];
        } elseif (isset($post['poster_linkedin_url'])) {
            // Extract from URL as last resort
            $authorName = $this->extractNameFromUrl($post['poster_linkedin_url']);
        }
        
        \App\Models\ViralPost::create([
            'user_id' => $userId, // User-specific or system-wide (1)
            'author_name' => $authorName,
            'author_headline' => $post['poster_title'] ?? $post['poster']['headline'] ?? '',
            'author_profile_url' => $post['poster_linkedin_url'] ?? '',
            'content' => $post['text'] ?? '',
            'post_url' => $post['post_url'] ?? '',
            'linkedin_post_id' => $post['urn'] ?? '',
            'likes' => $post['num_likes'] ?? 0,
            'comments' => $post['num_comments'] ?? 0,
            'shares' => $post['num_shares'] ?? 0,
            'views' => $post['num_views'] ?? 0,
            'engagement_rate' => $this->calculateEngagementRate($post),
            'post_type' => $postType,
            'post_date' => $post['posted'] ?? now(),
            'category' => $this->autoCategorize($post['text'] ?? ''),
            'saved_at' => now()
        ]);
    }
    
    /**
     * Check if post meets the engagement threshold
     * 
     * With date_range set to past-month, posts have had 2-4 weeks to accumulate likes
     * So we can use the actual user threshold (100+ likes)
     */
    private function meetsThreshold($post, $minEngagement = 100)
    {
        $likes = $post['num_likes'] ?? 0;
        $comments = $post['num_comments'] ?? 0;
        $shares = $post['num_shares'] ?? 0;
        $views = $post['num_views'] ?? 0;
        
        $totalEngagement = $likes + $comments + $shares;
        $engagementRate = $views > 0 ? ($totalEngagement / $views) * 100 : 0;
        
        // Use actual threshold since posts are from 2-4 weeks ago (had time to get engagement)
        return $likes >= $minEngagement 
            || $engagementRate >= 5.0  // Exceptional engagement rate
            || $comments >= max(20, $minEngagement / 5)  // Active discussion
            || $shares >= max(10, $minEngagement / 10)  // Valuable content being shared
            || ($totalEngagement >= $minEngagement && $engagementRate >= 3.0); // Good combined metrics
    }
    
    
    /**
     * Calculate engagement rate
     */
    private function calculateEngagementRate($post)
    {
        $likes = $post['num_likes'] ?? 0;
        $comments = $post['num_comments'] ?? 0;
        $shares = $post['num_shares'] ?? 0;
        $views = $post['num_views'] ?? 0;
        
        $totalEngagement = $likes + $comments + $shares;
        
        return $views > 0 ? round(($totalEngagement / $views) * 100, 2) : 0;
    }
    
    /**
     * Auto-categorize post content
     */
    private function autoCategorize($content)
    {
        $content = strtolower($content);
        
        if (str_contains($content, 'entrepreneur') || str_contains($content, 'startup') || str_contains($content, 'business')) {
            return 'Business';
        } elseif (str_contains($content, 'leadership') || str_contains($content, 'management') || str_contains($content, 'team')) {
            return 'Leadership';
        } elseif (str_contains($content, 'marketing') || str_contains($content, 'sales') || str_contains($content, 'brand')) {
            return 'Marketing';
        } elseif (str_contains($content, 'career') || str_contains($content, 'job') || str_contains($content, 'work')) {
            return 'Career';
        } elseif (str_contains($content, 'motivation') || str_contains($content, 'inspiration') || str_contains($content, 'success')) {
            return 'Motivation';
        } else {
            return 'General';
        }
    }
    
    /**
     * Extract name from LinkedIn URL as fallback
     * Example: linkedin.com/in/simonsinek -> Simon Sinek
     */
    private function extractNameFromUrl($url)
    {
        // Extract username from URL
        preg_match('/linkedin\.com\/in\/([^\/\?]+)/', $url, $matches);
        
        if (isset($matches[1])) {
            $username = $matches[1];
            
            // Convert camelCase or snake_case to Title Case
            // garyvaynerchuk -> Gary Vaynerchuk
            // simon-sinek -> Simon Sinek
            $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $username); // camelCase
            $name = str_replace(['-', '_'], ' ', $name); // dashes/underscores
            $name = ucwords($name); // Title Case
            
            return $name;
        }
        
        return 'Unknown';
    }
}

