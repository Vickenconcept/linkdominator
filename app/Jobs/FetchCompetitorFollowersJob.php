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
            // Fetch company post engagers (people who liked/commented on company posts)
            // This doesn't require admin access and works for any company
            $followers = $service->fetchCompanyPostEngagers(
                $this->companyUrl,
                null,
                600,
                15,
                $this->sessionCookie,
                $this->userAgent
            );
            
            Log::info('FetchCompetitorFollowersJob: PhantomBuster returned engagers', [
                'company_url' => $this->companyUrl,
                'engagers_count' => count($followers)
            ]);

            foreach ($followers as $follower) {
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
        $publicId = $follower['publicIdentifier'] 
            ?? $follower['public_identifier'] 
            ?? $follower['profileUrl'] 
            ?? null;

        // Extract from profileUrl if needed: https://www.linkedin.com/in/username/
        if (!$publicId && isset($follower['profileUrl'])) {
            $url = $follower['profileUrl'];
            if (preg_match('/linkedin\.com\/in\/([^\/]+)/', $url, $matches)) {
                $publicId = $matches[1];
            }
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
        $jobTitle = $follower['headline'] 
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

        // Profile URL
        $profileUrl = $follower['profileUrl'] 
            ?? $follower['profile_url'] 
            ?? ($publicId ? 'https://www.linkedin.com/in/' . $publicId . '/' : null);

        // Connection degree (1st, 2nd, 3rd) - PhantomBuster includes this
        $connectionDegree = $follower['connectionDegree'] 
            ?? $follower['connection_degree'] 
            ?? $follower['degree'] 
            ?? null;

        // Store in audience_lists
        AudienceList::updateOrCreate(
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
                'con_last_activity' => now(), // Use current time as last activity
                // Note: If audience_lists table has a connection_degree column, add it here
                // 'con_connection_degree' => $connectionDegree,
            ]
        );

        if ($publicId) {
            $seen[$publicId] = true;
        }
        $created++;

        Log::debug('FetchCompetitorFollowersJob: Stored follower', [
            'public_id' => $publicId,
            'name' => $fullName,
            'connection_degree' => $connectionDegree
        ]);
    }
}


