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
            $email = $profileData['professionalEmail'] 
                ?? $profileData['email'] 
                ?? $profileData['emailAddress'] 
                ?? $profileData['contactInfo']['emailAddress'] 
                ?? $profileData['contactInfo']['email'] 
                ?? null;

            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $audienceListItem->update(['con_email' => $email]);
                
                Log::info('FetchAudienceEmailJob: Successfully fetched and updated email', [
                    'audience_list_id' => $this->audienceListItemId,
                    'email' => $email,
                    'profile_url' => $profileUrl
                ]);
            } else {
                Log::info('FetchAudienceEmailJob: Profile scraped but no valid email found', [
                    'audience_list_id' => $this->audienceListItemId,
                    'profile_url' => $profileUrl,
                    'profile_data_keys' => array_keys($profileData)
                ]);
            }
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
}
