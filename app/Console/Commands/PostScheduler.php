<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\LinkedInPost;
use App\Jobs\PublishLinkedInPost;
use App\Services\LinkedInService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use Log;

class PostScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:post-scheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled LinkedIn posts that are ready to publish';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDate = Carbon::now();
        $formatedDate = $currentDate->toDateTimeString();

        Log::info("📅 Scheduler running at {$formatedDate}");

        // Check NEW linkedin_posts table for scheduled posts
        $readyPosts = LinkedInPost::where('status', 'scheduled')
            ->where('scheduled_at', '<=', $currentDate)
            ->whereNotNull('scheduled_at')
            ->get();

        if ($readyPosts->count() > 0) {
            Log::info("📅 Found {$readyPosts->count()} ready posts to publish");
            
            foreach ($readyPosts as $post) {
                try {
                    Log::info('📅 Scheduler dispatching post', [
                        'post_id' => $post->id,
                        'scheduled_at' => $post->scheduled_at,
                        'user_id' => $post->user_id
                    ]);
                    
                    // Update status before dispatching
                    $post->update(['status' => 'ready_to_publish']);
                    
                    // Dispatch job synchronously
                    PublishLinkedInPost::dispatchSync($post);
                    
                    Log::info("✅ Scheduler published post ID: {$post->id}");
                    
                } catch (\Exception $e) {
                    Log::error('❌ Scheduler failed to publish post', [
                        'post_id' => $post->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        // Also check OLD posts table for backward compatibility
        $linkedin = new LinkedInService;
        $oldPosts = $this->postCheck();

        if(count($oldPosts) > 0){
            Log::info("📅 Found " . count($oldPosts) . " OLD format posts to publish");
            foreach ($oldPosts as $post) {
                if($this->isAccessTokenValid($post, $linkedin)){
                    $linkedin->publishPost($post, $post['access_token']);
                }
            }
        }
    }

    public function postCheck()
    {
        $query = "posts.*, u.time_zone_id, t.gmt_offset, t.time_zone as country, i.oauth_uid, i.access_token, i.refresh_token";

        $posts = Post::select(DB::raw($query))
            ->join('users as u','posts.user_id','=','u.id')
            ->join('integrations as i','u.id','=','i.user_id')
            ->join('timezones as t','u.time_zone_id','=','t.id')
            ->where('posts.save_mode','schedule')
            ->where('posts.publish_status','scheduled')
            ->whereNotNull('posts.schedule_time')
            ->get();

        $set_post_to_publish = [];

        if($posts->count() > 0){
            foreach ($posts as $post) {
                // User current timezone
                $c_ist_convert = $this->getUTC($post->country);

                // User scheduled time
                $st_convert = new \DateTime($post->schedule_time);

                if ($c_ist_convert && $st_convert && $c_ist_convert >= $st_convert){
                    array_push($set_post_to_publish, $post);
                }
            }
        }

        return $set_post_to_publish;
    }

    public function isAccessTokenValid($data, $linkedin)
    {
        $post = new Post;

        try {
            $linkedin->getUserProfile($data['access_token']);
        } catch (\Throwable $th) {
            Log::debug($th);

            $post->where('id', $data['id'])
                ->update([
                    'comment' => 'Access token has expired',
                    'publish_status' => 'failed',
                ]);

            return false;
        }
        return true;
    }

    public function getUTC($timezone)
    {
        date_default_timezone_set($timezone);

        return new \DateTime(date('Y-m-d H:i:s'));
    }
}
