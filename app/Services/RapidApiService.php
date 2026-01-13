<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RapidApiService
{
    /**
     * Linkedin API Service By RapidApi
     */
    const linkedin_provider_api = "https://fresh-linkedin-profile-data.p.rapidapi.com";

    /**
     * Search LinkedIn posts by keyword with optional filters
     * 
     * @param string $keyword Search keyword
     * @param int $page Page number
     * @param string $dateRange Date range filter
     * @param array $filters Optional filters: min_likes, min_comments, min_shares, limit
     * @return array
     */
    public function search_posts($keyword, $page = 1, $dateRange = 'past-month', $filters = [])
    {
        $url = self::linkedin_provider_api . '/search-posts';

        $apiKey = config('services.rapidapi.key');
        
        // Check if API key exists
        if (!$apiKey) {
            Log::error("RAPIDAPI_KEY not found in environment variables");
            throw new \Exception("RAPIDAPI_KEY not configured");
        }

        $headers = [
            "x-rapidapi-key" => $apiKey,
            "x-rapidapi-host" => "fresh-linkedin-profile-data.p.rapidapi.com",
            "Content-Type" => "application/json"
        ];

        // Map our date range to API format (RapidAPI expects title case phrases e.g. "Past Week")
        $datePosted = $this->normalizeDatePostedParameter($dateRange);

        // Get filter values with defaults
        $minLikes = $filters['min_likes'] ?? null;
        $minComments = $filters['min_comments'] ?? null;
        $minShares = $filters['min_shares'] ?? null;
        $limit = $filters['limit'] ?? 50; // Default limit to reduce API calls

        $sortBy = ($datePosted !== null && $datePosted !== '') ? 'Top match' : 'Latest';

        $searchKeyword = $this->normalizeSearchKeyword($keyword);

        // Build payload - request exactly what we need
        $payload = [
            "search_keywords" => $searchKeyword,
            "sort_by" => $sortBy,
            "content_type" => "",
            "from_member" => [],
            "from_company" => [],
            "mentioning_member" => [],
            "mentioning_company" => [],
            "author_company" => [],
            "author_industry" => [],
            "author_keyword" => "",
            "page" => $page
        ];

        if ($datePosted !== null) {
            $payload["date_posted"] = $datePosted;
        }

        // RapidAPI rejects engagement filters when date_posted is supplied.
        $canSendEngagementFilters = ($datePosted === null || $datePosted === '');

        if ($canSendEngagementFilters) {
            // Add engagement filters - RapidAPI should filter server-side and return only matching posts
            if ($minLikes !== null) {
                $payload["min_likes"] = $minLikes;
            }
            if ($minComments !== null) {
                $payload["min_comments"] = $minComments;
            }
            if ($minShares !== null) {
                $payload["min_shares"] = $minShares;
            }
        } elseif ($minLikes !== null || $minComments !== null || $minShares !== null) {
            Log::info("RapidAPI limitation: engagement filters can't be combined with date_posted. Falling back to client-side filtering.", [
                'keyword' => $keyword,
                'date_posted' => $datePosted,
                'min_likes' => $minLikes,
                'min_comments' => $minComments,
                'min_shares' => $minShares,
            ]);
        }
        
        // Note: If RapidAPI supports pagination limits, we could add "per_page" or similar
        // but most APIs have fixed page sizes, so we'll filter client-side if needed

        // Log API request with filters
        Log::info("RapidAPI request - requesting filtered posts directly from API:", [
            'endpoint' => 'search-posts',
            'keyword' => $keyword,
            'normalized_keyword' => $searchKeyword,
            'page' => $page,
            'date_range' => $datePosted,
            'original_date_range' => $dateRange,
            'sort_by' => $sortBy,
            'min_likes' => $minLikes,
            'min_comments' => $minComments,
            'min_shares' => $minShares,
            'desired_limit' => $limit,
            'note' => 'If RapidAPI supports filters, will return only matching posts. Otherwise will filter client-side.'
        ]);
        
        $response = Http::withHeaders($headers)
            ->post($url, $payload);
        
        if ($response->failed()) {
            Log::error("RapidAPI request failed:", [
                'status' => $response->status(),
                'body' => $response->body(),
                'keyword' => $keyword,
                'page' => $page,
                'filters' => $filters
            ]);
            
            // If it's a 429 (rate limit) or quota error, throw a more helpful exception
            if ($response->status() == 429) {
                throw new \Exception("RapidAPI quota exceeded. Please upgrade your plan or wait for quota reset.");
            }
        }
        
        $result = $response->throw()->json();
        
        // Check if RapidAPI filtered server-side or if we need client-side filtering
        if (isset($result['data']) && is_array($result['data'])) {
            $originalCount = count($result['data']);
            $needsFiltering = false;
            
            // Check if RapidAPI did server-side filtering by checking if any posts are below threshold
            // If filters were sent and we find posts below threshold, RapidAPI didn't filter server-side
            if ($minLikes !== null || $minComments !== null || $minShares !== null) {
                foreach (array_slice($result['data'], 0, 5) as $samplePost) {
                    // Sample first 5 posts to check if filtering worked
                    $likes = (int)($samplePost['num_likes'] ?? $samplePost['likes'] ?? 0);
                    $comments = (int)($samplePost['num_comments'] ?? $samplePost['comments'] ?? 0);
                    $shares = (int)($samplePost['num_shares'] ?? $samplePost['shares'] ?? 0);
                    
                    if (($minLikes !== null && $likes < $minLikes) ||
                        ($minComments !== null && $comments < $minComments) ||
                        ($minShares !== null && $shares < $minShares)) {
                        $needsFiltering = true;
                        break;
                    }
                }
            }
            
            // Only do client-side filtering if RapidAPI didn't filter server-side
            // This ensures we get exactly what we need without processing unnecessary posts
            if ($needsFiltering) {
                $filteredData = [];
                $processedCount = 0; // Track how many we actually process
                
                foreach ($result['data'] as $post) {
                    $processedCount++;
                    
                    $likes = (int)($post['num_likes'] ?? $post['likes'] ?? 0);
                    $comments = (int)($post['num_comments'] ?? $post['comments'] ?? 0);
                    $shares = (int)($post['num_shares'] ?? $post['shares'] ?? 0);
                    
                    // Apply filters - skip posts that don't meet criteria
                    if ($minLikes !== null && $likes < $minLikes) {
                        continue; // Skip - doesn't meet criteria
                    }
                    if ($minComments !== null && $comments < $minComments) {
                        continue; // Skip - doesn't meet criteria
                    }
                    if ($minShares !== null && $shares < $minShares) {
                        continue; // Skip - doesn't meet criteria
                    }
                    
                    // Post meets all criteria - add it
                    $filteredData[] = $post;
                    
                    // CRITICAL: Stop immediately when we reach limit
                    // Don't process remaining posts - we have what we need!
                    if (count($filteredData) >= $limit) {
                        Log::info("Early stop: Reached limit of {$limit} posts, stopped processing at post #{$processedCount}", [
                            'keyword' => $keyword,
                            'processed' => $processedCount,
                            'returned' => count($filteredData),
                            'skipped' => $originalCount - $processedCount
                        ]);
                        break; // Stop processing - we have enough!
                    }
                }
                
                $result['data'] = $filteredData;
                $result['filtered_count'] = count($filteredData);
                $result['original_count'] = $originalCount;
                $result['server_filtered'] = false;
                
                Log::info("Client-side filtering applied: Filtered {$originalCount} posts to " . count($filteredData) . " matching criteria", [
                    'keyword' => $keyword,
                    'original' => $originalCount,
                    'filtered' => count($filteredData),
                    'min_likes' => $minLikes,
                    'note' => 'RapidAPI does not support server-side filtering, applied client-side'
                ]);
            } else {
                // RapidAPI filtered server-side or no filters needed - use results directly
                // Just apply limit if needed
                if (count($result['data']) > $limit) {
                    $result['data'] = array_slice($result['data'], 0, $limit);
                }
                
                $result['filtered_count'] = count($result['data']);
                $result['original_count'] = $originalCount;
                $result['server_filtered'] = true;
                
                Log::info("Using RapidAPI server-filtered results directly", [
                    'keyword' => $keyword,
                    'count' => count($result['data']),
                    'min_likes' => $minLikes,
                    'note' => 'All posts meet criteria - no client-side filtering needed'
                ]);
            }
        }
        
        return $result;
    }

    public function fetch_profile_posts($profile_url)
    {
        $url = self::linkedin_provider_api . '/get-profile-posts';

        $headers = [
            "x-rapidapi-key" => config('services.rapidapi.key'),
            "x-rapidapi-host" => "fresh-linkedin-profile-data.p.rapidapi.com"
        ];

        $params = [
            "linkedin_url" => $profile_url,
            "type" => "posts"
        ];

        return Http::withHeaders($headers)
            ->get($url, $params)
            ->throw()
            ->json();
    }

    /**
     * Fetch recent posts from a company page
     * Uses the correct RapidAPI endpoint: /get-company-posts
     *
     * @param string $companyUrl LinkedIn company URL (e.g., https://www.linkedin.com/company/microsoft/)
     * @param int $page Pagination page (converted to start parameter: 0 for page 1, 50 for page 2, etc.)
     * @param string $sortBy Sort by: "top" or "recent" (default: "recent")
     * @return array
     */
    public function fetch_company_posts(string $companyUrl, int $page = 1, string $sortBy = 'recent'): array
    {
        // Try the company posts endpoint first
        $url = self::linkedin_provider_api . '/get-company-posts';

        $apiKey = config('services.rapidapi.key');
        if (!$apiKey) {
            Log::error("RAPIDAPI_KEY not found in environment variables");
            throw new \Exception("RAPIDAPI_KEY not configured");
        }

        $headers = [
            "x-rapidapi-key" => $apiKey,
            "x-rapidapi-host" => "fresh-linkedin-profile-data.p.rapidapi.com"
        ];

        // Convert page to start parameter: page 1 = 0, page 2 = 50, etc.
        $start = ($page - 1) * 50;

        $params = [
            "linkedin_url" => $companyUrl,
            "start" => $start,
            "sort_by" => $sortBy
        ];

        Log::info('RapidAPI company posts request', [
            'company_url' => $companyUrl,
            'page' => $page,
            'start' => $start,
            'sort_by' => $sortBy,
            'endpoint' => '/get-company-posts'
        ]);

        $response = Http::withHeaders($headers)->get($url, $params);
        
        if ($response->failed()) {
            Log::error("RapidAPI company posts request failed:", [
                'status' => $response->status(),
                'body' => $response->body(),
                'company' => $companyUrl,
                'page' => $page
            ]);
            
            if ($response->status() == 429) {
                throw new \Exception("RapidAPI quota exceeded. Please upgrade your plan or wait for quota reset.");
            }
            
            // If /get-company-posts doesn't exist, fall back to search-posts
            if ($response->status() == 404) {
                Log::warning('RapidAPI: /get-company-posts endpoint not found, falling back to /search-posts');
                return $this->fetch_company_posts_fallback($companyUrl, $page);
            }
        }

        return $response->throw()->json();
    }

    /**
     * Fallback method using search-posts endpoint (if get-company-posts doesn't exist)
     *
     * @param string $companyUrl
     * @param int $page
     * @return array
     */
    private function fetch_company_posts_fallback(string $companyUrl, int $page): array
    {
        $url = self::linkedin_provider_api . '/search-posts';

        $apiKey = config('services.rapidapi.key');
        $headers = [
            "x-rapidapi-key" => $apiKey,
            "x-rapidapi-host" => "fresh-linkedin-profile-data.p.rapidapi.com",
            "Content-Type" => "application/json"
        ];

        $companyIdentifier = $this->normalizeCompanyIdentifier($companyUrl);

        $payload = [
            "search_keywords" => "",
            "sort_by" => "Latest",
            "content_type" => "",
            "from_member" => [],
            "from_company" => [],
            "mentioning_member" => [],
            "mentioning_company" => [],
            "author_company" => [$companyIdentifier],
            "author_industry" => [],
            "author_keyword" => "",
            "page" => $page
        ];

        Log::info('RapidAPI company posts fallback request', [
            'company_url' => $companyUrl,
            'normalized_company' => $companyIdentifier,
            'page' => $page
        ]);

        $response = Http::withHeaders($headers)->post($url, $payload);
        
        if ($response->failed()) {
            if ($response->status() == 429) {
                throw new \Exception("RapidAPI quota exceeded. Please upgrade your plan or wait for quota reset.");
            }
        }

        return $response->throw()->json();
    }

    private function normalizeDatePostedParameter(?string $dateRange): ?string
    {
        if ($dateRange === null) {
            return null;
        }

        $dateRange = trim($dateRange);
        $normalized = strtolower(str_replace(['_', ' '], '-', $dateRange));

        $map = [
            'any-time' => '',
            'anytime' => '',
            'past-week' => 'Past week',
            'past-2-weeks' => 'Past 2 weeks',
            'past-3-weeks' => 'Past 3 weeks',
            'past-month' => 'Past month',
        ];

        if (array_key_exists($normalized, $map)) {
            return $map[$normalized];
        }

        // If caller already passed a supported value (e.g. "Past Week"), use it as-is
        return $dateRange;
    }

    private function normalizeSearchKeyword(string $keyword): string
    {
        $normalized = str_replace(['&', '/', '\\'], ' ', $keyword);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return trim($normalized);
    }

    private function normalizeCompanyIdentifier(string $companyUrl): string
    {
        $identifier = trim($companyUrl);

        // If it already looks like a slug (no protocol), just return
        if (!str_contains($identifier, '://')) {
            return $identifier;
        }

        $path = parse_url($identifier, PHP_URL_PATH) ?: '';
        $segments = array_values(array_filter(explode('/', $path)));

        return $segments[count($segments) - 1] ?? $identifier;
    }

}