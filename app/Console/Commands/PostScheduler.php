<?php

namespace App\Console\Commands;

use App\Models\Post;
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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /**
         * check posts on schedule status and date time utc
         * Check access token is valid
         * Publish post
         */

        $currentDate = Carbon::now();
        $formatedDate = $currentDate->toDateTimeString();
        $today = $currentDate->toDateString();

        $this->info("Scheduler running at {$formatedDate}");

        $linkedin = new LinkedInService;

        $posts = $this->postCheck();

        if(count($posts)>0){
            foreach ($posts as $post) {
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
