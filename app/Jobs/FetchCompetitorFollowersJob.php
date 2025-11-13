<?php

namespace App\Jobs;

use App\Models\Audience;
use App\Models\AudienceList;
use App\Services\RapidApiService;
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

    public function __construct(int $userId, int $audiencePkId, string $companyUrl)
    {
        $this->userId = $userId;
        $this->audiencePkId = $audiencePkId;
        $this->companyUrl = $companyUrl;
    }

    public function handle(): void
    {
        $audience = Audience::find($this->audiencePkId);
        if (!$audience) {
            Log::warning('FetchCompetitorFollowersJob: Audience not found', ['audiencePkId' => $this->audiencePkId]);
            return;
        }

        $service = new RapidApiService();
        $uniqueByPublicId = [];
        $created = 0;

        // Fetch recent company posts (past month), iterate a few pages
        for ($page = 1; $page <= 3; $page++) {
            $postsResponse = $service->fetch_company_posts($this->companyUrl, $page);
            $posts = $postsResponse['data'] ?? [];
            if (empty($posts)) {
                break;
            }

            foreach ($posts as $post) {
                $postDate = isset($post['posted_at']) ? now()->parse($post['posted_at']) : now();

                // Collect commenters if available
                $comments = $post['comments_detail'] ?? $post['comments'] ?? [];
                foreach ($comments as $comment) {
                    $author = $comment['author'] ?? $comment['commenter'] ?? [];
                    $this->storeEngager($audience, $author, $postDate, $uniqueByPublicId, $created);
                }

                // Collect reactors/likers if available
                $likers = $post['likers'] ?? $post['reactions'] ?? [];
                foreach ($likers as $liker) {
                    $this->storeEngager($audience, $liker, $postDate, $uniqueByPublicId, $created);
                }
            }
        }

        Log::info('FetchCompetitorFollowersJob: RapidAPI engaged users stored', [
            'audience_id' => $audience->audience_id,
            'stored' => $created
        ]);
    }

    private function storeEngager(Audience $audience, array $person, $lastActivity, array &$seen, int &$created): void
    {
        if (empty($person)) {
            return;
        }

        $publicId = $person['public_identifier'] ?? $person['publicIdentifier'] ?? null;
        if ($publicId && isset($seen[$publicId])) {
            return;
        }

        $fullName = $person['name'] ?? trim(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? ''));
        $first = $person['first_name'] ?? ($fullName ? explode(' ', $fullName, 2)[0] : null);
        $last = $person['last_name'] ?? ($fullName && str_contains($fullName, ' ') ? explode(' ', $fullName, 2)[1] : null);

        $jobTitle = $person['headline'] ?? $person['title'] ?? null;
        $companyName = $person['company'] ?? $person['company_name'] ?? null;
        $location = $person['location'] ?? null;
        $profileUrl = $person['profile_url'] ?? ($publicId ? 'https://www.linkedin.com/in/' . $publicId . '/' : null);

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
                'con_last_activity' => $lastActivity,
            ]
        );

        if ($publicId) {
            $seen[$publicId] = true;
        }
        $created++;
    }
}


