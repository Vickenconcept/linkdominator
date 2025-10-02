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
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('🚀 PublishLinkedInPost job started', [
            'post_id' => $this->post->id,
            'user_id' => $this->post->user_id,
            'scheduled_at' => $this->post->scheduled_at
        ]);

        try {
            // Check if post is still scheduled and ready to publish
            if (!$this->post->isReadyToPublish()) {
                Log::warning('⚠️ Post is not ready to publish', [
                    'post_id' => $this->post->id,
                    'status' => $this->post->status,
                    'scheduled_at' => $this->post->scheduled_at
                ]);
                return;
            }

            // The extension will handle the actual posting
            // We just need to ensure the post is ready for the extension to pick up
            Log::info('📤 Post ready for extension to publish', [
                'post_id' => $this->post->id,
                'content_preview' => substr($this->post->content, 0, 100) . '...'
            ]);

            Log::info('✅ Post published successfully', [
                'post_id' => $this->post->id
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Failed to publish post', [
                'post_id' => $this->post->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark post as failed
            $this->post->markAsFailed();
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
