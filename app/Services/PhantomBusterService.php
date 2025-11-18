<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $response = Http::withHeaders([
            'X-Phantombuster-Key-1' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($url, $payload);

        if ($response->failed()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['error'] ?? $response->body();
            
            // Provide helpful error message for "Agent not found"
            if ($response->status() === 400 && str_contains(strtolower($errorMessage), 'agent not found')) {
                $helpfulError = "Phantom ID '{$phantomId}' not found in your workspace.\n\n";
                $helpfulError .= "This usually means:\n";
                $helpfulError .= "1. The phantom needs to be added to your workspace first\n";
                $helpfulError .= "2. Go to https://phantombuster.com/phantoms and add 'LinkedIn Company Follower Collector'\n";
                $helpfulError .= "3. After adding, get your instance ID from the phantom's URL\n";
                $helpfulError .= "4. The ID in the public URL is a template ID, not your instance\n\n";
                $helpfulError .= "Original error: {$errorMessage}";
                
                Log::error("PhantomBuster launch failed - Agent not found", [
                    'status' => $response->status(),
                    'body' => $errorBody,
                    'phantom_id' => $phantomId,
                    'helpful_message' => $helpfulError
                ]);
                
                throw new \Exception($helpfulError);
            }
            
            Log::error("PhantomBuster launch failed:", [
                'status' => $response->status(),
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
        $containerId = $data['containerId'] 
            ?? $data['container_id'] 
            ?? $data['id'] 
            ?? $data['outputId']
            ?? null;
        
        Log::info('PhantomBuster: Phantom launched successfully', [
            'phantom_id' => $phantomId,
            'container_id' => $containerId,
            'response_keys' => array_keys($data)
        ]);
        
        // If containerId is in a nested structure, try to extract it
        if (!$containerId && isset($data['data'])) {
            $containerId = $data['data']['containerId'] 
                ?? $data['data']['container_id'] 
                ?? $data['data']['id'] 
                ?? null;
        }
        
        // Add containerId to response if we found it
        if ($containerId) {
            $data['containerId'] = $containerId;
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

        $response = Http::withHeaders([
            'X-Phantombuster-Key-1' => $this->apiKey
        ])->get($url, [
            'containerId' => $containerId
        ]);

        if ($response->failed()) {
            Log::error("PhantomBuster output fetch failed:", [
                'status' => $response->status(),
                'body' => $response->body(),
                'phantom_id' => $phantomId,
                'container_id' => $containerId
            ]);
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

        $response = Http::withHeaders([
            'X-Phantombuster-Key-1' => $this->apiKey
        ])->get($url);

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
     * 3. For each post, extract commenters (using LinkedIn Post Commenters Export)
     *
     * @param string $companyUrl LinkedIn company URL
     * @param string|null $phantomId Not used anymore (kept for backward compatibility)
     * @param int $maxWaitSeconds Maximum seconds to wait
     * @param int $pollIntervalSeconds Seconds between status checks
     * @return array Array of engager profiles
     */
    public function fetchCompanyPostEngagers(
        string $companyUrl,
        ?string $phantomId = null,
        int $maxWaitSeconds = 600,
        int $pollIntervalSeconds = 15,
        ?string $sessionCookie = null,
        ?string $userAgent = null
    ): array {
        $this->sessionCookieOverride = $sessionCookie;
        $this->userAgentOverride = $userAgent;
        try {
            Log::info('PhantomBuster: Starting to fetch company post engagers', [
                'company_url' => $companyUrl
            ]);

            // Step 1: Get company posts using RapidAPI
            $rapidApiService = new \App\Services\RapidApiService();
            $posts = $rapidApiService->fetch_company_posts($companyUrl, 1);
            
            if (empty($posts) || !isset($posts['data']) || empty($posts['data'])) {
                Log::warning('PhantomBuster: No posts found for company', ['company_url' => $companyUrl]);
                return [];
            }

            Log::info('PhantomBuster: Found company posts from RapidAPI', [
                'company_url' => $companyUrl,
                'posts_count' => count($posts['data'])
            ]);

            // Step 2: Extract post URLs from RapidAPI posts
            $postUrls = $this->extractPostUrlsFromRapidApi($posts['data']);
            
            if (empty($postUrls)) {
                Log::warning('PhantomBuster: No post URLs found in posts', ['company_url' => $companyUrl]);
                return [];
            }

            $postLimit = max(1, (int) config('services.phantombuster.company_posts_limit', 5));
            if (count($postUrls) > $postLimit) {
                $postUrls = array_slice($postUrls, 0, $postLimit);
                Log::info('PhantomBuster: Limiting number of posts for this run', [
                    'limit' => $postLimit,
                    'post_urls_count' => count($postUrls)
                ]);
            }

            Log::info('PhantomBuster: Extracted post URLs', [
                'post_urls_count' => count($postUrls),
                'sample_urls' => array_slice($postUrls, 0, 3)
            ]);

            // Step 3: For each post, get likers and commenters
            $allEngagers = [];
            $processedPosts = 0;
            
            foreach ($postUrls as $postUrl) {
                $processedPosts++;
                Log::info('PhantomBuster: Processing post', [
                    'post_number' => $processedPosts,
                    'total_posts' => count($postUrls),
                    'post_url' => $postUrl
                ]);

                try {
                    // Get likers for this post
                    $likers = $this->fetchPostLikers($postUrl, $maxWaitSeconds, $pollIntervalSeconds);
                    Log::info('PhantomBuster: Got likers for post', [
                        'post_url' => $postUrl,
                        'likers_count' => count($likers)
                    ]);
                    $allEngagers = array_merge($allEngagers, $likers);
                } catch (\Exception $e) {
                    Log::warning('PhantomBuster: Failed to get likers for post', [
                        'post_url' => $postUrl,
                        'error' => $e->getMessage()
                    ]);
                }

                try {
                    // Get commenters for this post
                    $commenters = $this->fetchPostCommenters($postUrl, $maxWaitSeconds, $pollIntervalSeconds);
                    Log::info('PhantomBuster: Got commenters for post', [
                        'post_url' => $postUrl,
                        'commenters_count' => count($commenters)
                    ]);
                    $allEngagers = array_merge($allEngagers, $commenters);
                } catch (\Exception $e) {
                    Log::warning('PhantomBuster: Failed to get commenters for post', [
                        'post_url' => $postUrl,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Remove duplicates by public identifier
            $uniqueEngagers = [];
            $seen = [];
            foreach ($allEngagers as $engager) {
                $publicId = $engager['publicIdentifier'] 
                    ?? $engager['public_identifier'] 
                    ?? $engager['profileUrl'] 
                    ?? null;
                
                // Extract from profileUrl if needed
                if (!$publicId && isset($engager['profileUrl'])) {
                    if (preg_match('/linkedin\.com\/in\/([^\/]+)/', $engager['profileUrl'], $matches)) {
                        $publicId = $matches[1];
                    }
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
                'unique_engagers' => count($uniqueEngagers)
            ]);

            return $uniqueEngagers;
        } finally {
            $this->sessionCookieOverride = null;
            $this->userAgentOverride = null;
        }
    }

    /**
     * Extract post URLs from RapidAPI posts array
     *
     * @param array $posts Posts from RapidAPI
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

        // Poll for completion
        $startTime = time();
        $attempts = 0;
        
        while (time() - $startTime < $maxWaitSeconds) {
            $attempts++;
            sleep($pollIntervalSeconds);

            $output = $this->getPhantomOutput($phantomId, $containerId);
            
            // Check multiple possible locations for the data
            $likers = $output['data']['output'] 
                ?? $output['data']['resultObject'] 
                ?? $output['output'] 
                ?? [];
            
            $containerStatus = $output['data']['containerStatus'] ?? null;
            $agentStatus = $output['data']['agentStatus'] ?? null;
            $messages = $output['data']['messages'] ?? [];
            $progress = $output['data']['progress'] ?? null;
            
            Log::info('PhantomBuster: Post likers status check', [
                'attempt' => $attempts,
                'container_status' => $containerStatus,
                'agent_status' => $agentStatus,
                'likers_count' => is_array($likers) ? count($likers) : 0,
                'progress' => $progress,
                'has_resultObject' => isset($output['data']['resultObject']),
                'messages' => $messages
            ]);
            
            if (is_array($likers) && !empty($likers)) {
                Log::info('PhantomBuster: Got likers data', ['count' => count($likers)]);
                return $likers;
            }
            
            // Check if phantom is finished (even if no data yet)
            if ($containerStatus === 'not running' || $containerStatus === 'finished' || $containerStatus === 'completed') {
                // One final check for data in resultObject
                if (isset($output['data']['resultObject'])) {
                    $resultObject = $output['data']['resultObject'];
                    Log::info('PhantomBuster: Checking resultObject', [
                        'type' => gettype($resultObject),
                        'is_array' => is_array($resultObject),
                        'is_string' => is_string($resultObject),
                        'is_empty' => empty($resultObject),
                        'count' => is_array($resultObject) ? count($resultObject) : (is_string($resultObject) ? strlen($resultObject) : 'N/A'),
                        'sample' => is_array($resultObject) && !empty($resultObject) ? array_slice($resultObject, 0, 1) : (is_string($resultObject) ? substr($resultObject, 0, 200) : $resultObject)
                    ]);
                    
                    // If resultObject is a JSON string, try to decode it
                    if (is_string($resultObject)) {
                        $decoded = json_decode($resultObject, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            // Check if it's an error message
                            if (isset($decoded[0]['error'])) {
                                Log::warning('PhantomBuster: Phantom returned error in resultObject', [
                                    'error' => $decoded[0]['error'],
                                    'post_url' => $decoded[0]['postUrl'] ?? $postUrl
                                ]);
                                break; // Don't return error data
                            }
                            // It's valid data
                            Log::info('PhantomBuster: Found likers in resultObject (decoded from JSON string)', ['count' => count($decoded)]);
                            return $decoded;
                        }
                    }
                    
                    if (is_array($resultObject) && !empty($resultObject)) {
                        // Check if it's an error message
                        if (isset($resultObject[0]['error'])) {
                            Log::warning('PhantomBuster: Phantom returned error in resultObject', [
                                'error' => $resultObject[0]['error'],
                                'post_url' => $resultObject[0]['postUrl'] ?? $postUrl
                            ]);
                            break; // Don't return error data
                        }
                        Log::info('PhantomBuster: Found likers in resultObject', ['count' => count($resultObject)]);
                        return $resultObject;
                    }
                }
                
                Log::warning('PhantomBuster: Phantom finished but no likers found', [
                    'container_status' => $containerStatus,
                    'messages' => $messages,
                    'output_keys' => isset($output['data']) ? array_keys($output['data']) : [],
                    'has_resultObject' => isset($output['data']['resultObject']),
                    'resultObject_type' => isset($output['data']['resultObject']) ? gettype($output['data']['resultObject']) : null
                ]);
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

        Log::warning('PhantomBuster: Timeout or no data for post likers', [
            'post_url' => $postUrl,
            'waited_seconds' => time() - $startTime
        ]);
        return [];
    }

    /**
     * Fetch commenters for a specific post URL
     *
     * @param string $postUrl LinkedIn post URL
     * @param int $maxWaitSeconds
     * @param int $pollIntervalSeconds
     * @return array Array of commenter profiles
     */
    private function fetchPostCommenters(
        string $postUrl,
        int $maxWaitSeconds = 300,
        int $pollIntervalSeconds = 10
    ): array {
        $phantomId = config('services.phantombuster.linkedin_post_commenters_phantom_id');
        
        if (!$phantomId) {
            $phantomId = $this->findPhantomByName('linkedin post commenters');
        }
        
        if (!$phantomId) {
            Log::warning('PhantomBuster: LinkedIn Post Commenters phantom not found, skipping commenters extraction');
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
            throw new \Exception("Failed to get container ID from PhantomBuster launch for post commenters");
        }

        // Poll for completion
        $startTime = time();
        $attempts = 0;
        
        while (time() - $startTime < $maxWaitSeconds) {
            $attempts++;
            sleep($pollIntervalSeconds);

            $output = $this->getPhantomOutput($phantomId, $containerId);
            
            // Check multiple possible locations for the data
            $commenters = $output['data']['output'] 
                ?? $output['data']['resultObject'] 
                ?? $output['output'] 
                ?? [];
            
            $containerStatus = $output['data']['containerStatus'] ?? null;
            $agentStatus = $output['data']['agentStatus'] ?? null;
            $messages = $output['data']['messages'] ?? [];
            $progress = $output['data']['progress'] ?? null;
            
            Log::info('PhantomBuster: Post commenters status check', [
                'attempt' => $attempts,
                'container_status' => $containerStatus,
                'agent_status' => $agentStatus,
                'commenters_count' => is_array($commenters) ? count($commenters) : 0,
                'progress' => $progress,
                'has_resultObject' => isset($output['data']['resultObject']),
                'messages' => $messages
            ]);
            
            if (is_array($commenters) && !empty($commenters)) {
                Log::info('PhantomBuster: Got commenters data', ['count' => count($commenters)]);
                return $commenters;
            }
            
            // Check if phantom is finished (even if no data yet)
            if ($containerStatus === 'not running' || $containerStatus === 'finished' || $containerStatus === 'completed') {
                // One final check for data in resultObject
                if (isset($output['data']['resultObject'])) {
                    $resultObject = $output['data']['resultObject'];
                    Log::info('PhantomBuster: Checking resultObject', [
                        'type' => gettype($resultObject),
                        'is_array' => is_array($resultObject),
                        'is_string' => is_string($resultObject),
                        'is_empty' => empty($resultObject),
                        'count' => is_array($resultObject) ? count($resultObject) : (is_string($resultObject) ? strlen($resultObject) : 'N/A'),
                        'sample' => is_array($resultObject) && !empty($resultObject) ? array_slice($resultObject, 0, 1) : (is_string($resultObject) ? substr($resultObject, 0, 200) : $resultObject)
                    ]);
                    
                    // If resultObject is a JSON string, try to decode it
                    if (is_string($resultObject)) {
                        $decoded = json_decode($resultObject, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            // Check if it's an error message
                            if (isset($decoded[0]['error'])) {
                                Log::warning('PhantomBuster: Phantom returned error in resultObject', [
                                    'error' => $decoded[0]['error'],
                                    'post_url' => $decoded[0]['postUrl'] ?? $postUrl
                                ]);
                                break; // Don't return error data
                            }
                            // It's valid data
                            Log::info('PhantomBuster: Found commenters in resultObject (decoded from JSON string)', ['count' => count($decoded)]);
                            return $decoded;
                        }
                    }
                    
                    if (is_array($resultObject) && !empty($resultObject)) {
                        // Check if it's an error message
                        if (isset($resultObject[0]['error'])) {
                            Log::warning('PhantomBuster: Phantom returned error in resultObject', [
                                'error' => $resultObject[0]['error'],
                                'post_url' => $resultObject[0]['postUrl'] ?? $postUrl
                            ]);
                            break; // Don't return error data
                        }
                        Log::info('PhantomBuster: Found commenters in resultObject', ['count' => count($resultObject)]);
                        return $resultObject;
                    }
                }
                
                Log::warning('PhantomBuster: Phantom finished but no commenters found', [
                    'container_status' => $containerStatus,
                    'messages' => $messages,
                    'output_keys' => isset($output['data']) ? array_keys($output['data']) : [],
                    'has_resultObject' => isset($output['data']['resultObject']),
                    'resultObject_type' => isset($output['data']['resultObject']) ? gettype($output['data']['resultObject']) : null
                ]);
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

        Log::warning('PhantomBuster: Timeout or no data for post commenters', [
            'post_url' => $postUrl,
            'waited_seconds' => time() - $startTime
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
}

