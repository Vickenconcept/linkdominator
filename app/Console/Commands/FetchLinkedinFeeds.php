<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CommentFeedCampaign;
use App\Models\CommentFeedCampaignPost;
use App\Services\RapidApi;
use App\Services\ChatGPT;
use App\Services\RapidApiService;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use DB;

class FetchLinkedinFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-linkedin-feeds';

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
        $this->info("Started fetching linkedin post...");
        // check for comment campaign available
        $comment_campaign = new CommentFeedCampaign;

        $todayDate = Carbon::now()->toDateString();

        $query = "
            select a.*, b.access_token 
            from comment_feed_campaigns a 
            join users b on a.user_id = b.id
            where a.status in ('ongoing','processed') or a.total_post_found = 0;
        ";
        $campaigns = DB::select($query);

        $rapidapi_service = new RapidApiService;

        foreach ($campaigns as $item) {
            $itemUpdatedDate = date_format(date_create($item->updated_at), "Y-m-d");

            if(($item->total_post_found == 0 && $itemUpdatedDate == $todayDate) || $itemUpdatedDate < $todayDate)
            {
                if($item->campaign_type == 'keyword' && $item->keyword_list){
                    $item->keyword_list = str_replace("\r\n"," ",$item->keyword_list);

                    $linkedin_posts = $rapidapi_service->search_posts($item->keyword_list);
                    $linkedin_posts = $linkedin_posts['data'];

                }else if($item->campaign_type == 'profile' && $item->profile_list){
                    $item->profile_list = explode("\r\n", $item->profile_list);
                    $linkedin_posts = [];

                    foreach ($item->profile_list as $p_url) {
                        $profile_posts = $rapidapi_service->fetch_profile_posts($p_url);
                        $post_data = $profile_posts['data'];

                        foreach ($post_data as $li_post) {
                            if(property_exists($li_post, 'poster')){
                                array_push($linkedin_posts, [
                                    'post_type' => 'document',
                                    'post_url' => $li_post['post_url'],
                                    'posted' => $li_post['posted'],
                                    'poster_linkedin_url' => $li_post['poster_linkedin_url'],
                                    'poster_name' => str_replace("'", "\\'", $li_post['poster']['first']) .' '. str_replace("'", "\\'", $li_post['poster']['last']),
                                    'poster_title' => $li_post['poster']['headline'],
                                    'text' => $li_post['text'],
                                    'urn' => $li_post['urn']  
                                ]); 
                            }
                        }
                    }
                }

                if(count($linkedin_posts)>0){
                    $prompt = "
                        Generate a linkedin comment for a linkedin post. 
                        The comment should be constructed under the following rules,
                        comment style: $item->ai_comment_style
                        comment type: $item->ai_comment_type comment
                        product name and description: $item->product_name_description
                        product unique selling proposition: $item->product_unique_selling_point
                        persona description: $item->persona_description
                        actions to take: $item->what_ai_need_todo
                        actions to avoid: $item->what_ai_should_avoid
                        tone and style: $item->tone_style  
                    ";

                    $comment_campaign_post = new CommentFeedCampaignPost;

                    foreach ($linkedin_posts as $post) {
                        $gpt_comment = '';

                        if ($item->ai_commenter == 'common'){
                            // Get comment for each post
                            $prompt .= "here is the linkedin post: $post->text";

                            try {
                                $chatgpt = new ChatGPT;
                                $chatgpt->checkModeration($prompt);
                                $gpt_comment = $chatgpt->generateContent($prompt)['content'];
                            } catch (\Throwable $th) {
                                //throw $th;
                                $gpt_comment = '';
                            }
                        }else if($item->ai_commenter == 'custom' && $item->custom_webhook && $item->access_token){
                            // Post to webhook if set
                            $payload = [
                                "post_type" => $post->post_type,
                                "post_url" => $post->post_url,
                                "posted" => $post->posted,
                                "poster_linkedin_url" => $post->poster_linkedin_url,
                                "poster_name" => $post->poster_name,
                                "poster_title" => $post->poster_title,
                                "text" => $post->text,
                                "urn" => $post->urn
                            ];

                            $header = [
                                "X-Api-Key" => $item->access_token
                            ];

                            Http::asForm()
                                ->withHeaders($headers)
                                ->post($item->custom_webhook, $payload)
                                ->throw()
                                ->json();
                        }

                        // Create post in DB
                        // Check if same post urn exist in campaign
                        $query = sprintf("select campaign_id, urn from comment_feed_campaign_post where campaign_id=%s and urn='%s'", $item->id, $post->urn);
                        $data = DB::select($query);

                        if(count($data)>0){
                            $comment_campaign_post->create([
                                'campaign_id' => $item->id, 
                                'num_comments' => null, 
                                'num_likes' => null, 
                                'num_shares' => null, 
                                'post_type' => $post->post_type, 
                                'post_url' => $post->post_url, 
                                'posted' => $post->posted, 
                                'poster_linkedin_url' => $post->poster_linkedin_url, 
                                'poster_name' => $post->poster_name, 
                                'poster_title' => str_replace("'", "\\'", $post->poster_title), 
                                'post' => str_replace("'", "\\'", $post->text), 
                                'urn' => $post->urn, 
                                'comment' => str__replace("'", "\\'", $gpt_comment)
                            ]);
                        }

                        $comment_campaign->where('id', $item->id)
                            ->update([
                                'total_post_found' => count($linkedin_posts),
                                'status' => 'processed'
                            ]);

                        $this->info('Post created successfully.');
                    }
                }else{
                    $this->info('No post found.');
                }
            }else{
                $this->info('No comment campaign available.');
            }
        }
        $this->info('Completed fetching linkedin post.');
    }
}
