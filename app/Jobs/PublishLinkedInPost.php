<?php

namespace App\Jobs;

use App\Models\LinkedInPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PublishLinkedInPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;

    /**
     * Create a new job instance.
     */
    public function __construct(LinkedInPost $post)
    {
        $this->post = $post;
    }

    /**
     * 🔥 Execute the job - NOW USES LINKEDIN OFFICIAL API!
     * Posts automatically from backend - user doesn't need to be online!
     */
    public function handle(): void
    {
        // Get the user who created this post
        $postCreator = \App\Models\User::find($this->post->user_id);
        
        Log::info('🚀 ========== PublishLinkedInPost JOB STARTED ==========', [
            'post_id' => $this->post->id,
            'post_user_id' => $this->post->user_id,
            'post_creator_name' => $postCreator ? $postCreator->name : 'Unknown',
            'post_creator_email' => $postCreator ? $postCreator->email : 'Unknown',
            'post_creator_linkedin_id' => $postCreator ? ($postCreator->linkedin_id ?? 'not_set') : 'Unknown',
            'scheduled_at' => $this->post->scheduled_at,
            'post_type' => $this->post->post_type,
            'current_status' => $this->post->status,
            'content_preview' => substr($this->post->content, 0, 100),
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            Log::info('Step 1: Checking post status', [
                'post_id' => $this->post->id,
                'current_status' => $this->post->status,
                'expected_status' => 'ready_to_publish or scheduled'
            ]);

            // Check if post is still ready to publish
            if ($this->post->status !== 'ready_to_publish' && $this->post->status !== 'scheduled') {
                Log::warning('⚠️ Post is not in publishable status - ABORTING', [
                    'post_id' => $this->post->id,
                    'status' => $this->post->status,
                    'scheduled_at' => $this->post->scheduled_at
                ]);
                return;
            }

            Log::info('✅ Step 1 passed: Post status is valid');
            
            // Get details of the app user who created this post
            $appUser = \App\Models\User::find($this->post->user_id);
            Log::info('👤 Post Creator (App User) Details', [
                'user_id' => $this->post->user_id,
                'user_name' => $appUser ? $appUser->name : 'Unknown',
                'user_email' => $appUser ? $appUser->email : 'Unknown',
                'user_linkedin_id' => $appUser ? ($appUser->linkedin_id ?? 'not_set') : 'Unknown'
            ]);

            Log::info('Step 2: Fetching LinkedIn integration for THIS user', [
                'post_user_id' => $this->post->user_id,
                'searching_for' => 'oauth_provider=linkedin, connected_status=1'
            ]);

            // FIRST: Log ALL LinkedIn integrations in the system to see what's available
            $allIntegrations = \App\Models\Integration::where('oauth_provider', 'linkedin')
                ->where('connected_status', 1)
                ->get(['id', 'user_id', 'first_name', 'last_name', 'email', 'oauth_uid']);
            
            Log::info('📊 ALL LinkedIn integrations in system', [
                'total_count' => $allIntegrations->count(),
                'integrations' => $allIntegrations->map(function($i) {
                    return [
                        'integration_id' => $i->id,
                        'belongs_to_user_id' => $i->user_id,
                        'linkedin_name' => $i->first_name . ' ' . $i->last_name,
                        'linkedin_email' => $i->email,
                        'oauth_uid' => $i->oauth_uid
                    ];
                })->toArray()
            ]);

            // NOW: Get user's LinkedIn integration
            $integration = \App\Models\Integration::where('user_id', $this->post->user_id)
                ->where('oauth_provider', 'linkedin')
                ->where('connected_status', 1)
                ->first();
            
            Log::info('🎯 Selected integration for THIS post', [
                'post_user_id' => $this->post->user_id,
                'selected_integration_id' => $integration ? $integration->id : 'NONE',
                'selected_integration_user_id' => $integration ? $integration->user_id : 'NONE',
                'query_used' => "Integration::where('user_id', {$this->post->user_id})->where('oauth_provider', 'linkedin')->where('connected_status', 1)->first()"
            ]);

            if (!$integration) {
                Log::error('❌ Step 2 FAILED: No LinkedIn integration found for user', [
                    'post_id' => $this->post->id,
                    'user_id' => $this->post->user_id,
                    'available_integrations' => \App\Models\Integration::where('user_id', $this->post->user_id)->count()
                ]);
                
                $this->post->markAsFailed();
                return;
            }

            Log::info('✅ Step 2 passed: Integration found', [
                'integration_id' => $integration->id,
                'oauth_provider' => $integration->oauth_provider,
                'connected_status' => $integration->connected_status,
                'integration_user_id' => $integration->user_id,
                'integration_owner_name' => $integration->first_name . ' ' . $integration->last_name,
                'integration_email' => $integration->email,
                'oauth_uid' => $integration->oauth_uid
            ]);
            
            // VERIFY integration belongs to the post creator
            if ($integration->user_id !== $this->post->user_id) {
                Log::error('🚨 CRITICAL ERROR: Integration user mismatch!', [
                    'post_user_id' => $this->post->user_id,
                    'integration_user_id' => $integration->user_id,
                    'this_means' => 'Post created by one user but publishing to different user\'s LinkedIn!'
                ]);
                $this->post->markAsFailed();
                return;
            }

            Log::info('Step 3: Validating access token', [
                'integration_id' => $integration->id,
                'has_access_token' => !empty($integration->access_token),
                'has_refresh_token' => !empty($integration->refresh_token)
            ]);

            if (!$integration->access_token) {
                Log::error('❌ Step 3 FAILED: No access token available', [
                    'post_id' => $this->post->id,
                    'integration_id' => $integration->id
                ]);
                
                $this->post->markAsFailed();
                return;
            }

            Log::info('✅ Step 3 passed: Access token exists', [
                'integration_id' => $integration->id,
                'oauth_uid' => $integration->oauth_uid,
                'has_access_token' => !empty($integration->access_token),
                'has_refresh_token' => !empty($integration->refresh_token),
                'token_preview' => substr($integration->access_token, 0, 20) . '...'
            ]);

            Log::info('Step 4: Calling LinkedIn API service');

            // 🔥 Publish using LinkedIn Official API v2
            $linkedInService = new \App\Services\LinkedInService();
            $response = $linkedInService->publishPostV2($this->post, $integration);

            Log::info('✅ Step 4 passed: LinkedIn API service completed', [
                'response_type' => gettype($response),
                'response_keys' => is_array($response) ? array_keys($response) : 'not_array'
            ]);

            Log::info('Step 5: Extracting LinkedIn post ID from response', [
                'response' => $response
            ]);

            // Extract post ID from response
            $linkedinPostId = null;
            if (isset($response['id'])) {
                $linkedinPostId = $response['id'];
                Log::info('Found LinkedIn post ID in response["id"]', ['linkedin_post_id' => $linkedinPostId]);
            } elseif (isset($response['value'])) {
                // Some API responses nest the ID
                $linkedinPostId = $response['value'];
                Log::info('Found LinkedIn post ID in response["value"]', ['linkedin_post_id' => $linkedinPostId]);
            } else {
                Log::warning('⚠️ No LinkedIn post ID found in response', [
                    'response_structure' => $response
                ]);
            }

            Log::info('Step 6: Marking post as published in database');

            // Mark post as published
            $this->post->markAsPublished($linkedinPostId);

            Log::info('✅✅✅ POST PUBLISHED SUCCESSFULLY ✅✅✅', [
                'post_id' => $this->post->id,
                'linkedin_post_id' => $linkedinPostId,
                'full_response' => $response,
                'timestamp' => now()->toDateTimeString()
            ]);

            Log::info('========== JOB COMPLETED SUCCESSFULLY ==========');

        } catch (\Exception $e) {
            Log::error('❌❌❌ FAILED TO PUBLISH POST ❌❌❌', [
                'post_id' => $this->post->id,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'full_trace' => $e->getTraceAsString()
            ]);

            // Mark post as failed
            $this->post->markAsFailed();
            
            Log::error('========== JOB FAILED ==========');
            
            // Re-throw to trigger failed() method
            throw $e;
        }
    }


    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('❌ PublishLinkedInPost job failed', [
            'post_id' => $this->post->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Mark post as failed
        $this->post->markAsFailed();
    }
}
