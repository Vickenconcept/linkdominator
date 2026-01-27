<?php

namespace App\Jobs;

use App\Models\Audience;
use App\Models\AudienceList;
use App\Services\PhantomBusterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchCompetitorFollowersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public int $audiencePkId;
    public string $companyUrl;
    public string $sessionCookie;
    public string $userAgent;
    
    private int $emailDispatchCount = 0; // Track email dispatches per job instance

    public function __construct(int $userId, int $audiencePkId, string $companyUrl, string $sessionCookie, string $userAgent)
    {
        $this->userId = $userId;
        $this->audiencePkId = $audiencePkId;
        $this->companyUrl = $companyUrl;
        $this->sessionCookie = $sessionCookie;
        $this->userAgent = $userAgent;
    }

    public function handle(): void
    {
        $audience = Audience::find($this->audiencePkId);
        if (!$audience) {
            Log::warning('FetchCompetitorFollowersJob: Audience not found', ['audiencePkId' => $this->audiencePkId]);
            return;
        }

        $service = new PhantomBusterService();
        $uniqueByPublicId = [];
        $created = 0;

        Log::info('FetchCompetitorFollowersJob: started with PhantomBuster', [
            'audience_id' => $audience->audience_id,
            'user_id' => $this->userId,
            'company_url' => $this->companyUrl
        ]);

        try {
            // Get already-scraped post URLs from audience source_meta to skip them
            $scrapedPostUrls = [];
            if ($audience->source_meta) {
                $meta = json_decode($audience->source_meta, true);
                if (isset($meta['scraped_post_urls']) && is_array($meta['scraped_post_urls'])) {
                    $scrapedPostUrls = $meta['scraped_post_urls'];
                }
            }
            
            Log::info('FetchCompetitorFollowersJob: Checking for already-scraped posts', [
                'audience_id' => $audience->audience_id,
                'already_scraped_count' => count($scrapedPostUrls),
                'company_url' => $this->companyUrl
            ]);
            
            // Fetch company post engagers (people who liked company posts)
            // This doesn't require admin access and works for any company
            // Pass scraped post URLs to skip them
            $result = $service->fetchCompanyPostEngagers(
                $this->companyUrl,
                null,
                600,
                15,
                $this->sessionCookie,
                $this->userAgent,
                $scrapedPostUrls, // Pass already-scraped posts
                $audience // Pass audience to update source_meta with newly scraped posts
            );
            
            $followers = $result['engagers'] ?? $result;
            $newlyScrapedPosts = $result['newly_scraped_posts'] ?? [];
            
            Log::info('FetchCompetitorFollowersJob: PhantomBuster returned engagers', [
                'company_url' => $this->companyUrl,
                'engagers_count' => is_array($followers) ? count($followers) : 0,
                'newly_scraped_posts_count' => count($newlyScrapedPosts)
            ]);
            
            // Update audience source_meta with newly scraped post URLs
            if (!empty($newlyScrapedPosts)) {
                $existingScraped = $scrapedPostUrls;
                $allScraped = array_unique(array_merge($existingScraped, $newlyScrapedPosts));
                
                $meta = json_decode($audience->source_meta, true) ?? [];
                $meta['scraped_post_urls'] = $allScraped;
                $audience->source_meta = json_encode($meta);
                $audience->save();
                
                Log::info('FetchCompetitorFollowersJob: Updated audience with scraped posts', [
                    'audience_id' => $audience->audience_id,
                    'newly_scraped' => count($newlyScrapedPosts),
                    'total_scraped' => count($allScraped)
                ]);
            }

            foreach ($followers as $follower) {
                // Skip if not an array (safety check)
                if (!is_array($follower)) {
                    Log::warning('FetchCompetitorFollowersJob: Skipping non-array follower', [
                        'type' => gettype($follower),
                        'value' => is_string($follower) ? substr($follower, 0, 100) : $follower
                    ]);
                    continue;
                }
                
                $this->storeFollower($audience, $follower, $uniqueByPublicId, $created);
            }

            Log::info('FetchCompetitorFollowersJob: PhantomBuster followers stored', [
                'audience_id' => $audience->audience_id,
                'stored' => $created,
                'total_fetched' => count($followers)
            ]);
        } catch (\Exception $e) {
            Log::error('FetchCompetitorFollowersJob: PhantomBuster failed', [
                'audience_id' => $audience->audience_id,
                'company_url' => $this->companyUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Store a follower from PhantomBuster response
     *
     * @param Audience $audience
     * @param array $follower Follower data from PhantomBuster
     * @param array $seen Track seen public IDs to avoid duplicates
     * @param int $created Counter for created records
     * @return void
     */
    private function storeFollower(Audience $audience, array $follower, array &$seen, int &$created): void
    {
        if (empty($follower)) {
            return;
        }

        // PhantomBuster returns different field names, handle various formats
        // Check profileLink first (PhantomBuster's format)
        $publicId = null;
        $profileLink = $follower['profileLink'] 
            ?? $follower['profileUrl'] 
            ?? $follower['profile_url'] 
            ?? null;

        // Extract ID from profileLink/profileUrl
        if ($profileLink) {
            // Try to extract public identifier from URL
            // Format can be: https://www.linkedin.com/in/username/ or https://www.linkedin.com/in/ACoAA.../
            if (preg_match('/linkedin\.com\/in\/([^\/\?]+)/', $profileLink, $matches)) {
                $extractedId = $matches[1];
                // Use extracted ID (could be username or internal ID)
                $publicId = $extractedId;
            }
        }

        // Fallback to other fields
        if (!$publicId) {
            $publicId = $follower['publicIdentifier'] 
                ?? $follower['public_identifier'] 
                ?? $follower['memberId'] // Use memberId as fallback
                ?? null;
        }

        if ($publicId && isset($seen[$publicId])) {
            return; // Skip duplicates
        }

        // Extract name - PhantomBuster may use different field names
        $fullName = $follower['fullName'] 
            ?? $follower['name'] 
            ?? trim(($follower['firstName'] ?? $follower['first_name'] ?? '') . ' ' . ($follower['lastName'] ?? $follower['last_name'] ?? ''));
        
        $first = $follower['firstName'] 
            ?? $follower['first_name'] 
            ?? ($fullName ? explode(' ', $fullName, 2)[0] : null);
        
        $last = $follower['lastName'] 
            ?? $follower['last_name'] 
            ?? ($fullName && str_contains($fullName, ' ') ? explode(' ', $fullName, 2)[1] : null);

        // Extract job title and company
        // PhantomBuster uses 'occupation' field for job title
        $jobTitle = $follower['occupation'] 
            ?? $follower['headline'] 
            ?? $follower['title'] 
            ?? $follower['jobTitle'] 
            ?? null;
        
        $companyName = $follower['company'] 
            ?? $follower['companyName'] 
            ?? $follower['company_name'] 
            ?? null;

        // Extract location
        $location = $follower['location'] 
            ?? $follower['locationName'] 
            ?? null;

        // Profile URL - use profileLink if available, otherwise construct from publicId
        $profileUrl = $follower['profileLink'] 
            ?? $follower['profileUrl'] 
            ?? $follower['profile_url'] 
            ?? ($publicId ? 'https://www.linkedin.com/in/' . $publicId . '/' : null);
        
        // Log profile URL extraction for debugging
        if (!$profileUrl && isset($follower['profileLink'])) {
            Log::warning('FetchCompetitorFollowersJob: profileLink exists but is empty/null', [
                'profileLink_value' => $follower['profileLink'],
                'public_id' => $publicId,
                'follower_keys' => array_keys($follower)
            ]);
        }

        // Connection degree (1st, 2nd, 3rd) - PhantomBuster includes this
        $connectionDegree = $follower['connectionDegree'] 
            ?? $follower['connection_degree'] 
            ?? $follower['degree'] 
            ?? $follower['networkDistance']
            ?? $follower['network_distance']
            ?? null;

        // Log what PhantomBuster returned
        Log::info('📊 POST-SCRAPING AUDIENCE: PhantomBuster response data', [
            'public_id' => $publicId,
            'full_name' => $fullName,
            'connectionDegree' => $connectionDegree,
            'connectionDegree_source' => isset($follower['connectionDegree']) ? 'connectionDegree' : 
                                        (isset($follower['connection_degree']) ? 'connection_degree' : 
                                        (isset($follower['degree']) ? 'degree' : 
                                        (isset($follower['networkDistance']) ? 'networkDistance' : 
                                        (isset($follower['network_distance']) ? 'network_distance' : 'not_found')))),
            'profileLink_received' => $follower['profileLink'] ?? null,
            'profileUrl_extracted' => $profileUrl,
            'all_follower_keys' => array_keys($follower),
            'sample_follower_data' => [
                'firstName' => $first,
                'lastName' => $last,
                'jobTitle' => $jobTitle,
                'company' => $companyName,
                'location' => $location,
                'profileUrl' => $profileUrl
            ]
        ]);

        // Convert connection degree to distance format (1, 2, 3 or DISTANCE_1, DISTANCE_2, DISTANCE_3)
        $con_distance = null;
        if ($connectionDegree !== null) {
            // Handle different formats: "1", "1st", "DISTANCE_1", 1, etc.
            if (is_numeric($connectionDegree)) {
                $con_distance = 'DISTANCE_' . (int)$connectionDegree;
            } elseif (is_string($connectionDegree)) {
                // Extract number from strings like "1st", "2nd", "3rd", "DISTANCE_1"
                if (preg_match('/(\d+)/', $connectionDegree, $matches)) {
                    $con_distance = 'DISTANCE_' . $matches[1];
                } elseif (str_starts_with(strtoupper($connectionDegree), 'DISTANCE_')) {
                    $con_distance = strtoupper($connectionDegree);
                } else {
                    $con_distance = $connectionDegree; // Use as-is if can't parse
                }
            }
        }

        // Store in audience_lists
        $savedItem = AudienceList::updateOrCreate(
            [
                'audience_id' => $audience->audience_id,
                'con_public_identifier' => $publicId,
            ],
            [
                'con_first_name' => $first,
                'con_last_name' => $last,
                'con_job_title' => $jobTitle,
                'con_company_name' => $companyName,
                'con_location' => $location,
                'con_profile_url' => $profileUrl,
                'con_distance' => $con_distance, // Save network distance
                'con_last_activity' => now(), // Use current time as last activity
            ]
        );

        // Log what was saved to database
        Log::info('💾 POST-SCRAPING AUDIENCE: Saved to audience_lists table from PhantomBuster', [
            'id' => $savedItem->id,
            'audience_id' => $audience->audience_id,
            'public_id' => $publicId,
            'name' => $fullName,
            'connectionDegree_received' => $connectionDegree,
            'con_distance_saved' => $savedItem->con_distance,
            'con_distance_was_null' => $savedItem->con_distance === null,
            'profile_url_saved' => $savedItem->con_profile_url,
            'profile_url_was_null' => $savedItem->con_profile_url === null,
            'all_saved_fields' => [
                'con_first_name' => $savedItem->con_first_name,
                'con_last_name' => $savedItem->con_last_name,
                'con_public_identifier' => $savedItem->con_public_identifier,
                'con_profile_url' => $savedItem->con_profile_url,
                'con_job_title' => $savedItem->con_job_title,
                'con_company_name' => $savedItem->con_company_name,
                'con_location' => $savedItem->con_location,
                'con_distance' => $savedItem->con_distance
            ]
        ]);

        if ($publicId) {
            $seen[$publicId] = true;
        }
        $created++;
        
        // Dispatch email fetch job if email is missing (max 5 at a time to allow other users)
        if (empty($savedItem->con_email) && !empty($publicId)) {
            // Get user to check daily limit
            $user = \App\Models\User::find($this->userId);
            if ($user) {
                // Check and reset daily limit
                $today = now()->toDateString();
                $resetDate = $user->daily_profile_email_scraping_reset_at 
                    ? \Carbon\Carbon::parse($user->daily_profile_email_scraping_reset_at)->toDateString() 
                    : null;

                if ($resetDate !== $today) {
                    $user->update([
                        'daily_profile_email_scraping_count' => 0,
                        'daily_profile_email_scraping_reset_at' => $today
                    ]);
                    $user->refresh();
                }
                
                // Only dispatch if under daily limit and we haven't dispatched 5 yet in this job
                $dailyLimit = config('services.email_scraping.daily_limit_per_user', 100);
                if ($user->daily_profile_email_scraping_count < $dailyLimit) {
                    if ($this->emailDispatchCount < 5) {
                        \App\Jobs\FetchAudienceEmailJob::dispatch($savedItem->id, $publicId)
                            ->onQueue('phantombuster');
                        $this->emailDispatchCount++;
                        
                        Log::info('FetchCompetitorFollowersJob: Dispatched email fetch job', [
                            'audience_list_id' => $savedItem->id,
                            'public_identifier' => $publicId,
                            'dispatched_count' => $this->emailDispatchCount,
                            'max_per_job' => 5
                        ]);
                    } else {
                        Log::info('FetchCompetitorFollowersJob: Reached max email dispatch limit (5) for this job', [
                            'audience_list_id' => $savedItem->id
                        ]);
                    }
                } else {
                    Log::info('FetchCompetitorFollowersJob: User daily limit reached, skipping email dispatch', [
                        'user_id' => $this->userId,
                        'count' => $user->daily_profile_email_scraping_count
                    ]);
                }
            }
        }
    }
}


