<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PhantomBusterService
{
    private string $apiKey;
    private string $apiUrl;
    private ?string $sessionCookieOverride = null;
    private ?string $userAgentOverride = null;

    public function __construct()
    {
        $this->apiKey = config('services.phantombuster.api_key');
        $this->apiUrl = config('services.phantombuster.api_url', 'https://api.phantombuster.com/api/v1');

        if (!$this->apiKey) {
            Log::error("PHANTOMBUSTER_API_KEY not found in environment variables");
            throw new \Exception("PHANTOMBUSTER_API_KEY not configured");
        }
    }

    /**
     * Launch a Phantom with given parameters
     *
     * @param string $phantomId The Phantom ID to launch
     * @param array $arguments Arguments to pass to the Phantom
     * @return array Response with containerId
     */
    public function launchPhantom(string $phantomId, array $arguments = []): array
    {
        $url = "{$this->apiUrl}/agent/{$phantomId}/launch";

        $payload = [];
        if (!empty($arguments)) {
            $payload['argument'] = $arguments;
        }

        Log::info('PhantomBuster: Launching phantom', [
            'phantom_id' => $phantomId,
            'arguments' => $arguments
        ]);

        try {
            $response = Http::timeout(30) // 30 seconds timeout
                ->connectTimeout(15) // 15 seconds for DNS/connection
                ->withHeaders([
                    'X-Phantombuster-Key-1' => $this->apiKey,
                    'Content-Type' => 'application/json'
                ])->post($url, $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle network/DNS errors
            $errorMessage = $e->getMessage();
            
            if (str_contains($errorMessage, 'Resolving timed out') || str_contains($errorMessage, 'cURL error 28')) {
                Log::error("PhantomBuster: DNS resolution timeout", [
                    'phantom_id' => $phantomId,
                    'url' => $url,
                    'error' => $errorMessage
                ]);
                
                throw new \Exception(
                    'NETWORK_TIMEOUT: Cannot connect to PhantomBuster API. ' .
                    'This is usually a network connectivity issue. ' .
                    'Please check your internet connection and try again. ' .
                    'If the problem persists, your server may be blocking external API connections.'
                );
            }
            
            if (str_contains($errorMessage, 'Connection timed out') || str_contains($errorMessage, 'cURL error 7')) {
                Log::error("PhantomBuster: Connection timeout", [
                    'phantom_id' => $phantomId,
                    'url' => $url,
                    'error' => $errorMessage
                ]);
                
                throw new \Exception(
                    'NETWORK_TIMEOUT: Connection to PhantomBuster API timed out. ' .
                    'The API may be temporarily unavailable or your network is slow. ' .
                    'Please try again in a few moments.'
                );
            }
            
            // Re-throw other connection errors
            throw $e;
        }

        if ($response->failed()) {
            $status = $response->status();
            $errorBody = $response->json();
            $errorMessage = is_array($errorBody) ? ($errorBody['error'] ?? json_encode($errorBody)) : $response->body();
            
            // Handle rate limiting (429) - parallel execution limit
            if ($status === 429) {
                $detailedError = is_array($errorBody) && isset($errorBody['details']['detailedErrorSlug']) 
                    ? $errorBody['details']['detailedErrorSlug'] 
                    : null;
                
                $helpfulError = "PhantomBuster rate limit reached (429). ";
                if ($detailedError === 'maxParallelismReached') {
                    $helpfulError .= "Your account allows only 1 parallel execution. Wait for running phantoms to finish, or upgrade your plan for more parallel executions.";
                } else {
                    $helpfulError .= "Too many requests. Please wait before retrying.";
                }
                $helpfulError .= " Original error: {$errorMessage}";
                
                Log::error("PhantomBuster launch failed - Rate limit", [
                    'status' => $status,
                    'body' => $errorBody,
                    'phantom_id' => $phantomId,
                    'detailed_error' => $detailedError
                ]);
                
                throw new \Exception($helpfulError);
            }
            
            // Provide helpful error message for "Agent not found"
            if ($status === 400 && str_contains(strtolower($errorMessage), 'agent not found')) {
                $helpfulError = "Phantom ID '{$phantomId}' not found in your workspace.\n\n";
                $helpfulError .= "This usually means the agent is not added to your workspace yet.\n";
                $helpfulError .= "1. Go to https://phantombuster.com/phantoms and add the phantom you intend to run.\n";
                $helpfulError .= "2. Use the instance ID from your workspace URL (the template/gallery ID will not work).\n\n";
                $helpfulError .= "Original error: {$errorMessage}";
                
                Log::error("PhantomBuster launch failed - Agent not found", [
                    'status' => $status,
                    'body' => $errorBody,
                    'phantom_id' => $phantomId,
                    'helpful_message' => $helpfulError
                ]);
                
                throw new \Exception($helpfulError);
            }
            
            Log::error("PhantomBuster launch failed:", [
                'status' => $status,
                'body' => $errorBody,
                'phantom_id' => $phantomId
            ]);
            $response->throw();
        }

        $data = $response->json();
        
        // Log full response for debugging
        Log::info('PhantomBuster: Launch response', [
            'phantom_id' => $phantomId,
            'full_response' => $data
        ]);
        
        // Try different possible field names for container ID
        // First check nested structure (most common format)
        $containerId = null;
        if (isset($data['data']) && is_array($data['data'])) {
            $containerId = $data['data']['containerId'] 
                ?? $data['data']['container_id'] 
                ?? $data['data']['id'] 
                ?? null;
        }
        
        // Fallback to top-level keys
        if (!$containerId) {
            $containerId = $data['containerId'] 
                ?? $data['container_id'] 
                ?? $data['id'] 
                ?? $data['outputId']
                ?? null;
        }
        
        Log::info('PhantomBuster: Phantom launched successfully', [
            'phantom_id' => $phantomId,
            'container_id' => $containerId,
            'response_keys' => array_keys($data),
            'has_data_key' => isset($data['data']),
            'data_keys' => isset($data['data']) && is_array($data['data']) ? array_keys($data['data']) : []
        ]);
        
        // Add containerId to response if we found it
        if ($containerId) {
            $data['containerId'] = $containerId;
        } else {
            Log::error('PhantomBuster: Could not extract containerId from launch response', [
                'phantom_id' => $phantomId,
                'full_response' => $data
            ]);
            throw new \Exception("Failed to extract container ID from PhantomBuster launch response");
        }

        return $data;
    }

    /**
     * Get the output/results from a completed Phantom execution
     *
     * @param string $phantomId The Phantom ID
     * @param string $containerId The container ID from launch response
     * @return array Output data
     */
    public function getPhantomOutput(string $phantomId, string $containerId): array
    {
        $url = "{$this->apiUrl}/agent/{$phantomId}/output";

        Log::info('PhantomBuster: Fetching output', [
            'phantom_id' => $phantomId,
            'container_id' => $containerId
        ]);

        try {
            $response = Http::timeout(30) // 30 seconds timeout
                ->connectTimeout(15) // 15 seconds for DNS/connection
                ->withHeaders([
                    'X-Phantombuster-Key-1' => $this->apiKey
                ])->get($url, [
                    'containerId' => $containerId
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $errorMessage = $e->getMessage();
            
            if (str_contains($errorMessage, 'Resolving timed out') || str_contains($errorMessage, 'cURL error 28')) {
                Log::error("PhantomBuster: DNS resolution timeout when fetching output", [
                    'phantom_id' => $phantomId,
                    'container_id' => $containerId,
                    'url' => $url,
                    'error' => $errorMessage
                ]);
                
                throw new \Exception(
                    'NETWORK_TIMEOUT: Cannot connect to PhantomBuster API. ' .
                    'Network connectivity issue detected. Please check your internet connection.'
                );
            }
            
            throw $e;
        }

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();
            
            Log::error("PhantomBuster output fetch failed:", [
                'status' => $status,
                'body' => $body,
                'phantom_id' => $phantomId,
                'container_id' => $containerId
            ]);
            
            // Handle 404 gracefully - container may not be ready yet or may have expired
            if ($status === 404) {
                $errorData = json_decode($body, true);
                $errorMessage = $errorData['error'] ?? 'Container not found';
                throw new \Exception("Container not found (404): {$errorMessage}. Container may not be ready yet or may have expired.");
            }
            
            $response->throw();
        }

        $data = $response->json();
        
        // PhantomBuster API returns data in different structures
        // Try to find the actual output array
        $outputData = $data['output'] ?? $data['data'] ?? $data;
        
        // If data is nested, try to extract it
        if (isset($data['data']) && is_array($data['data'])) {
            // Check if data contains output
            if (isset($data['data']['output'])) {
                $outputData = $data['data']['output'];
            } elseif (isset($data['data']['data'])) {
                $outputData = $data['data']['data'];
            } else {
                // data might be the output array itself
                $outputData = $data['data'];
            }
        }
        
        // Normalize to always have 'output' key
        if (!isset($data['output'])) {
            $data['output'] = is_array($outputData) ? $outputData : [];
        }
        
        // Log full output structure for debugging
        $outputRows = is_array($data['output']) ? count($data['output']) : 0;
        Log::info('PhantomBuster: Output fetched', [
            'phantom_id' => $phantomId,
            'container_id' => $containerId,
            'output_rows' => $outputRows,
            'status' => $data['status'] ?? 'unknown',
            'has_output_key' => isset($data['output']),
            'has_data_key' => isset($data['data']),
            'output_structure' => isset($data['output']) ? (is_array($data['output']) ? 'array' : gettype($data['output'])) : 'not_set',
            'all_keys' => array_keys($data),
            'data_structure' => isset($data['data']) ? (is_array($data['data']) ? 'array(' . count($data['data']) . ')' : gettype($data['data'])) : 'not_set'
        ]);
        
        // Log first few lines of output if it exists (for debugging)
        if (isset($data['output']) && is_array($data['output']) && !empty($data['output'])) {
            Log::debug('PhantomBuster: Sample output data', [
                'first_item' => $data['output'][0] ?? null,
                'total_items' => count($data['output'])
            ]);
        }
        
        // Log the data structure to understand what's inside
        if (isset($data['data']) && is_array($data['data'])) {
            Log::debug('PhantomBuster: Data structure analysis', [
                'data_count' => count($data['data']),
                'data_keys' => array_keys($data['data']),
                'first_data_key' => array_key_first($data['data']),
                'first_data_value_type' => isset($data['data'][0]) ? gettype($data['data'][0]) : 'no_index_0',
                'sample_data_item' => isset($data['data'][0]) && is_array($data['data'][0]) ? array_keys($data['data'][0]) : $data['data'][0] ?? 'not_array'
            ]);
            
            // Check if data contains an array of followers
            if (isset($data['data'][0]) && is_array($data['data'][0])) {
                Log::debug('PhantomBuster: First data item structure', [
                    'keys' => array_keys($data['data'][0]),
                    'sample' => $data['data'][0]
                ]);
            }
        }

        return $data;
    }

    /**
     * Check the status of a running Phantom
     *
     * @param string $phantomId The Phantom ID
     * @param string $containerId The container ID
     * @return array Status information
     */
    public function getPhantomStatus(string $phantomId, string $containerId): array
    {
        // Try to get output - if it's ready, we'll get data; if not, we'll get status info
        try {
            $output = $this->getPhantomOutput($phantomId, $containerId);
            // If we got here, phantom might be finished or still running
            // Check if output has status field
            if (isset($output['status'])) {
                return $output;
            }
            // If we have output data, consider it finished
            if (isset($output['output'])) {
                return ['status' => 'finished', 'output' => $output['output']];
            }
            return ['status' => 'running'];
        } catch (\Exception $e) {
            // If output fetch fails, phantom might still be running
            Log::debug('PhantomBuster: Status check - output not ready yet', [
                'container_id' => $containerId,
                'error' => $e->getMessage()
            ]);
            return ['status' => 'running'];
        }
    }

    /**
     * List all available phantoms for the authenticated user
     *
     * @return array List of phantoms with their IDs and names
     */
    public function listPhantoms(): array
    {
        $url = "{$this->apiUrl}/agents/fetch-all";

        Log::info('PhantomBuster: Fetching list of phantoms');

        try {
            $response = Http::timeout(30) // 30 seconds timeout
                ->connectTimeout(15) // 15 seconds for DNS/connection
                ->withHeaders([
                    'X-Phantombuster-Key-1' => $this->apiKey
                ])->get($url);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $errorMessage = $e->getMessage();
            
            if (str_contains($errorMessage, 'Resolving timed out') || str_contains($errorMessage, 'cURL error 28')) {
                Log::error("PhantomBuster: DNS resolution timeout when listing phantoms", [
                    'url' => $url,
                    'error' => $errorMessage
                ]);
                
                throw new \Exception(
                    'NETWORK_TIMEOUT: Cannot connect to PhantomBuster API. ' .
                    'Network connectivity issue detected. Please check your internet connection.'
                );
            }
            
            throw $e;
        }

        if ($response->failed()) {
            Log::error("PhantomBuster list phantoms failed:", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            $response->throw();
        }

        $data = $response->json();
        Log::info('PhantomBuster: Retrieved phantoms list', [
            'count' => count($data)
        ]);

        return $data;
    }

    /**
     * Find a phantom by name or slug
     *
     * @param string $name Phantom name or slug to search for
     * @return string|null Phantom ID if found, null otherwise
     */
    public function findPhantomByName(string $name): ?string
    {
        $phantoms = $this->listPhantoms();
        
        $searchName = strtolower($name);
        
        foreach ($phantoms as $phantom) {
            $phantomName = strtolower($phantom['name'] ?? '');
            $phantomSlug = strtolower($phantom['slug'] ?? '');
            
            if (str_contains($phantomName, $searchName) || str_contains($phantomSlug, $searchName)) {
                $phantomId = $phantom['id'] ?? $phantom['phantomId'] ?? null;
                Log::info('PhantomBuster: Found phantom by name', [
                    'search' => $name,
                    'found_name' => $phantom['name'] ?? 'Unknown',
                    'phantom_id' => $phantomId
                ]);
                return $phantomId;
            }
        }

        Log::warning('PhantomBuster: Phantom not found by name', ['search' => $name]);
        return null;
    }

    /**
     * Fetch company post engagers (alternative to direct followers - doesn't require admin access)
     * Workflow:
     * 1. Get company posts using RapidAPI (to get post URLs)
     * 2. For each post, extract likers (using LinkedIn Post Likers Export)
     *
     * @param string $companyUrl LinkedIn company URL
     * @param string|null $phantomId Not used anymore (kept for backward compatibility)
     * @param int $maxWaitSeconds Maximum seconds to wait
     * @param int $pollIntervalSeconds Seconds between status checks
     * @return array Array of engager profiles
     */
    /**
     * Get globally scraped post URLs for a company (shared across all users)
     * Uses cache + database aggregation from all audiences for this company
     */
    private function getGlobalScrapedPosts(string $companyUrl): array
    {
        $normalizedUrl = $this->normalizeCompanyUrl($companyUrl);
        $cacheKey = "scraped_posts:{$normalizedUrl}";
        
        // Check cache first (fast, expires after 30 days)
        $cached = Cache::get($cacheKey);
        if ($cached !== null && is_array($cached)) {
            return $cached;
        }
        
        // If not in cache, check all audiences for this company URL
        $scrapedPosts = $this->getScrapedPostsFromAllAudiences($normalizedUrl);
        
        // Cache for 30 days
        Cache::put($cacheKey, $scrapedPosts, now()->addDays(30));
        
        return $scrapedPosts;
    }
    
    /**
     * Add post URLs to global scraped list (shared across all users)
     */
    private function addToGlobalScrapedPosts(string $companyUrl, array $postUrls): void
    {
        if (empty($postUrls)) {
            return;
        }
        
        $normalizedUrl = $this->normalizeCompanyUrl($companyUrl);
        $cacheKey = "scraped_posts:{$normalizedUrl}";
        
        // Get existing scraped posts
        $existing = $this->getGlobalScrapedPosts($companyUrl);
        
        // Merge and deduplicate
        $allScraped = array_unique(array_merge($existing, $postUrls));
        
        // Update cache (30 days)
        Cache::put($cacheKey, $allScraped, now()->addDays(30));
        
        // Also update all audiences for this company URL (for persistence)
        $this->updateAllAudiencesForCompany($normalizedUrl, $allScraped);
        
        Log::info('PhantomBuster: Updated global scraped posts', [
            'company_url' => $normalizedUrl,
            'new_posts' => count($postUrls),
            'total_scraped' => count($allScraped)
        ]);
    }
    
    /**
     * Normalize company URL for consistent caching
     */
    private function normalizeCompanyUrl(string $url): string
    {
        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        $path = rtrim($parsed['path'] ?? '', '/');
        
        return strtolower("{$scheme}://{$host}{$path}");
    }
    
    /**
     * Get scraped posts from all audiences for a company URL
     */
    private function getScrapedPostsFromAllAudiences(string $normalizedUrl): array
    {
        $allScraped = [];
        
        // Query all audiences for this company URL
        $audiences = \App\Models\Audience::where('source', 'linkedin_company_followers')
            ->where('tag', 'competitor_active_followers')
            ->whereNotNull('source_meta')
            ->get();
        
        foreach ($audiences as $audience) {
            $meta = json_decode($audience->source_meta, true);
            if (!is_array($meta)) {
                continue;
            }
            
            // Check if this audience is for the same company
            $audienceCompanyUrl = $meta['company_url'] ?? null;
            if ($audienceCompanyUrl && $this->normalizeCompanyUrl($audienceCompanyUrl) === $normalizedUrl) {
                $scraped = $meta['scraped_post_urls'] ?? [];
                if (is_array($scraped)) {
                    $allScraped = array_merge($allScraped, $scraped);
                }
            }
        }
        
        return array_unique($allScraped);
    }
    
    /**
     * Update all audiences for a company URL with scraped posts
     */
    private function updateAllAudiencesForCompany(string $normalizedUrl, array $scrapedPosts): void
    {
        $audiences = \App\Models\Audience::where('source', 'linkedin_company_followers')
            ->where('tag', 'competitor_active_followers')
            ->whereNotNull('source_meta')
            ->get();
        
        foreach ($audiences as $audience) {
            $meta = json_decode($audience->source_meta, true) ?? [];
            $audienceCompanyUrl = $meta['company_url'] ?? null;
            
            // Update audiences for this company
            if ($audienceCompanyUrl && $this->normalizeCompanyUrl($audienceCompanyUrl) === $normalizedUrl) {
                $existingScraped = $meta['scraped_post_urls'] ?? [];
                $allScraped = array_unique(array_merge($existingScraped, $scrapedPosts));
                $meta['scraped_post_urls'] = $allScraped;
                
                $audience->source_meta = json_encode($meta);
                $audience->save();
            }
        }
    }
    
    /**
     * Clear global scraped posts for a company URL (admin/reset function)
     */
    public function clearGlobalScrapedPosts(string $companyUrl): void
    {
        $normalizedUrl = $this->normalizeCompanyUrl($companyUrl);
        $cacheKey = "scraped_posts:{$normalizedUrl}";
        
        // Clear cache
        Cache::forget($cacheKey);
        
        // Clear from all audiences for this company
        $audiences = \App\Models\Audience::where('source', 'linkedin_company_followers')
            ->where('tag', 'competitor_active_followers')
            ->whereNotNull('source_meta')
            ->get();
        
        foreach ($audiences as $audience) {
            $meta = json_decode($audience->source_meta, true) ?? [];
            $audienceCompanyUrl = $meta['company_url'] ?? null;
            
            if ($audienceCompanyUrl && $this->normalizeCompanyUrl($audienceCompanyUrl) === $normalizedUrl) {
                unset($meta['scraped_post_urls']);
                $audience->source_meta = json_encode($meta);
                $audience->save();
            }
        }
        
        Log::info('PhantomBuster: Cleared global scraped posts', [
            'company_url' => $normalizedUrl
        ]);
    }

    public function fetchCompanyPostEngagers(
        string $companyUrl,
        ?string $phantomId = null,
        int $maxWaitSeconds = 600,
        int $pollIntervalSeconds = 15,
        ?string $sessionCookie = null,
        ?string $userAgent = null,
        array $alreadyScrapedPostUrls = [],
        $audience = null
    ): array {
        $this->sessionCookieOverride = $sessionCookie;
        $this->userAgentOverride = $userAgent;
        try {
            Log::info('PhantomBuster: Starting to fetch company post engagers', [
                'company_url' => $companyUrl
            ]);

            // Step 1: Get company posts using RapidAPI - sort by "top" for highest engagement
            $rapidApiService = new \App\Services\RapidApiService();
            // Try "top" first for highest engagement posts, fallback to "recent" if not supported
            $posts = $rapidApiService->fetch_company_posts($companyUrl, 1, 'top');
            
            if (empty($posts) || !isset($posts['data']) || empty($posts['data'])) {
                Log::warning('PhantomBuster: No posts found for company', ['company_url' => $companyUrl]);
                return [];
            }

            Log::info('PhantomBuster: Found company posts from RapidAPI', [
                'company_url' => $companyUrl,
                'posts_count' => count($posts['data']),
                'sort_by' => 'top'
            ]);

            // Step 2: Sort posts by engagement (likes) before extracting URLs
            // This ensures we process the most engaging posts first
            $sortedPosts = $this->sortPostsByEngagement($posts['data']);
            
            // Extract post URLs from sorted posts
            $allPostUrls = $this->extractPostUrlsFromRapidApi($sortedPosts);
            
            if (empty($allPostUrls)) {
                Log::warning('PhantomBuster: No post URLs found in posts', ['company_url' => $companyUrl]);
                return ['engagers' => [], 'newly_scraped_posts' => []];
            }
            
            // Filter out ONLY posts this specific user/audience has already scraped
            // Each user tracks their own scraped posts independently
            // This allows multiple users to scrape the same posts if PhantomBuster allows it
            $postUrls = array_values(array_filter($allPostUrls, function($url) use ($alreadyScrapedPostUrls) {
                return !in_array($url, $alreadyScrapedPostUrls);
            }));
            
            Log::info('PhantomBuster: Filtered posts by user scraped status', [
                'total_posts_available' => count($allPostUrls),
                'user_scraped' => count($alreadyScrapedPostUrls),
                'posts_to_process' => count($postUrls),
                'skipped_posts' => count($allPostUrls) - count($postUrls),
                'note' => 'Each user tracks their own scraped posts. Multiple users can attempt the same posts.'
            ]);
            
            if (empty($postUrls)) {
                Log::warning('PhantomBuster: All posts have already been scraped', [
                    'company_url' => $companyUrl,
                    'total_posts' => count($allPostUrls),
                    'already_scraped' => count($alreadyScrapedPostUrls)
                ]);
                return ['engagers' => [], 'newly_scraped_posts' => []];
            }

            // Step 3: Process posts dynamically - skip already-scraped ones and continue
            // Keep processing until we find unscraped posts or hit max attempts
            $maxAttempts = (int) config('services.phantombuster.company_posts_limit', 15);
            $maxSuccessfulPosts = 5; // Target: try to get data from at least 5 posts
            $minEngagersForEarlyStop = (int) config('services.phantombuster.min_engagers_for_early_stop', 1000);
            
            Log::info('PhantomBuster: Starting dynamic post processing', [
                'total_posts_available' => count($postUrls),
                'max_attempts' => $maxAttempts,
                'target_successful_posts' => $maxSuccessfulPosts,
                'note' => 'Will skip already-scraped posts and continue until finding unscraped ones.'
            ]);

            $allEngagers = [];
            $processedPosts = 0;
            $skippedPosts = 0;
            $successfulPosts = 0;
            $postIndex = 0;
            $newlyScrapedPostUrls = []; // Track newly scraped posts to return
            
            // Process posts until we find enough unscraped ones or hit max attempts
            while ($postIndex < count($postUrls) && $processedPosts < $maxAttempts) {
                $postUrl = $postUrls[$postIndex];
                $postIndex++;
                $processedPosts++;
                Log::info('PhantomBuster: Processing post', [
                    'post_number' => $processedPosts,
                    'post_index' => $postIndex,
                    'total_available' => count($postUrls),
                    'successful_so_far' => $successfulPosts,
                    'skipped_so_far' => $skippedPosts,
                    'post_url' => $postUrl
                ]);

                $postEngagers = 0;
                $likersFailed = false;

                try {
                    // Get likers for this post
                    $likers = $this->fetchPostLikers($postUrl, $maxWaitSeconds, $pollIntervalSeconds);
                    
                    // Ensure we only merge arrays (filter out any non-array items)
                    $validLikers = array_filter($likers, function($liker) {
                        return is_array($liker);
                    });
                    
                    $postEngagers += count($validLikers);
                    Log::info('PhantomBuster: Got likers for post', [
                        'post_url' => $postUrl,
                        'likers_count' => count($validLikers),
                        'filtered_out' => count($likers) - count($validLikers)
                    ]);
                    $allEngagers = array_merge($allEngagers, $validLikers);
                } catch (\Exception $e) {
                    $likersFailed = true;
                    $errorMsg = $e->getMessage();
                    $isAlreadyScraped = str_contains($errorMsg, 'already scraped') || 
                                       str_contains($errorMsg, 'input is empty');
                    
                    Log::warning('PhantomBuster: Failed to get likers for post', [
                        'post_url' => $postUrl,
                        'error' => $errorMsg,
                        'already_scraped' => $isAlreadyScraped,
                        'error_type' => get_class($e)
                    ]);
                    
                    // If it's a 429 error (rate limit), wait a bit before continuing
                    if (str_contains($errorMsg, '429') || str_contains($errorMsg, 'parallel')) {
                        Log::info('PhantomBuster: Rate limit hit, waiting before next request', [
                            'wait_seconds' => 30
                        ]);
                        sleep(30); // Wait 30 seconds for rate limit to clear
                    }
                }
                
                // Track successful vs skipped posts
                $shouldMarkAsScraped = false;
                
                if ($postEngagers > 0) {
                    $successfulPosts++;
                    $shouldMarkAsScraped = true;
                    
                    Log::info('PhantomBuster: Post processed successfully', [
                        'post_url' => $postUrl,
                        'engagers_count' => $postEngagers,
                        'successful_posts' => $successfulPosts,
                        'total_engagers_so_far' => count($allEngagers)
                    ]);
                    
                    // Early stop if we have enough engagers (saves PhantomBuster credits)
                    if ($minEngagersForEarlyStop > 0 && count($allEngagers) >= $minEngagersForEarlyStop) {
                        Log::info('PhantomBuster: Early stopping - enough engagers collected (saving credits)', [
                            'successful_posts' => $successfulPosts,
                            'total_engagers' => count($allEngagers),
                            'min_required' => $minEngagersForEarlyStop,
                            'phantom_calls_used' => $processedPosts,
                            'phantom_calls_saved' => ($maxAttempts - $processedPosts)
                        ]);
                        break; // Stop processing more posts to save credits
                    }
                    
                    // If we've found enough successful posts with good data, continue but log it
                    if ($successfulPosts >= $maxSuccessfulPosts && count($allEngagers) >= 100) {
                        Log::info('PhantomBuster: Found enough successful posts, continuing to max attempts', [
                            'successful_posts' => $successfulPosts,
                            'total_engagers' => count($allEngagers),
                            'remaining_attempts' => $maxAttempts - $processedPosts
                        ]);
                    }
                } elseif ($likersFailed) {
                    $skippedPosts++;
                    // Mark as scraped for THIS USER ONLY (not globally)
                    // Other users can still try this post - maybe PhantomBuster will allow it for them
                    $newlyScrapedPostUrls[] = $postUrl;
                    
                    Log::info('PhantomBuster: Post already scraped by PhantomBuster - marking for this user only', [
                        'post_url' => $postUrl,
                        'skipped_posts' => $skippedPosts,
                        'successful_posts' => $successfulPosts,
                        'remaining_attempts' => $maxAttempts - $processedPosts,
                        'total_engagers_so_far' => count($allEngagers),
                        'note' => 'This post marked as scraped for this user. Other users can still attempt it.'
                    ]);
                }
                
                // Mark post as scraped if we attempted it
                if ($shouldMarkAsScraped) {
                    $newlyScrapedPostUrls[] = $postUrl;
                }
                
                // Small delay before processing next post to avoid hitting parallel limits
                sleep(2);
            }
            
            Log::info('PhantomBuster: Finished processing all posts', [
                'posts_processed' => $processedPosts,
                'posts_available' => count($postUrls),
                'successful_posts' => $successfulPosts,
                'skipped_posts' => $skippedPosts
            ]);
            
            Log::info('PhantomBuster: Post processing summary', [
                'total_posts_processed' => $processedPosts,
                'successful_posts' => $successfulPosts,
                'skipped_posts' => $skippedPosts,
                'total_engagers_found' => count($allEngagers)
            ]);

            // Remove duplicates by public identifier
            $uniqueEngagers = [];
            $seen = [];
            foreach ($allEngagers as $engager) {
                // Skip if not an array (shouldn't happen, but safety check)
                if (!is_array($engager)) {
                    Log::warning('PhantomBuster: Skipping non-array engager', [
                        'type' => gettype($engager),
                        'value' => is_string($engager) ? substr($engager, 0, 100) : $engager
                    ]);
                    continue;
                }
                
                $publicId = null;
                
                // Check profileLink first (PhantomBuster's format)
                $profileLink = $engager['profileLink'] 
                    ?? $engager['profileUrl'] 
                    ?? $engager['profile_url'] 
                    ?? null;
                
                // Extract ID from profileLink/profileUrl
                if ($profileLink) {
                    if (preg_match('/linkedin\.com\/in\/([^\/\?]+)/', $profileLink, $matches)) {
                        $publicId = $matches[1];
                    }
                }
                
                // Fallback to other fields
                if (!$publicId) {
                    $publicId = $engager['publicIdentifier'] 
                        ?? $engager['public_identifier'] 
                        ?? $engager['memberId'] 
                        ?? null;
                }
                
                if ($publicId && !isset($seen[$publicId])) {
                    $uniqueEngagers[] = $engager;
                    $seen[$publicId] = true;
                } elseif (!$publicId) {
                    // If no public ID, still add it (might be unique by other fields)
                    $uniqueEngagers[] = $engager;
                }
            }

            Log::info('PhantomBuster: Finished fetching engagers', [
                'company_url' => $companyUrl,
                'total_engagers' => count($allEngagers),
                'unique_engagers' => count($uniqueEngagers),
                'newly_scraped_posts' => count($newlyScrapedPostUrls ?? [])
            ]);

            // Return both engagers and newly scraped posts for tracking
            // Note: Scraped posts are tracked per-user in the audience source_meta
            // This allows multiple users to attempt the same posts independently
            return [
                'engagers' => $uniqueEngagers,
                'newly_scraped_posts' => $newlyScrapedPostUrls ?? []
            ];
        } finally {
            $this->sessionCookieOverride = null;
            $this->userAgentOverride = null;
        }
    }

    /**
     * Fetch likers for a single LinkedIn post using PhantomBuster.
     *
     * @param string $postUrl Full LinkedIn post URL
     * @param int $maxWaitSeconds
     * @param int $pollIntervalSeconds
     * @param string|null $sessionCookie Optional override for li_at
     * @param string|null $userAgent Optional override for user agent
     * @return array
     * @throws \Exception
     */
    public function fetchPostLikersForUrl(
        string $postUrl,
        int $maxWaitSeconds = 300,
        int $pollIntervalSeconds = 10,
        ?string $sessionCookie = null,
        ?string $userAgent = null
    ): array {
        $this->sessionCookieOverride = $sessionCookie;
        $this->userAgentOverride = $userAgent;

        try {
            return $this->fetchPostLikers($postUrl, $maxWaitSeconds, $pollIntervalSeconds);
        } finally {
            $this->sessionCookieOverride = null;
            $this->userAgentOverride = null;
        }
    }

    /**
     * Fetch search results from a LinkedIn search URL using PhantomBuster's "LinkedIn Search Export".
     *
     * @param string $searchUrl A full LinkedIn search URL (people search preferred)
     * @param int $maxWaitSeconds Maximum seconds to wait for the phantom to finish
     * @param int $pollIntervalSeconds Poll interval while waiting
     * @param string|null $sessionCookie Optional override for li_at
     * @param string|null $userAgent Optional override for user agent
     * @param array|null $identities Optional identities array format: [['identityId' => string, 'sessionCookie' => string, 'userAgent' => string]]
     * @return array Array of profiles from PhantomBuster
     * @throws \Exception
     */
    public function fetchSearchExportResults(
        ?string $searchUrl = null,
        int $maxWaitSeconds = 600,
        int $pollIntervalSeconds = 15,
        ?string $sessionCookie = null,
        ?string $userAgent = null,
        ?string $keywords = null,
        array $connectionDegrees = [],
        string $category = 'People',
        ?int $resultsLimit = null,
        ?array $identities = null
    ): array {
        $this->sessionCookieOverride = $sessionCookie;
        $this->userAgentOverride = $userAgent;

        try {
            $phantomId = config('services.phantombuster.linkedin_search_export_phantom_id');

            if (!$phantomId) {
                $phantomId = $this->findPhantomByName('linkedin search export');
            }

            if (!$phantomId) {
                Log::warning('PhantomBuster: LinkedIn Search Export phantom not found, skipping search extraction');
                return [];
            }

            $arguments = [];

            // Normalize category to ensure it's capitalized (PhantomBuster requires "People" not "people")
            $category = ucfirst(strtolower(trim($category)));

            $hasKeywords = $keywords && trim($keywords) !== '';
            $hasUrl = $searchUrl && trim($searchUrl) !== '';

            // Priority: If complete URL is provided (with filters), use it
            // Otherwise, use keywords mode and build minimal URL
            if ($hasUrl) {
                // Complete URL provided - use it (includes keywords and all filters)
                $arguments['searchUrl'] = $searchUrl;
                $arguments['linkedInSearchUrl'] = $searchUrl;
                $arguments['searchType'] = 'linkedInSearchUrl';
                
                // Also include keywords if provided (for PhantomBuster's keyword mode compatibility)
                if ($hasKeywords) {
                    $arguments['keywords'] = trim($keywords);
                    $arguments['category'] = $category;
                    if (!empty($connectionDegrees)) {
                        $arguments['connectionDegreesToScrape'] = $connectionDegrees;
                    }
                }
            } elseif ($hasKeywords) {
                // Only keywords provided - build minimal URL
                $arguments['keywords'] = trim($keywords);
                $arguments['category'] = $category;
                if (!empty($connectionDegrees)) {
                    $arguments['connectionDegreesToScrape'] = $connectionDegrees;
                }
                $arguments['searchType'] = 'keywords';
                
                // Build minimal search URL from keywords
                $encodedKeywords = urlencode(trim($keywords));
                $networkParam = urlencode(json_encode(!empty($connectionDegrees) ? $connectionDegrees : ['2','3+']));
                $builtUrl = "https://www.linkedin.com/search/results/all/?keywords={$encodedKeywords}&network={$networkParam}";
                $arguments['searchUrl'] = $builtUrl;
                $arguments['linkedInSearchUrl'] = $builtUrl;
            } else {
                throw new \Exception("Search export requires either a searchUrl or keywords.");
            }

            // Add limits to match Phantom config expectations
            $limit = $resultsLimit ?? 100;
            $perSearch = max(1, min($limit, 1000)); // cap at 1000
            // Mirror Phantom UI defaults: 100 per launch, 10 lines per launch
            $perLaunch = max(1, min($perSearch, 100));
            $linesPerLaunch = max(1, min(10, $perLaunch));

            $arguments['numberOfResultsPerSearch'] = $perSearch;
            $arguments['numberOfResultsPerLaunch'] = $perLaunch;
            $arguments['numberOfLinesPerLaunch'] = $linesPerLaunch;
            $arguments['enrichLeadsWithAdditionalInformation'] = true;

            // Use identities array format if provided, otherwise use top-level sessionCookie/userAgent
            if (!empty($identities) && is_array($identities)) {
                // Format identities array for PhantomBuster - match exact structure from PhantomBuster dashboard
                $formattedIdentities = [];
                foreach ($identities as $identity) {
                    if (is_array($identity) && isset($identity['sessionCookie'])) {
                        $formattedIdentity = [
                            'sessionCookie' => $identity['sessionCookie']
                        ];
                        
                        // Add identityId if provided (optional but recommended for reuse)
                        if (isset($identity['identityId']) && !empty($identity['identityId'])) {
                            $formattedIdentity['identityId'] = $identity['identityId'];
                        }
                        
                        // Add userAgent to identity (required in identities array)
                        if (isset($identity['userAgent']) && !empty($identity['userAgent'])) {
                            $formattedIdentity['userAgent'] = $identity['userAgent'];
                        } elseif ($userAgent) {
                            $formattedIdentity['userAgent'] = $userAgent;
                        } elseif ($this->getUserAgent()) {
                            $formattedIdentity['userAgent'] = $this->getUserAgent();
                        }
                        
                        $formattedIdentities[] = $formattedIdentity;
                    }
                }
                
                if (!empty($formattedIdentities)) {
                    $arguments['identities'] = $formattedIdentities;
                    
                    // Also add top-level userAgent (PhantomBuster structure includes both)
                    $topLevelUserAgent = $userAgent ?? $this->getUserAgent();
                    if ($topLevelUserAgent) {
                        $arguments['userAgent'] = $topLevelUserAgent;
                    }
                    
                    Log::info('PhantomBuster: Using identities array format', [
                        'identities_count' => count($formattedIdentities),
                        'has_identityId' => !empty($formattedIdentities[0]['identityId'] ?? null)
                    ]);
                }
            } else {
                // Fallback to top-level sessionCookie and userAgent (backward compatibility)
                $sessionCookieValue = $this->getSessionCookie();
                if ($sessionCookieValue) {
                    $arguments['sessionCookie'] = $sessionCookieValue;
                }

                $userAgentValue = $this->getUserAgent();
                if ($userAgentValue) {
                    $arguments['userAgent'] = $userAgentValue;
                }
            }

            $launchResponse = $this->launchPhantom($phantomId, $arguments);
            $containerId = $launchResponse['containerId'] ?? $launchResponse['data']['containerId'] ?? null;

            if (!$containerId) {
                throw new \Exception("Failed to get container ID from PhantomBuster launch for search export");
            }

            // Wait briefly before polling
            sleep(5);

            $startTime = time();
            $attempts = 0;
            $last404Time = null;
            $alreadyRetrievedWarning = false; // Track if we detect "already retrieved" warning

            while (time() - $startTime < $maxWaitSeconds) {
                $attempts++;

                try {
                    if ($attempts > 1) {
                        sleep($pollIntervalSeconds);
                    }

                    $output = $this->getPhantomOutput($phantomId, $containerId);
                } catch (\Exception $e) {
                    if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Container not found')) {
                        if ($last404Time === null) {
                            $last404Time = time();
                        }

                        if (time() - $last404Time > 30) {
                            Log::error('PhantomBuster: Search export container not found after multiple attempts', [
                                'phantom_id' => $phantomId,
                                'container_id' => $containerId,
                                'attempts' => $attempts,
                                'search_url' => $searchUrl
                            ]);
                            return [];
                        }

                        Log::info('PhantomBuster: Search export container not ready yet, waiting...', [
                            'attempt' => $attempts,
                            'phantom_id' => $phantomId,
                            'container_id' => $containerId
                        ]);
                        continue;
                    }

                    throw $e;
                }

                // Extract profiles - handle both JSON string and array formats
                $profiles = [];
                
                // Check for "already retrieved" warning in output
                $outputString = '';
                if (isset($output['data']['output']) && is_string($output['data']['output'])) {
                    $outputString = $output['data']['output'];
                }
                
                $alreadyRetrievedWarning = stripos($outputString, "already retrieved all results") !== false;
                
                if ($alreadyRetrievedWarning) {
                    Log::warning('PhantomBuster: Search already retrieved - results may be cached', [
                        'message' => 'PhantomBuster indicates this search was already performed. Results may be in cache.',
                        'suggestion' => 'Try changing search keywords or parameters, or check PhantomBuster dashboard for cached results'
                    ]);
                }
                
                // Try resultObject first (newer phantoms return JSON string here)
                if (!empty($output['data']['resultObject'] ?? null)) {
                    $resultObject = $output['data']['resultObject'];
                    
                    if (is_string($resultObject)) {
                        // Decode JSON string
                        $decoded = json_decode($resultObject, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $profiles = $decoded;
                        } else {
                            Log::warning('PhantomBuster: Failed to decode resultObject JSON string', [
                                'json_error' => json_last_error_msg(),
                                'resultObject_preview' => substr($resultObject, 0, 200)
                            ]);
                        }
                    } elseif (is_array($resultObject)) {
                        $profiles = $resultObject;
                    }
                }
                
                // Fallback to output if resultObject didn't yield data
                if (empty($profiles) && !empty($output['data']['output'] ?? null)) {
                    $outputData = $output['data']['output'];
                    
                    if (is_string($outputData)) {
                        // Skip if it's just log text (contains warning messages but no JSON)
                        // Check if it looks like JSON (starts with [ or {)
                        $trimmed = trim($outputData);
                        if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                            $decoded = json_decode($outputData, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $profiles = $decoded;
                            } else {
                                Log::warning('PhantomBuster: Output is string but not valid JSON', [
                                    'json_error' => json_last_error_msg(),
                                    'output_preview' => substr($outputData, 0, 200),
                                    'is_log_text' => $alreadyRetrievedWarning
                                ]);
                            }
                        } else {
                            // It's log text, not JSON data
                            Log::info('PhantomBuster: Output is log text, not profile data', [
                                'output_preview' => substr($outputData, 0, 300),
                                'has_warning' => $alreadyRetrievedWarning
                            ]);
                        }
                    } elseif (is_array($outputData)) {
                        $profiles = $outputData;
                    }
                }
                
                // Final fallback to top-level output
                if (empty($profiles) && !empty($output['output'] ?? null)) {
                    $topLevelOutput = $output['output'];
                    if (is_string($topLevelOutput)) {
                        $trimmed = trim($topLevelOutput);
                        if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                            $decoded = json_decode($topLevelOutput, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $profiles = $decoded;
                            }
                        }
                    } elseif (is_array($topLevelOutput)) {
                        $profiles = $topLevelOutput;
                    }
                }

                $containerStatus = $output['data']['containerStatus'] ?? null;
                $agentStatus = $output['data']['agentStatus'] ?? null;
                $messages = $output['data']['messages'] ?? [];
                $progress = $output['data']['progress'] ?? null;

                Log::info('PhantomBuster: Search export status check', [
                    'attempt' => $attempts,
                    'container_status' => $containerStatus,
                    'agent_status' => $agentStatus,
                    'profiles_count' => count($profiles),
                    'messages' => $messages,
                    'progress' => $progress,
                    'has_output' => isset($output['data']['output']),
                    'has_resultObject' => isset($output['data']['resultObject']),
                    'resultObject_type' => isset($output['data']['resultObject']) ? gettype($output['data']['resultObject']) : null,
                    'output_type' => isset($output['data']['output']) ? gettype($output['data']['output']) : null,
                ]);

                // Filter out error objects - PhantomBuster sometimes returns error objects instead of profiles
                $validProfiles = [];
                $errorObjects = [];
                foreach ($profiles as $profile) {
                    if (!is_array($profile)) {
                        continue;
                    }
                    // Skip error objects (they have 'error' key but no valid profile data)
                    if (isset($profile['error']) && empty($profile['fullName']) && empty($profile['profileUrl']) && empty($profile['firstName'])) {
                        $errorObjects[] = $profile;
                        continue;
                    }
                    // Only include profiles with at least a name or profileUrl
                    if (!empty($profile['fullName']) || !empty($profile['profileUrl']) || !empty($profile['firstName'])) {
                        $validProfiles[] = $profile;
                    }
                }
                
                // Log errors if all results were error objects
                if (!empty($errorObjects) && empty($validProfiles)) {
                    $firstError = $errorObjects[0] ?? null;
                    Log::warning('PhantomBuster: All returned items were error objects', [
                        'error_count' => count($errorObjects),
                        'first_error' => $firstError,
                        'error_message' => $firstError['error'] ?? null,
                        'error_query' => $firstError['query'] ?? null,
                        'search_url' => $searchUrl,
                        'keywords' => $keywords,
                        'suggestion' => 'Check PhantomBuster dashboard for detailed error message. This may indicate LinkedIn session issues or search parameter problems.'
                    ]);
                } elseif (!empty($errorObjects)) {
                    Log::info('PhantomBuster: Filtered out error objects', [
                        'error_count' => count($errorObjects),
                        'valid_profiles_count' => count($validProfiles),
                        'first_error' => $errorObjects[0] ?? null
                    ]);
                }
                
                $profiles = $validProfiles;

                if (!empty($profiles) && is_array($profiles) && count($profiles) > 0) {
                    Log::info('PhantomBuster: Successfully extracted profiles', ['count' => count($profiles)]);
                    return $profiles;
                }

                if ($containerStatus === 'not running' || $containerStatus === 'finished' || $containerStatus === 'completed') {
                    // Check for invalid/expired session cookie error
                    $outputString = '';
                    if (isset($output['data']['output']) && is_string($output['data']['output'])) {
                        $outputString = $output['data']['output'];
                    }
                    
                    // Check for session cookie errors
                    $sessionCookieErrorPatterns = [
                        'No valid credentials found',
                        'Invalid/expired cookie',
                        'network-cookie-invalid',
                        'No valid credentials',
                        'exit code: 87'
                    ];
                    
                    $hasSessionCookieError = false;
                    foreach ($sessionCookieErrorPatterns as $pattern) {
                        if (stripos($outputString, $pattern) !== false) {
                            $hasSessionCookieError = true;
                            break;
                        }
                    }
                    
                    if ($hasSessionCookieError && empty($profiles)) {
                        Log::error('PhantomBuster: LinkedIn session cookie invalid or expired', [
                            'container_status' => $containerStatus,
                            'agent_status' => $agentStatus,
                            'error_detected' => 'Session cookie invalid/expired',
                            'output_preview' => substr($outputString, 0, 500)
                        ]);
                        
                        throw new \Exception(
                            'LINKEDIN_SESSION_EXPIRED: Your LinkedIn session cookie has expired or is invalid. ' .
                            'Please update it in the Social Accounts page of your CRM. ' .
                            'Go to: Social Accounts → LinkedIn → Update session cookie'
                        );
                    }
                    
                    // Check if this is the "already retrieved" case
                    if ($alreadyRetrievedWarning && empty($profiles)) {
                        Log::info('PhantomBuster: Search already retrieved - returning empty array for pagination', [
                            'container_status' => $containerStatus,
                            'agent_status' => $agentStatus,
                            'message' => 'This exact search was already performed. Returning empty array to allow frontend pagination to stop gracefully.',
                            'note' => 'PhantomBuster returns all results at once, so pagination requests with same params will be empty'
                        ]);
                        // Return empty array instead of throwing exception
                        // This allows frontend pagination to stop gracefully when no more results
                        return [];
                    }
                    
                    // Finished but no data - log detailed information
                    Log::warning('PhantomBuster: Search export finished immediately with no data', [
                        'container_status' => $containerStatus,
                        'agent_status' => $agentStatus,
                        'messages' => $messages,
                        'progress' => $progress,
                        'output_keys' => isset($output['data']) ? array_keys($output['data']) : [],
                        'has_output' => isset($output['data']['output']),
                        'output_type' => isset($output['data']['output']) ? gettype($output['data']['output']) : null,
                        'output_preview' => isset($output['data']['output']) ? (
                            is_string($output['data']['output']) ? substr($output['data']['output'], 0, 500) : 
                            (is_array($output['data']['output']) ? json_encode(array_slice($output['data']['output'], 0, 2)) : 
                            $output['data']['output'])
                        ) : null,
                        'full_output_sample' => isset($output['data']) ? array_slice($output['data'], 0, 10) : null,
                    ]);
                    break;
                }
            }

            // Check for "already retrieved" warning in final output if we didn't catch it earlier
            $finalOutputString = '';
            if (isset($output['data']['output']) && is_string($output['data']['output'])) {
                $finalOutputString = $output['data']['output'];
            }
            $finalAlreadyRetrieved = stripos($finalOutputString, "already retrieved all results") !== false;
            
            if ($finalAlreadyRetrieved && empty($profiles)) {
                Log::info('PhantomBuster: Search already retrieved - returning empty array for pagination', [
                    'search_url' => $searchUrl,
                    'keywords' => $keywords,
                    'waited_seconds' => time() - $startTime,
                    'note' => 'Returning empty array to allow frontend pagination to stop gracefully'
                ]);
                // Return empty array instead of throwing exception
                // This allows frontend pagination to stop gracefully when no more results
                return [];
            }

            // Final check for session cookie error before returning empty
            $finalOutputString = '';
            if (isset($output['data']['output']) && is_string($output['data']['output'])) {
                $finalOutputString = $output['data']['output'];
            }
            
            $sessionCookieErrorPatterns = [
                'No valid credentials found',
                'Invalid/expired cookie',
                'network-cookie-invalid',
                'No valid credentials',
                'exit code: 87'
            ];
            
            foreach ($sessionCookieErrorPatterns as $pattern) {
                if (stripos($finalOutputString, $pattern) !== false) {
                    Log::error('PhantomBuster: LinkedIn session cookie invalid or expired (final check)', [
                        'search_url' => $searchUrl,
                        'error_detected' => 'Session cookie invalid/expired',
                        'output_preview' => substr($finalOutputString, 0, 500)
                    ]);
                    
                    throw new \Exception(
                        'LINKEDIN_SESSION_EXPIRED: Your LinkedIn session cookie has expired or is invalid. ' .
                        'Please update it in the Social Accounts page of your CRM. ' .
                        'Go to: Social Accounts → LinkedIn → Update session cookie'
                    );
                }
            }
            
            Log::warning('PhantomBuster: Search export finished with no data', [
                'search_url' => $searchUrl,
                'waited_seconds' => time() - $startTime,
                'max_wait_seconds' => $maxWaitSeconds,
            ]);

            return [];
        } finally {
            $this->sessionCookieOverride = null;
            $this->userAgentOverride = null;
        }
    }

    /**
     * Sort posts by engagement (likes only)
     * Posts with higher engagement will be processed first
     *
     * @param array $posts Posts from RapidAPI
     * @return array Sorted posts array (highest engagement first)
     */
    private function sortPostsByEngagement(array $posts): array
    {
        usort($posts, function($a, $b) {
            // Extract engagement metrics (likes only)
            $likesA = (int)($a['num_likes'] ?? $a['likes'] ?? $a['like_count'] ?? 0);
            $likesB = (int)($b['num_likes'] ?? $b['likes'] ?? $b['like_count'] ?? 0);
            
            // Sort descending (highest likes first)
            return $likesB <=> $likesA;
        });
        
        // Log top 3 posts for debugging
        if (count($posts) > 0) {
            $topPosts = array_slice($posts, 0, min(3, count($posts)));
            $topEngagement = [];
            foreach ($topPosts as $post) {
                $likes = (int)($post['num_likes'] ?? $post['likes'] ?? $post['like_count'] ?? 0);
                $topEngagement[] = [
                    'likes' => $likes
                ];
            }
            Log::info('PhantomBuster: Top engaging posts selected', [
                'top_3_engagement' => $topEngagement
            ]);
        }
        
        return $posts;
    }

    /**
     * Extract post URLs from RapidAPI posts array
     *
     * @param array $posts Posts from RapidAPI (should be sorted by engagement)
     * @return array Array of post URLs
     */
    private function extractPostUrlsFromRapidApi(array $posts): array
    {
        $postUrls = [];
        
        foreach ($posts as $post) {
            // RapidAPI returns post URL in different possible fields
            $postUrl = $post['url'] 
                ?? $post['postUrl'] 
                ?? $post['post_url'] 
                ?? $post['linkedInUrl'] 
                ?? $post['linkedin_url'] 
                ?? $post['link'] 
                ?? null;
            
            // If post is a string (URL), use it directly
            if (!$postUrl && is_string($post)) {
                $postUrl = $post;
            }
            
            // Validate it's a LinkedIn URL
            if ($postUrl && (filter_var($postUrl, FILTER_VALIDATE_URL) || str_starts_with($postUrl, 'https://www.linkedin.com'))) {
                $postUrls[] = $postUrl;
            }
        }
        
        return array_unique($postUrls);
    }

    /**
     * Fetch likers for a specific post URL
     *
     * @param string $postUrl LinkedIn post URL
     * @param int $maxWaitSeconds
     * @param int $pollIntervalSeconds
     * @return array Array of liker profiles
     */
    private function fetchPostLikers(
        string $postUrl,
        int $maxWaitSeconds = 300,
        int $pollIntervalSeconds = 10
    ): array {
        $phantomId = config('services.phantombuster.linkedin_post_likers_phantom_id');
        
        if (!$phantomId) {
            $phantomId = $this->findPhantomByName('linkedin post likers');
        }
        
        if (!$phantomId) {
            Log::warning('PhantomBuster: LinkedIn Post Likers phantom not found, skipping likers extraction');
            return [];
        }

        $arguments = [
            'postUrl' => $postUrl,
        ];
        
        // Add session cookie and user agent
        $sessionCookie = $this->getSessionCookie();
        if ($sessionCookie) {
            $arguments['sessionCookie'] = $sessionCookie;
        }
        
        $userAgent = $this->getUserAgent();
        if ($userAgent) {
            $arguments['userAgent'] = $userAgent;
        }

        $launchResponse = $this->launchPhantom($phantomId, $arguments);
        $containerId = $launchResponse['containerId'] ?? $launchResponse['data']['containerId'] ?? null;
        
        if (!$containerId) {
            throw new \Exception("Failed to get container ID from PhantomBuster launch for post likers");
        }

        // Wait a bit before first poll to allow container to initialize
        // PhantomBuster containers need time to start up
        sleep(5);

        // Poll for completion
        $startTime = time();
        $attempts = 0;
        $last404Time = null;
        
        while (time() - $startTime < $maxWaitSeconds) {
            $attempts++;
            
            try {
                // Don't sleep on first attempt since we already waited 5 seconds
                if ($attempts > 1) {
                    sleep($pollIntervalSeconds);
                }

                $output = $this->getPhantomOutput($phantomId, $containerId);
            } catch (\Exception $e) {
                // Handle 404 errors gracefully - container might not be ready yet
                if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Container not found')) {
                    if ($last404Time === null) {
                        $last404Time = time();
                    }
                    
                    // If we keep getting 404s for more than 30 seconds, give up
                    if (time() - $last404Time > 30) {
                        Log::error('PhantomBuster: Container not found after multiple attempts', [
                            'phantom_id' => $phantomId,
                            'container_id' => $containerId,
                            'attempts' => $attempts,
                            'post_url' => $postUrl
                        ]);
                        return [];
                    }
                    
                    Log::info('PhantomBuster: Container not ready yet, waiting...', [
                        'attempt' => $attempts,
                        'phantom_id' => $phantomId,
                        'container_id' => $containerId
                    ]);
                    continue;
                }
                
                // For other errors, re-throw
                throw $e;
            }
            
            // Check multiple possible locations for the data
            $likers = $output['data']['output'] 
                ?? $output['data']['resultObject'] 
                ?? $output['output'] 
                ?? [];
            
            $containerStatus = $output['data']['containerStatus'] ?? null;
            $agentStatus = $output['data']['agentStatus'] ?? null;
            $messages = $output['data']['messages'] ?? [];
            $progress = $output['data']['progress'] ?? null;
            
            // Log full output structure on first attempt or when finished
            if ($attempts === 1 || $containerStatus === 'not running' || $containerStatus === 'finished') {
                Log::info('PhantomBuster: Full output structure', [
                    'attempt' => $attempts,
                    'full_output' => $output,
                    'data_keys' => isset($output['data']) ? array_keys($output['data']) : [],
                    'output_keys' => isset($output['output']) && is_array($output['output']) ? array_keys($output['output']) : 'not_array',
                    'data_output_type' => isset($output['data']['output']) ? gettype($output['data']['output']) : 'not_set',
                    'data_output_sample' => isset($output['data']['output']) ? (is_array($output['data']['output']) ? array_slice($output['data']['output'], 0, 2) : substr((string)$output['data']['output'], 0, 500)) : null
                ]);
            }
            
            Log::info('PhantomBuster: Post likers status check', [
                'attempt' => $attempts,
                'container_status' => $containerStatus,
                'agent_status' => $agentStatus,
                'likers_count' => is_array($likers) ? count($likers) : 0,
                'progress' => $progress,
                'has_resultObject' => isset($output['data']['resultObject']),
                'messages' => $messages,
                'output_array_type' => isset($output['data']['output']) ? gettype($output['data']['output']) : 'not_set',
                'output_array_count' => isset($output['data']['output']) && is_array($output['data']['output']) ? count($output['data']['output']) : 'not_array'
            ]);
            
            if (is_array($likers) && !empty($likers)) {
                Log::info('PhantomBuster: Got likers data', ['count' => count($likers)]);
                return $likers;
            }
            
            // Check if phantom is finished (even if no data yet)
            if ($containerStatus === 'not running' || $containerStatus === 'finished' || $containerStatus === 'completed') {
                // Check for error messages first
                $errorDetected = false;
                $errorMessage = null;
                
                // Check messages array for errors
                if (!empty($messages) && is_array($messages)) {
                    $messagesText = is_array($messages) ? implode(' | ', array_filter($messages)) : (string)$messages;
                    Log::info('PhantomBuster: Checking messages for errors', [
                        'messages' => $messages,
                        'messages_text' => $messagesText
                    ]);
                    
                    // Check for common error patterns
                    $errorPatterns = [
                        'export limit' => 'Export limit reached - upgrade your PhantomBuster plan',
                        'slot' => 'No available slots - wait or upgrade plan',
                        'limit reached' => 'PhantomBuster limit reached',
                        'couldn\'t load' => 'Could not load post - post may be private or deleted',
                        'session' => 'LinkedIn session expired - refresh your li_at cookie',
                        'unauthorized' => 'LinkedIn authorization failed',
                        'error' => 'PhantomBuster error detected'
                    ];
                    
                    foreach ($errorPatterns as $pattern => $description) {
                        if (stripos($messagesText, $pattern) !== false) {
                            $errorDetected = true;
                            $errorMessage = $description . " (found: '$pattern')";
                            break;
                        }
                    }
                }
                
                // One final check for data in resultObject
                if (isset($output['data']['resultObject'])) {
                    $resultObject = $output['data']['resultObject'];
                    Log::info('PhantomBuster: Checking resultObject', [
                        'type' => gettype($resultObject),
                        'is_array' => is_array($resultObject),
                        'is_string' => is_string($resultObject),
                        'is_empty' => empty($resultObject),
                        'count' => is_array($resultObject) ? count($resultObject) : (is_string($resultObject) ? strlen($resultObject) : 'N/A'),
                        'sample' => is_array($resultObject) && !empty($resultObject) ? array_slice($resultObject, 0, 1) : (is_string($resultObject) ? substr($resultObject, 0, 500) : $resultObject)
                    ]);
                    
                    // If resultObject is a JSON string, try to decode it
                    if (is_string($resultObject)) {
                        $decoded = json_decode($resultObject, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            // Check if it's an error message
                            if (isset($decoded[0]['error'])) {
                                $errorDetected = true;
                                $errorText = $decoded[0]['error'];
                                
                                // Check if this is likely a session expiration issue
                                $sessionExpiredPatterns = [
                                    'cannot be displayed',
                                    'not available',
                                    'access denied',
                                    'unauthorized',
                                    'session expired'
                                ];
                                
                                $isSessionIssue = false;
                                foreach ($sessionExpiredPatterns as $pattern) {
                                    if (stripos($errorText, $pattern) !== false) {
                                        $isSessionIssue = true;
                                        $errorMessage = "LinkedIn session expired or invalid - refresh your li_at cookie. Error: " . $errorText;
                                        break;
                                    }
                                }
                                
                                if (!$isSessionIssue) {
                                    $errorMessage = "PhantomBuster error: " . $errorText;
                                }
                                
                                Log::error('PhantomBuster: Phantom returned error in resultObject', [
                                    'error' => $errorText,
                                    'post_url' => $decoded[0]['postUrl'] ?? $postUrl,
                                    'is_session_issue' => $isSessionIssue,
                                    'full_result' => $decoded
                                ]);
                                break; // Don't return error data
                            }
                            // It's valid data
                            Log::info('PhantomBuster: Found likers in resultObject (decoded from JSON string)', ['count' => count($decoded)]);
                            return $decoded;
                        }
                        
                        // Check if resultObject string contains error keywords
                        $errorPatterns = [
                            'export limit' => 'Export limit reached',
                            'couldn\'t load' => 'Could not load post',
                            'error' => 'Error in result'
                        ];
                        foreach ($errorPatterns as $pattern => $description) {
                            if (stripos($resultObject, $pattern) !== false) {
                                $errorDetected = true;
                                $errorMessage = $description . " (found in resultObject)";
                                Log::error('PhantomBuster: Error detected in resultObject string', [
                                    'error_pattern' => $pattern,
                                    'resultObject_sample' => substr($resultObject, 0, 500)
                                ]);
                                break 2;
                            }
                        }
                    }
                    
                    if (is_array($resultObject) && !empty($resultObject)) {
                        // Check if it's an error message
                        if (isset($resultObject[0]['error'])) {
                            $errorDetected = true;
                            $errorText = $resultObject[0]['error'];
                            
                            // Check if this is likely a session expiration issue
                            $sessionExpiredPatterns = [
                                'cannot be displayed',
                                'not available',
                                'access denied',
                                'unauthorized',
                                'session expired'
                            ];
                            
                            $isSessionIssue = false;
                            foreach ($sessionExpiredPatterns as $pattern) {
                                if (stripos($errorText, $pattern) !== false) {
                                    $isSessionIssue = true;
                                    $errorMessage = "LinkedIn session expired or invalid - refresh your li_at cookie. Error: " . $errorText;
                                    break;
                                }
                            }
                            
                            if (!$isSessionIssue) {
                                $errorMessage = "PhantomBuster error: " . $errorText;
                            }
                            
                            Log::error('PhantomBuster: Phantom returned error in resultObject', [
                                'error' => $errorText,
                                'post_url' => $resultObject[0]['postUrl'] ?? $postUrl,
                                'is_session_issue' => $isSessionIssue,
                                'full_result' => $resultObject
                            ]);
                            break; // Don't return error data
                        }
                        Log::info('PhantomBuster: Found likers in resultObject', ['count' => count($resultObject)]);
                        return $resultObject;
                    }
                }
                
                // Check the output array itself for errors or data
                $outputArray = $output['data']['output'] ?? null;
                if ($outputArray !== null) {
                    if (is_string($outputArray)) {
                        // Check for specific PhantomBuster messages in the log string
                        if (stripos($outputArray, 'Every post is scraped') !== false || stripos($outputArray, 'input is empty') !== false) {
                            $errorDetected = true;
                            $errorMessage = "Post already scraped or input empty - PhantomBuster may have cached this post. Try a different post URL or clear PhantomBuster cache.";
                            Log::warning('PhantomBuster: Post already scraped message detected', [
                                'output_sample' => substr($outputArray, 0, 500),
                                'post_url' => $postUrl
                            ]);
                        } elseif (stripos($outputArray, 'export limit') !== false || stripos($outputArray, 'limit reached') !== false) {
                            $errorDetected = true;
                            $errorMessage = "Export limit reached - found in output string";
                        } else {
                            // Try to decode as JSON
                            $decodedOutput = json_decode($outputArray, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedOutput)) {
                                if (isset($decodedOutput[0]['error'])) {
                                    $errorDetected = true;
                                    $errorMessage = "PhantomBuster output error: " . $decodedOutput[0]['error'];
                                } elseif (!empty($decodedOutput)) {
                                    // It's actually data, not an error!
                                    Log::info('PhantomBuster: Found likers in output array (decoded from string)', ['count' => count($decodedOutput)]);
                                    return $decodedOutput;
                                }
                            }
                        }
                    } elseif (is_array($outputArray) && !empty($outputArray)) {
                        // Check if first item is an error
                        if (isset($outputArray[0]['error'])) {
                            $errorDetected = true;
                            $errorMessage = "PhantomBuster output error: " . $outputArray[0]['error'];
                        } else {
                            // It's actual data!
                            Log::info('PhantomBuster: Found likers in output array', ['count' => count($outputArray)]);
                            return $outputArray;
                        }
                    }
                }
                
                // Log detailed error information
                if ($errorDetected) {
                    Log::error('PhantomBuster: Phantom finished with error - no likers returned', [
                        'error_message' => $errorMessage,
                        'container_status' => $containerStatus,
                        'agent_status' => $agentStatus,
                        'messages' => $messages,
                        'post_url' => $postUrl,
                        'output_keys' => isset($output['data']) ? array_keys($output['data']) : [],
                        'has_resultObject' => isset($output['data']['resultObject']),
                        'resultObject_type' => isset($output['data']['resultObject']) ? gettype($output['data']['resultObject']) : null,
                        'output_array_type' => isset($output['data']['output']) ? gettype($output['data']['output']) : null,
                        'output_array_sample' => isset($output['data']['output']) ? (is_string($output['data']['output']) ? substr($output['data']['output'], 0, 500) : (is_array($output['data']['output']) ? array_slice($output['data']['output'], 0, 2) : $output['data']['output'])) : null,
                        'full_output_sample' => isset($output['data']) ? array_slice($output['data'], 0, 5) : null
                    ]);
                } else {
                    Log::warning('PhantomBuster: Phantom finished but no likers found (no error detected)', [
                        'container_status' => $containerStatus,
                        'agent_status' => $agentStatus,
                        'messages' => $messages,
                        'post_url' => $postUrl,
                        'output_keys' => isset($output['data']) ? array_keys($output['data']) : [],
                        'has_resultObject' => isset($output['data']['resultObject']),
                        'resultObject_type' => isset($output['data']['resultObject']) ? gettype($output['data']['resultObject']) : null,
                        'output_array_type' => isset($output['data']['output']) ? gettype($output['data']['output']) : null,
                        'output_array_sample' => isset($output['data']['output']) ? (is_string($output['data']['output']) ? substr($output['data']['output'], 0, 500) : (is_array($output['data']['output']) ? array_slice($output['data']['output'], 0, 2) : $output['data']['output'])) : null,
                        'full_output_sample' => isset($output['data']) ? array_slice($output['data'], 0, 5) : null,
                        'note' => 'Phantom finished immediately - likely export limit reached (you have 1055 exports). Check PhantomBuster dashboard to confirm.'
                    ]);
                }
                break;
            }
            
            if ($containerStatus === 'error') {
                Log::error('PhantomBuster: Phantom error', [
                    'messages' => $messages,
                    'agent_status' => $agentStatus
                ]);
                break;
            }
        }

        $errorNote = 'Phantom may have hit export limits, slot limits, or session issues. Check PhantomBuster dashboard for details.';
        if (isset($errorMessage) && stripos($errorMessage, 'session expired') !== false) {
            $errorNote = 'LinkedIn session cookie (li_at) appears to be expired. Please refresh it from the Social Accounts page.';
        }
        
        Log::error('PhantomBuster: Timeout or no data for post likers', [
            'post_url' => $postUrl,
            'waited_seconds' => time() - $startTime,
            'max_wait_seconds' => $maxWaitSeconds,
            'note' => $errorNote,
            'error_message' => $errorMessage ?? null
        ]);
        return [];
    }


    private function getSessionCookie(): ?string
    {
        return $this->sessionCookieOverride ?? config('services.phantombuster.linkedin_session_cookie');
    }

    private function getUserAgent(): ?string
    {
        return $this->userAgentOverride ?? config('services.phantombuster.linkedin_user_agent');
    }

    /**
     * Scrape a LinkedIn profile to get full profile data including email
     * 
     * @param string $profileUrl LinkedIn profile URL (e.g., https://www.linkedin.com/in/username/)
     * @param string|null $sessionCookie Optional override for li_at
     * @param string|null $userAgent Optional override for user agent
     * @param array|null $identities Optional identities array format
     * @param int $maxWaitSeconds Maximum seconds to wait for the phantom to finish
     * @param int $pollIntervalSeconds Poll interval while waiting
     * @return array Profile data including email if available
     * @throws \Exception
     */
    public function scrapeLinkedInProfile(
        string $profileUrl,
        ?string $sessionCookie = null,
        ?string $userAgent = null,
        ?array $identities = null,
        int $maxWaitSeconds = 300,
        int $pollIntervalSeconds = 10
    ): array {
        $this->sessionCookieOverride = $sessionCookie;
        $this->userAgentOverride = $userAgent;

        try {
            $phantomId = config('services.phantombuster.linkedin_profile_scraper_phantom_id');

            if (!$phantomId) {
                $phantomId = $this->findPhantomByName('linkedin profile scraper');
            }

            if (!$phantomId) {
                Log::warning('PhantomBuster: LinkedIn Profile Scraper phantom not found, cannot scrape profile');
                throw new \Exception('LinkedIn Profile Scraper phantom not configured');
            }

         
            $arguments = [
                'urls' => [$profileUrl],
                'spreadsheetUrl' => 'org-storage://leads/by-list/7041576098381409', 
                'emailChooser' => 'phantombuster',
                'enrichWithCompanyData' => false,
                'updateMonitoringMetadata' => false,
                'pushResultToCRM' => false,
                'numberOfAddsPerLaunch' => 1,
            ];

            // Use identities array format if provided, otherwise use top-level sessionCookie/userAgent
            if (!empty($identities) && is_array($identities)) {
                $formattedIdentities = [];
                foreach ($identities as $identity) {
                    if (is_array($identity) && isset($identity['sessionCookie'])) {
                        $formattedIdentity = [
                            'sessionCookie' => $identity['sessionCookie']
                        ];
                        
                        if (isset($identity['identityId']) && !empty($identity['identityId'])) {
                            $formattedIdentity['identityId'] = $identity['identityId'];
                        }
                        
                        if (isset($identity['userAgent']) && !empty($identity['userAgent'])) {
                            $formattedIdentity['userAgent'] = $identity['userAgent'];
                        } elseif ($userAgent) {
                            $formattedIdentity['userAgent'] = $userAgent;
                        } elseif ($this->getUserAgent()) {
                            $formattedIdentity['userAgent'] = $this->getUserAgent();
                        }
                        
                        $formattedIdentities[] = $formattedIdentity;
                    }
                }
                
                if (!empty($formattedIdentities)) {
                    $arguments['identities'] = $formattedIdentities;
                    
                    Log::info('PhantomBuster: Using identities array format', [
                        'identities_count' => count($formattedIdentities),
                        'has_identityId' => isset($formattedIdentities[0]['identityId']) && !empty($formattedIdentities[0]['identityId'])
                    ]);
                }
            } else {
                $sessionCookieValue = $this->getSessionCookie();
                if ($sessionCookieValue) {
                    $arguments['sessionCookie'] = $sessionCookieValue;
                }

                $userAgentValue = $this->getUserAgent();
                if ($userAgentValue) {
                    $arguments['userAgent'] = $userAgentValue;
                }
            }

            Log::info('PhantomBuster: Launching profile scraper', [
                'phantom_id' => $phantomId,
                'profile_url' => $profileUrl,
                'has_identities' => !empty($arguments['identities'] ?? null),
                'arguments_keys' => array_keys($arguments),
                'identities_count' => isset($arguments['identities']) ? count($arguments['identities']) : 0,
                'identity_has_id' => isset($arguments['identities'][0]['identityId']) && !empty($arguments['identities'][0]['identityId']),
                'full_arguments' => $arguments // Log full arguments for debugging
            ]);

            $launchResponse = $this->launchPhantom($phantomId, $arguments);
            $containerId = $launchResponse['containerId'] ?? $launchResponse['data']['containerId'] ?? null;

            if (!$containerId) {
                throw new \Exception("Failed to get container ID from PhantomBuster launch for profile scraper");
            }

            sleep(5);
            
            Log::info('PhantomBuster: Starting to poll for profile data', [
                'container_id' => $containerId,
                'max_wait_seconds' => $maxWaitSeconds,
                'poll_interval_seconds' => $pollIntervalSeconds
            ]);

            $startTime = time();
            $attempts = 0;
            $profileData = null;

            while (time() - $startTime < $maxWaitSeconds) {
                $attempts++;

                if ($attempts > 1) {
                    sleep($pollIntervalSeconds);
                }

                try {
                    $output = $this->getPhantomOutput($phantomId, $containerId);
                } catch (\Exception $e) {
                    if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Container not found')) {
                        Log::info('PhantomBuster: Profile scraper container not ready yet', [
                            'attempt' => $attempts,
                            'container_id' => $containerId
                        ]);
                        continue;
                    }
                    throw $e;
                }

                $resultObject = $output['data']['resultObject'] ?? null;
                $outputData = $output['data']['output'] ?? null;
                $messages = $output['data']['messages'] ?? [];
                $progress = $output['data']['progress'] ?? null;
                
                if (!empty($messages) || !empty($progress)) {
                    Log::info('PhantomBuster: Profile scraper messages and progress', [
                        'attempt' => $attempts,
                        'messages' => $messages,
                        'progress' => $progress,
                        'messages_count' => is_array($messages) ? count($messages) : 0
                    ]);
                }
                
                if (!empty($messages)) {
                    $errorMessages = array_filter($messages, function($msg) {
                        return is_string($msg) && (
                            stripos($msg, 'error') !== false ||
                            stripos($msg, 'failed') !== false ||
                            stripos($msg, 'invalid') !== false ||
                            stripos($msg, 'expired') !== false ||
                            stripos($msg, 'Invalid argument') !== false
                        );
                    });
                    
                    if (!empty($errorMessages)) {
                        Log::warning('PhantomBuster: Error messages in output', [
                            'profile_url' => $profileUrl,
                            'messages' => $messages,
                            'error_messages' => array_values($errorMessages)
                        ]);
                    }
                }

                if ($resultObject) {
                    if (is_string($resultObject)) {
                        $decoded = json_decode($resultObject, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $profileData = is_array($decoded) && isset($decoded[0]) ? $decoded[0] : $decoded;
                        }
                    } elseif (is_array($resultObject)) {
                        $profileData = isset($resultObject[0]) ? $resultObject[0] : $resultObject;
                    }
                }

                if (!$profileData && $outputData) {
                    if (is_string($outputData)) {
                        $trimmed = trim($outputData);
                        if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                            $decoded = json_decode($outputData, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $profileData = is_array($decoded) && isset($decoded[0]) ? $decoded[0] : $decoded;
                            }
                        }
                    } elseif (is_array($outputData)) {
                        $profileData = isset($outputData[0]) ? $outputData[0] : $outputData;
                    }
                }

                $containerStatus = $output['data']['containerStatus'] ?? null;
                $agentStatus = $output['data']['agentStatus'] ?? null;

                Log::info('PhantomBuster: Profile scraper status check', [
                    'attempt' => $attempts,
                    'container_status' => $containerStatus,
                    'agent_status' => $agentStatus,
                    'has_profile_data' => !empty($profileData),
                    'has_email' => isset($profileData['email']) && !empty($profileData['email']),
                    'has_resultObject' => !empty($resultObject),
                    'has_outputData' => !empty($outputData),
                    'resultObject_type' => $resultObject ? gettype($resultObject) : null,
                    'outputData_type' => $outputData ? gettype($outputData) : null,
                    'output_keys' => array_keys($output['data'] ?? []),
                    'profile_data_keys' => $profileData ? array_keys($profileData) : []
                ]);

                if ($profileData && isset($profileData['email']) && !empty($profileData['email'])) {
                    Log::info('PhantomBuster: Successfully scraped profile with email', [
                        'profile_url' => $profileUrl,
                        'email_found' => true
                    ]);
                    return $profileData;
                }

                if ($containerStatus === 'not running' || $containerStatus === 'finished' || $containerStatus === 'completed') {
                    if (!$profileData) {
                        Log::warning('PhantomBuster: Profile scraper finished but no profile data extracted', [
                            'profile_url' => $profileUrl,
                            'attempt' => $attempts,
                            'waited_seconds' => time() - $startTime,
                            'output_structure' => [
                                'has_resultObject' => !empty($resultObject),
                                'has_outputData' => !empty($outputData),
                                'resultObject_type' => $resultObject ? gettype($resultObject) : null,
                                'resultObject_preview' => $resultObject ? (is_string($resultObject) ? substr($resultObject, 0, 500) : (is_array($resultObject) ? array_keys($resultObject) : gettype($resultObject))) : null,
                                'outputData_type' => $outputData ? gettype($outputData) : null,
                                'outputData_preview' => $outputData ? (is_string($outputData) ? substr($outputData, 0, 500) : (is_array($outputData) ? array_keys($outputData) : gettype($outputData))) : null,
                                'messages' => $messages,
                                'progress' => $progress,
                                'all_output_keys' => array_keys($output['data'] ?? []),
                                'full_output_data_keys' => isset($output['data']) ? array_keys($output['data']) : []
                            ]
                        ]);
                    } else {
                        Log::warning('PhantomBuster: Profile scraped but no email found', [
                            'profile_url' => $profileUrl,
                            'profile_data_keys' => array_keys($profileData),
                            'profile_data_sample' => array_slice($profileData, 0, 5, true) // First 5 keys for debugging
                        ]);
                        return $profileData;
                    }
                    break;
                }
            }

            Log::warning('PhantomBuster: Profile scraper finished with no data', [
                'profile_url' => $profileUrl,
                'waited_seconds' => time() - $startTime
            ]);

            return $profileData ?? [];
        } catch (\Throwable $th) {
            Log::error('PhantomBuster: Failed to scrape profile', [
                'profile_url' => $profileUrl,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

}

