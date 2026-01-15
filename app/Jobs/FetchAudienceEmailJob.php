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

class FetchAudienceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $audienceListItemId;
    public string $publicIdentifier;

    /**
     * Create a new job instance.
     */
    public function __construct(int $audienceListItemId, string $publicIdentifier)
    {
        $this->audienceListItemId = $audienceListItemId;
        $this->publicIdentifier = $publicIdentifier;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $audienceListItem = AudienceList::find($this->audienceListItemId);
            
            if (!$audienceListItem) {
                Log::warning('FetchAudienceEmailJob: Audience list item not found', [
                    'audience_list_id' => $this->audienceListItemId
                ]);
                return;
            }

            // Skip if email already exists
            if (!empty($audienceListItem->con_email)) {
                Log::info('FetchAudienceEmailJob: Email already exists, skipping', [
                    'audience_list_id' => $this->audienceListItemId
                ]);
                return;
            }

            // Build profile URL from public identifier
            $profileUrl = "https://www.linkedin.com/in/{$this->publicIdentifier}/";
            
            // Get user's LinkedIn integration for session cookie
            $audience = Audience::where('audience_id', $audienceListItem->audience_id)->first();
            if (!$audience) {
                Log::warning('FetchAudienceEmailJob: Audience not found', [
                    'audience_list_id' => $this->audienceListItemId,
                    'audience_id' => $audienceListItem->audience_id
                ]);
                return;
            }

            $user = User::find($audience->user_id);
            if (!$user) {
                Log::warning('FetchAudienceEmailJob: User not found', [
                    'audience_list_id' => $this->audienceListItemId,
                    'user_id' => $audience->user_id
                ]);
                return;
            }

            // Check and reset daily limit if needed
            $this->checkAndResetDailyLimit($user);
            
            // Refresh user model to get latest count after potential reset
            $user->refresh();
            
            // Check daily limit before processing
            $dailyLimit = config('services.email_scraping.daily_limit_per_user', 100);
            if ($user->daily_profile_email_scraping_count >= $dailyLimit) {
                Log::warning('FetchAudienceEmailJob: Daily limit exceeded', [
                    'user_id' => $user->id,
                    'current_count' => $user->daily_profile_email_scraping_count,
                    'limit' => $dailyLimit
                ]);
                throw new \Exception("Daily email scraping limit reached ({$dailyLimit} profiles/day). Please try again tomorrow.");
            }

            $integration = Integration::where('user_id', $user->id)
                ->where('oauth_provider', 'linkedin')
                ->whereNotNull('linkedin_session_cookie')
                ->latest('linkedin_session_verified_at')
                ->first();

            if (!$integration) {
                Log::warning('FetchAudienceEmailJob: LinkedIn session cookie not found', [
                    'audience_list_id' => $this->audienceListItemId,
                    'user_id' => $user->id
                ]);
                return;
            }

            // Build identities array
            $identities = [[
                'sessionCookie' => $integration->linkedin_session_cookie,
                'userAgent' => $integration->linkedin_user_agent ?? config('services.phantombuster.linkedin_user_agent')
            ]];

            // Add identityId if available
            if (isset($integration->linkedin_identity_id) && !empty($integration->linkedin_identity_id)) {
                $identities[0]['identityId'] = $integration->linkedin_identity_id;
            }

            Log::info('FetchAudienceEmailJob: Fetching email using PhantomBuster Profile Scraper', [
                'audience_list_id' => $this->audienceListItemId,
                'profile_url' => $profileUrl,
                'public_identifier' => $this->publicIdentifier
            ]);

            $service = new PhantomBusterService();
            $profileData = $service->scrapeLinkedInProfile(
                $profileUrl,
                null, // sessionCookie - using identities instead
                null, // userAgent - using identities instead
                $identities,
                300, // maxWaitSeconds
                10   // pollIntervalSeconds
            );

            // Extract email from profile data
            // PhantomBuster Profile Scraper returns email in 'professionalEmail' field
            // Check multiple possible fields, but ensure value is non-empty (not empty string)
            $email = null;
            
            // Try professionalEmail first (most common field)
            if (isset($profileData['professionalEmail']) && 
                is_string($profileData['professionalEmail']) && 
                trim($profileData['professionalEmail']) !== '') {
                $email = trim($profileData['professionalEmail']);
            }
            // Try email field
            elseif (isset($profileData['email']) && 
                    is_string($profileData['email']) && 
                    trim($profileData['email']) !== '') {
                $email = trim($profileData['email']);
            }
            // Try emailAddress field
            elseif (isset($profileData['emailAddress']) && 
                    is_string($profileData['emailAddress']) && 
                    trim($profileData['emailAddress']) !== '') {
                $email = trim($profileData['emailAddress']);
            }
            // Try nested contactInfo fields
            elseif (isset($profileData['contactInfo']) && is_array($profileData['contactInfo'])) {
                if (isset($profileData['contactInfo']['emailAddress']) && 
                    is_string($profileData['contactInfo']['emailAddress']) && 
                    trim($profileData['contactInfo']['emailAddress']) !== '') {
                    $email = trim($profileData['contactInfo']['emailAddress']);
                } elseif (isset($profileData['contactInfo']['email']) && 
                          is_string($profileData['contactInfo']['email']) && 
                          trim($profileData['contactInfo']['email']) !== '') {
                    $email = trim($profileData['contactInfo']['email']);
                }
            }

            // Log what we found for debugging
            Log::info('FetchAudienceEmailJob: Email extraction result', [
                'audience_list_id' => $this->audienceListItemId,
                'profile_url' => $profileUrl,
                'email_found' => !empty($email),
                'email_value' => $email ? substr($email, 0, 50) . '...' : null,
                'professionalEmail_value' => isset($profileData['professionalEmail']) ? (is_string($profileData['professionalEmail']) ? substr($profileData['professionalEmail'], 0, 50) : gettype($profileData['professionalEmail'])) : 'not_set',
                'professionalEmail_empty' => isset($profileData['professionalEmail']) && empty(trim($profileData['professionalEmail'] ?? '')),
                'profile_data_keys' => array_keys($profileData)
            ]);

            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $audienceListItem->update([
                    'con_email' => $email,
                    'email_fetch_status' => 'completed'
                ]);
                
                Log::info('FetchAudienceEmailJob: Successfully fetched and updated email', [
                    'audience_list_id' => $this->audienceListItemId,
                    'email' => $email,
                    'profile_url' => $profileUrl
                ]);
            } else {
                // Mark that email fetch was attempted but no email found
                $audienceListItem->update([
                    'email_fetch_attempted_at' => now(),
                    'email_fetch_status' => 'completed'
                ]);
                
                Log::warning('FetchAudienceEmailJob: Profile scraped but no valid email found', [
                    'audience_list_id' => $this->audienceListItemId,
                    'profile_url' => $profileUrl,
                    'email_attempted' => $email,
                    'email_valid' => $email ? filter_var($email, FILTER_VALIDATE_EMAIL) : false,
                    'profile_data_keys' => array_keys($profileData)
                ]);
            }

            // Update daily count (increment by 1 for this single profile scrape)
            $user->increment('daily_profile_email_scraping_count', 1);
            
            Log::info('FetchAudienceEmailJob: Daily count updated', [
                'user_id' => $user->id,
                'new_count' => $user->fresh()->daily_profile_email_scraping_count
            ]);
        } catch (\Throwable $th) {
            Log::error('FetchAudienceEmailJob: Failed to fetch email', [
                'audience_list_id' => $this->audienceListItemId,
                'public_identifier' => $this->publicIdentifier,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            
            // Re-throw to mark job as failed (will retry if configured)
            throw $th;
        }
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
            
            Log::info('FetchAudienceEmailJob: Daily limit reset', [
                'user_id' => $user->id,
                'reset_date' => $today
            ]);
        }
    }
}
