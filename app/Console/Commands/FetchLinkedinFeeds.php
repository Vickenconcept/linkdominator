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
    protected $signature = 'app:fetch-linkedin-feeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Started fetching linkedin post...");
        
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

                    $linkedin_posts = $rapidapi_service->search_posts($item->keyword_list);
                    $linkedin_posts = $linkedin_posts['data'];

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
        $this->info('Completed fetching linkedin post.');
    }

    /**
     * Fetch viral posts for inspiration library
     * 
     * HYBRID APPROACH (3 Sources):
     * 
     * 1. TRULY VIRAL (1000+ likes): Fetch from popular LinkedIn creators
     *    - Gary Vaynerchuk, Simon Sinek, Justin Welsh, etc.
     *    - These consistently get 1000+ likes
     * 
     * 2. HIGH-PERFORMING (100+ likes): Keyword searches
     *    - Recent posts with strong engagement
     *    - Good inspiration even if not "viral"
     * 
     * 3. PERSONAL CURATION: Chrome extension (already built)
     *    - Users save posts they find interesting
     *    - Most personalized approach
     */
    private function fetchViralPostsForInspiration()
    {
        $this->info("🔍 Fetching viral posts for inspiration library...");
        $this->info("   Strategy: Hybrid (Popular Creators + Keyword Search)");
        $this->newLine();
        
        $rapidapi_service = new RapidApiService;
        $totalFetched = 0;
        
        // PART 1: Fetch from popular creators (TRULY VIRAL - 1000+ likes)
        $totalFetched += $this->fetchFromPopularCreators($rapidapi_service);
        
        // PART 2: Fetch from keyword searches (HIGH-PERFORMING - 100+ likes)
        $totalFetched += $this->fetchFromKeywords($rapidapi_service);
        
        $this->newLine();
        $this->info("═══════════════════════════════════════════════");
        if ($totalFetched > 0) {
            $this->info("✅ Total Fetched: {$totalFetched} posts for inspiration library");
        } else {
            $this->warn("⚠️  No qualifying posts found");
            $this->info("💡 Tip: Users can save posts manually via Chrome extension");
        }
        $this->info("═══════════════════════════════════════════════");
    }
    
    /**
     * Fetch posts from popular LinkedIn creators (1000+ likes guaranteed)
     */
    private function fetchFromPopularCreators($rapidapi_service)
    {
        $this->info("📍 PART 1: Fetching from Popular Creators (Truly Viral Content)");
        $this->info("   Threshold: 1000+ likes OR 10%+ engagement");
        $this->newLine();
        
        // Popular LinkedIn creators who consistently get viral engagement
        $popularCreators = [
            // Business & Entrepreneurship
            ['url' => 'https://www.linkedin.com/in/garyvaynerchuk', 'name' => 'Gary Vaynerchuk'],
            ['url' => 'https://www.linkedin.com/in/simonsinek', 'name' => 'Simon Sinek'],
            ['url' => 'https://www.linkedin.com/in/justinwelsh', 'name' => 'Justin Welsh'],
            
            // Marketing & Sales
            ['url' => 'https://www.linkedin.com/in/neilpatel', 'name' => 'Neil Patel'],
            ['url' => 'https://www.linkedin.com/in/randfish', 'name' => 'Rand Fishkin'],
            
            // Leadership & Career
            ['url' => 'https://www.linkedin.com/in/adamposajenichyberboard', 'name' => 'Adam Posner'],
            ['url' => 'https://www.linkedin.com/in/briankdavis', 'name' => 'Brian K. Davis'],
            
            // Add more as needed - these are examples
        ];
        
        $totalFetched = 0;
        
        foreach ($popularCreators as $creator) {
            try {
                $this->info("Fetching from: {$creator['name']}");
                
                $response = $rapidapi_service->fetch_profile_posts($creator['url']);
                
                if (isset($response['data']) && is_array($response['data'])) {
                    $postCount = count($response['data']);
                    $this->info("  Found {$postCount} posts");
                    
                    $viralCount = 0;
                    $duplicateCount = 0;
                    $nonViralCount = 0;
                    
                    foreach ($response['data'] as $postData) {
                        $post = $postData['post'] ?? $postData;
                        
                        // Check if already exists
                        $existingPost = \App\Models\ViralPost::where('linkedin_post_id', $post['urn'] ?? null)
                            ->orWhere('post_url', $post['post_url'] ?? null)
                            ->first();
                            
                        if ($existingPost) {
                            $duplicateCount++;
                            continue;
                        }
                        
                        // Use STRICT viral criteria for popular creators
                        if ($this->isTrulyViral($post)) {
                            $viralCount++;
                            $this->saveViralPost($post);
                            $totalFetched++;
                        } else {
                            $nonViralCount++;
                        }
                    }
                    
                    if ($viralCount > 0) {
                        $this->info("  ✅ Viral: {$viralCount}, Non-viral: {$nonViralCount}, Duplicates: {$duplicateCount}");
                    } else {
                        $this->info("  ⚪ Viral: 0, Non-viral: {$nonViralCount}, Duplicates: {$duplicateCount}");
                    }
                } else {
                    $this->warn("  No data in response");
                }
                
                sleep(2); // Rate limiting
                
            } catch (\Exception $e) {
                $this->error("Error fetching from '{$creator['name']}': " . $e->getMessage());
            }
        }
        
        $this->newLine();
        $this->info("📊 Popular Creators: Fetched {$totalFetched} truly viral posts");
        $this->newLine();
        
        return $totalFetched;
    }
    
    /**
     * Fetch posts from keyword searches (100+ likes - high performing)
     */
    private function fetchFromKeywords($rapidapi_service)
    {
        $this->info("📍 PART 2: Fetching from Keyword Searches (High-Performing Content)");
        $this->info("   Threshold: 100+ likes OR 5%+ engagement");
        $this->newLine();
        
        // Fewer, more targeted keywords (to save API calls)
        $viralKeywords = [
            'entrepreneurship',
            'leadership',
            'digital marketing',
            'career growth',
            'AI artificial intelligence',
            'startup',
            'productivity',
            'business strategy',
        ];
        
        $totalFetched = 0;
        
        foreach ($viralKeywords as $keyword) {
            try {
                $this->info("Searching for: {$keyword}");
                
                $response = $rapidapi_service->search_posts($keyword);
                
                if (isset($response['data']) && is_array($response['data'])) {
                    $postCount = count($response['data']);
                    $this->info("  Found {$postCount} posts");
                    
                    $highPerformingCount = 0;
                    $duplicateCount = 0;
                    $lowPerformingCount = 0;
                    
                    foreach ($response['data'] as $postData) {
                        $post = $postData['post'] ?? $postData;
                        
                        // Check if already exists
                        $existingPost = \App\Models\ViralPost::where('linkedin_post_id', $post['urn'] ?? null)
                            ->orWhere('post_url', $post['post_url'] ?? null)
                            ->first();
                            
                        if ($existingPost) {
                            $duplicateCount++;
                            continue;
                        }
                        
                        // Use RELAXED criteria for keyword searches (100+ likes)
                        if ($this->isHighPerforming($post)) {
                            $highPerformingCount++;
                            $this->saveViralPost($post);
                            $totalFetched++;
                        } else {
                            $lowPerformingCount++;
                        }
                    }
                    
                    if ($highPerformingCount > 0) {
                        $this->info("  ✅ High-performing: {$highPerformingCount}, Low: {$lowPerformingCount}, Duplicates: {$duplicateCount}");
                    } else {
                        $this->info("  ⚪ High-performing: 0, Low: {$lowPerformingCount}, Duplicates: {$duplicateCount}");
                    }
                } else {
                    $this->warn("  No data in response");
                }
                
                sleep(2); // Rate limiting
                
            } catch (\Exception $e) {
                $this->error("Error fetching posts for keyword '{$keyword}': " . $e->getMessage());
            }
        }
        
        $this->newLine();
        $this->info("📊 Keyword Searches: Fetched {$totalFetched} high-performing posts");
        
        return $totalFetched;
    }
    
    /**
     * Save a viral post to database
     */
    private function saveViralPost($post)
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
            // Extract from URL as last resort: linkedin.com/in/simonsinek -> Simon Sinek
            $authorName = $this->extractNameFromUrl($post['poster_linkedin_url']);
        }
        
        \App\Models\ViralPost::create([
            'user_id' => 1, // System-wide library
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
     * TWO-TIER VIRAL DETECTION SYSTEM
     * 
     * Tier 1: TRULY VIRAL (for popular creators)
     * Tier 2: HIGH-PERFORMING (for keyword searches)
     */
    
    /**
     * Check if a post is TRULY VIRAL (1000+ likes)
     * Used for popular creator posts
     */
    private function isTrulyViral($post)
    {
        $likes = $post['num_likes'] ?? 0;
        $comments = $post['num_comments'] ?? 0;
        $shares = $post['num_shares'] ?? 0;
        $views = $post['num_views'] ?? 0;
        
        $totalEngagement = $likes + $comments + $shares;
        $engagementRate = $views > 0 ? ($totalEngagement / $views) * 100 : 0;
        
        // STRICT CRITERIA for truly viral content:
        // 1. 1000+ likes (proven viral)
        // 2. OR 10%+ engagement rate (exceptional)
        // 3. OR 500+ likes AND 5%+ engagement (very strong)
        
        return $likes >= 1000 
            || $engagementRate >= 10.0
            || ($likes >= 500 && $engagementRate >= 5.0);
    }
    
    /**
     * Check if a post is HIGH-PERFORMING (100+ likes)
     * Used for keyword search posts - more lenient
     */
    private function isHighPerforming($post)
    {
        $likes = $post['num_likes'] ?? 0;
        $comments = $post['num_comments'] ?? 0;
        $shares = $post['num_shares'] ?? 0;
        $views = $post['num_views'] ?? 0;
        
        $totalEngagement = $likes + $comments + $shares;
        $engagementRate = $views > 0 ? ($totalEngagement / $views) * 100 : 0;
        
        // RELAXED CRITERIA for high-performing content:
        // 1. 100+ likes (solid performance)
        // 2. OR 5%+ engagement rate (good engagement)
        // 3. OR 50+ comments (active discussion)
        // 4. OR 20+ shares (valuable content being shared)
        
        return $likes >= 100 
            || $engagementRate >= 5.0
            || $comments >= 50
            || $shares >= 20;
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
