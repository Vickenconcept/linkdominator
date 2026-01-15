<?php

namespace App\Jobs;

use App\Models\Audience;
use App\Models\AudienceList;
use App\Models\Integration;
use App\Models\User;
use App\Services\PhantomBusterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchAudienceEmailBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2; // Retry up to 2 times
    public $timeout = 1200; // 20 minutes timeout for batch processing
    public $backoff = [120, 300]; // Wait 2min, 5min between retries

    /**
     * Array of audience list item IDs to process
     * @var array<int>
     */
    public array $audienceListItemIds;

    /**
     * User ID who initiated the batch
     * @var int
     */
    public int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $audienceListItemIds, int $userId)
    {
        $this->audienceListItemIds = $audienceListItemIds;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = User::find($this->userId);
            if (!$user) {
                Log::warning('FetchAudienceEmailBatchJob: User not found', [
                    'user_id' => $this->userId
                ]);
                return;
            }

            // Check daily limit before processing
            $this->checkAndResetDailyLimit($user);
            
            $dailyLimit = config('services.email_scraping.daily_limit_per_user', 100);
            $profileCount = count($this->audienceListItemIds);
            if ($user->daily_profile_email_scraping_count + $profileCount > $dailyLimit) {
                Log::warning('FetchAudienceEmailBatchJob: Daily limit exceeded', [
                    'user_id' => $this->userId,
                    'current_count' => $user->daily_profile_email_scraping_count,
                    'requested_count' => $profileCount,
                    'limit' => $dailyLimit
                ]);
                throw new \Exception("Daily email scraping limit reached ({$dailyLimit} profiles/day). Please try again tomorrow.");
            }

            // Load all audience list items
            $audienceListItems = AudienceList::whereIn('id', $this->audienceListItemIds)->get();
            
            if ($audienceListItems->isEmpty()) {
                Log::warning('FetchAudienceEmailBatchJob: No audience list items found', [
                    'audience_list_ids' => $this->audienceListItemIds
                ]);
                return;
            }

            // Get user's LinkedIn integration
            $integration = Integration::where('user_id', $user->id)
                ->where('oauth_provider', 'linkedin')
                ->whereNotNull('linkedin_session_cookie')
                ->latest('linkedin_session_verified_at')
                ->first();

            if (!$integration) {
                Log::warning('FetchAudienceEmailBatchJob: LinkedIn session cookie not found', [
                    'user_id' => $user->id
                ]);
                return;
            }

            // Build identities array
            $identities = [[
                'sessionCookie' => $integration->linkedin_session_cookie,
                'userAgent' => $integration->linkedin_user_agent ?? config('services.phantombuster.linkedin_user_agent')
            ]];

            if (isset($integration->linkedin_identity_id) && !empty($integration->linkedin_identity_id)) {
                $identities[0]['identityId'] = $integration->linkedin_identity_id;
            }

            // Collect profile URLs and map them to audience list item IDs
            $profileUrls = [];
            $urlToItemMap = [];
            
            foreach ($audienceListItems as $item) {
                // Skip if email already exists
                if (!empty($item->con_email)) {
                    Log::info('FetchAudienceEmailBatchJob: Email already exists, skipping', [
                        'audience_list_id' => $item->id
                    ]);
                    continue;
                }

                // Build profile URL from public identifier
                $publicIdentifier = $item->con_public_identifier;
                if (empty($publicIdentifier) && !empty($item->con_profile_url)) {
                    if (preg_match('/\/in\/([^\/\?]+)/', $item->con_profile_url, $matches)) {
                        $publicIdentifier = $matches[1];
                    }
                }

                if (empty($publicIdentifier)) {
                    Log::warning('FetchAudienceEmailBatchJob: No public identifier found', [
                        'audience_list_id' => $item->id
                    ]);
                    continue;
                }

                $profileUrl = "https://www.linkedin.com/in/{$publicIdentifier}/";
                $profileUrls[] = $profileUrl;
                $urlToItemMap[$profileUrl] = $item->id;
            }

            if (empty($profileUrls)) {
                Log::info('FetchAudienceEmailBatchJob: No valid profile URLs to process');
                return;
            }

            Log::info('FetchAudienceEmailBatchJob: Starting batch email fetch', [
                'user_id' => $user->id,
                'profile_count' => count($profileUrls),
                'audience_list_ids' => $this->audienceListItemIds
            ]);

            // Call PhantomBuster service with batch URLs
            $service = new PhantomBusterService();
            $results = $service->scrapeLinkedInProfilesBatch(
                $profileUrls,
                $identities,
                600, // maxWaitSeconds - 10 minutes for batch
                15   // pollIntervalSeconds
            );

            // Process results and update audience list items
            $updatedCount = 0;
            $notFoundCount = 0;
            $errorCount = 0;

            Log::info('FetchAudienceEmailBatchJob: Processing batch results', [
                'results_count' => count($results),
                'expected_count' => count($profileUrls),
                'result_urls' => array_keys($results),
                'expected_urls' => $profileUrls
            ]);

            // If no results, mark all as attempted
            if (empty($results)) {
                Log::warning('FetchAudienceEmailBatchJob: No results returned from PhantomBuster', [
                    'profile_urls' => $profileUrls,
                    'audience_list_ids' => $this->audienceListItemIds
                ]);
                
                foreach ($audienceListItems as $item) {
                    if (empty($item->con_email) && empty($item->email_fetch_attempted_at)) {
                        $item->update([
                            'email_fetch_attempted_at' => now(),
                            'email_fetch_status' => 'completed'
                        ]);
                        $notFoundCount++;
                    }
                }
            }

            foreach ($results as $profileUrl => $profileData) {
                $audienceListItemId = $urlToItemMap[$profileUrl] ?? null;
                
                if (!$audienceListItemId) {
                    continue;
                }

                $audienceListItem = $audienceListItems->firstWhere('id', $audienceListItemId);
                if (!$audienceListItem) {
                    continue;
                }

                try {
                    // Extract email from profile data
                    $email = $this->extractEmail($profileData);

                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $audienceListItem->update([
                            'con_email' => $email,
                            'email_fetch_status' => 'completed'
                        ]);
                        $updatedCount++;
                        
                        Log::info('FetchAudienceEmailBatchJob: Successfully fetched email', [
                            'audience_list_id' => $audienceListItemId,
                            'email' => substr($email, 0, 50) . '...'
                        ]);
                    } else {
                        // Mark that email fetch was attempted but no email found
                        $audienceListItem->update([
                            'email_fetch_attempted_at' => now(),
                            'email_fetch_status' => 'completed'
                        ]);
                        $notFoundCount++;
                        
                        Log::warning('FetchAudienceEmailBatchJob: No valid email found', [
                            'audience_list_id' => $audienceListItemId,
                            'profile_url' => $profileUrl
                        ]);
                    }
                } catch (\Throwable $th) {
                    $errorCount++;
                    Log::error('FetchAudienceEmailBatchJob: Error processing result', [
                        'audience_list_id' => $audienceListItemId,
                        'error' => $th->getMessage()
                    ]);
                }
            }

            // Update daily count
            $user->increment('daily_profile_email_scraping_count', count($profileUrls));

            Log::info('FetchAudienceEmailBatchJob: Batch processing completed', [
                'user_id' => $user->id,
                'total_profiles' => count($profileUrls),
                'updated' => $updatedCount,
                'not_found' => $notFoundCount,
                'errors' => $errorCount
            ]);

        } catch (\Throwable $th) {
            Log::error('FetchAudienceEmailBatchJob: Failed to process batch', [
                'user_id' => $this->userId,
                'audience_list_ids' => $this->audienceListItemIds,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            
            // Re-throw to mark job as failed
            throw $th;
        }
    }

    /**
     * Extract email from profile data
     */
    private function extractEmail(array $profileData): ?string
    {
        // Try professionalEmail first (most common field)
        if (isset($profileData['professionalEmail']) && 
            is_string($profileData['professionalEmail']) && 
            trim($profileData['professionalEmail']) !== '') {
            return trim($profileData['professionalEmail']);
        }
        // Try email field
        elseif (isset($profileData['email']) && 
                is_string($profileData['email']) && 
                trim($profileData['email']) !== '') {
            return trim($profileData['email']);
        }
        // Try emailAddress field
        elseif (isset($profileData['emailAddress']) && 
                is_string($profileData['emailAddress']) && 
                trim($profileData['emailAddress']) !== '') {
            return trim($profileData['emailAddress']);
        }
        // Try nested contactInfo fields
        elseif (isset($profileData['contactInfo']) && is_array($profileData['contactInfo'])) {
            if (isset($profileData['contactInfo']['emailAddress']) && 
                is_string($profileData['contactInfo']['emailAddress']) && 
                trim($profileData['contactInfo']['emailAddress']) !== '') {
                return trim($profileData['contactInfo']['emailAddress']);
            } elseif (isset($profileData['contactInfo']['email']) && 
                      is_string($profileData['contactInfo']['email']) && 
                      trim($profileData['contactInfo']['email']) !== '') {
                return trim($profileData['contactInfo']['email']);
            }
        }

        return null;
    }

    /**
     * Check and reset daily limit if needed
     */
    private function checkAndResetDailyLimit(User $user): void
    {
        $today = now()->toDateString();
        $resetDate = $user->daily_profile_email_scraping_reset_at 
            ? \Carbon\Carbon::parse($user->daily_profile_email_scraping_reset_at)->toDateString() 
            : null;

        // Reset if it's a new day
        if ($resetDate !== $today) {
            $user->update([
                'daily_profile_email_scraping_count' => 0,
                'daily_profile_email_scraping_reset_at' => $today
            ]);
            
            Log::info('FetchAudienceEmailBatchJob: Daily limit reset', [
                'user_id' => $user->id,
                'reset_date' => $today
            ]);
        }
    }
}
