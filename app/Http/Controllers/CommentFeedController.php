<?php

namespace App\Http\Controllers;

use App\Models\CommentFeedCampaign;
use App\Models\CommentFeedCampaignPost;
use App\Models\User;
use App\Services\ChatGPT;
use Illuminate\Http\Request;

class CommentFeedController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tab = $request->query('tab');

        $campaigns = [];
        $campaignPosts = [];

        if(isset($tab)){
            if($tab == 'campaigns'){
                $campaigns = CommentFeedCampaign::where('user_id', $user->id)
                    ->latest()
                    ->paginate(15);
            }else {
                $campaignPosts = CommentFeedCampaignPost::selectRaw('comment_feed_campaign_post.*, comment_feed_campaigns.campaign_name')
                    ->join(
                    'comment_feed_campaigns','comment_feed_campaign_post.campaign_id','=','comment_feed_campaigns.id'
                    )
                    ->where('comment_feed_campaigns.user_id', $user->id)
                    ->orderBy('comment_feed_campaign_post.id','desc')
                    ->paginate(10);
            }
        }

        return view('comment.index', compact('campaigns','campaignPosts'));
    }

    public function createCampaign(Request $request)
    {
        $cid = 0;
        $step = 'express-setup';
        $campaign = null;
        $accessToken = auth()->user()->access_token;

        if($request->has('cid')){
            $cid = $request->query('cid');
            $campaign = CommentFeedCampaign::find($cid);
        }
        if($request->has('step')){
            $step = $request->query('step');
        }

        return view('comment.create-campaign', compact('cid','step','campaign','accessToken'));
    }

    public function storeCampaign(Request $request)
    {
        $campaign_id = (int)$request->campaign_id;
        $step = $request->step;
        $user = auth()->user();

        $campaign = new CommentFeedCampaign;

        if ($campaign_id == 0){
            $campaign = $campaign->create([
                'linkedin_profile_url' => $request->linkedin_profile_url,
                'user_id' => $user->id
            ]);
            $step = 'type';
            $campaign_id = $campaign->id;
        }else if($campaign_id > 0 && $step == 'express-setup'){
            $campaign->where('id', $campaign_id)
                ->update([
                    'linkedin_profile_url' => $request->linkedin_profile_url
                ]);
            $step = 'type';
        }else if($step == 'type'){
            $campaign->where('id', $campaign_id)
                ->update([
                    'campaign_type' => $request->campaign_type
                ]);
            $step = 'search';
        }else if($step == 'search'){
            if($request->campaign_type == 'keyword'){
                $campaign->where('id', $campaign_id)
                ->update([
                    'keyword_list' => $request->keyword_list
                ]);
            }else if($request->campaign_type == 'profile'){
                $campaign->where('id', $campaign_id)
                ->update([
                    'profile_list' => $request->profile_list
                ]);
            }
            $step = 'ai_commenter';
        }else if($step == 'ai_commenter'){
            $campaign->where('id', $campaign_id)
                ->update([
                    'ai_commenter' => $request->ai_commenter,
                    'ai_comment_style' => $request->ai_comment_style,
                    'ai_comment_type' => $request->ai_comment_type,
                    'product_name_description' => str_replace("'", "\\'", $request->product_name_description),
                    'product_unique_selling_point' => str_replace("'", "\\'", $request->product_unique_selling_point),
                    'persona_description' => str_replace("'", "\\'", $request->persona_description),
                    'what_ai_need_todo' => str_replace("'", "\\'", $request->what_ai_need_todo),
                    'what_ai_should_avoid' => str_replace("'", "\\'", $request->what_ai_should_avoid),
                    'tone_style' => str_replace("'", "\\'", $request->tone_style),
                    'custom_webhook' => $request->custom_webhook
                ]);
            $step = 'final';
        }else if($step == 'final'){
            $campaign->where('id', $campaign_id)
                ->update([
                    'campaign_name' => $request->campaign_name,
                    'max_comment_per_day_campaign' => $request->max_comment_per_day_campaign,
                    'max_comment_per_profile_day' => $request->max_comment_per_profile_day,
                    'max_comment_per_profile_week' => $request->max_comment_per_profile_week,
                    'max_comment_per_profile_month' => $request->max_comment_per_profile_month,
                    'skip_post_older_than' => $request->skip_post_older_than,
                    'status' => 'ongoing'
                ]);

            notify()->success('Comment campaign submitted successful');
            return redirect()->route('comment.index',['tab' => 'campaigns']);
        }
        
        return redirect()->route('comment.create-campaign',['step' => $step, 'cid' => $campaign_id]);
    }

    public function updateCampaignStatus(Request $request, string $id)
    {
        $campaign = CommentFeedCampaign::findOrFail($id);
        $campaign->update([
            'status' => $request->status
        ]);

        notify()->success('Comment campaign updated successful');
        return redirect()->route('comment.index',['tab' => 'campaigns']);
    }

    public function destroyCampaign(string $id)
    {
        $campaign = CommentFeedCampaign::findOrFail($id);

        $campaign->delete();

        notify()->success('Comment campaign deleted successful');
        return redirect()->route('comment.index',['tab' => 'campaigns']);
    }

    public function generateWebhook(Request $request)
    {
        $access_token = $request->header('X-Api-Key');
        $urn = $request->urn;
        $answer = $request->answer;

        $user = User::where('access_token', $access_token)->first();

        if(!$user){
            return response()->json([
                'message' => 'Unauthorized'
            ],422);
        }

        CommentFeedCampaignPost::where('urn', $urn)->update([
            'comment' => $answer
        ]);

        return response()->json([
            'message' => 'Posted successful'
        ],201);
    }

    public function skipComment(Request $request)
    {
        CommentFeedCampaignPost::where('id', $request->postId)
            ->update([
                'comment_status' => $request->publishType
            ]);

        return response()->json([
            'message' => 'Post skipped successfully'
        ]);
    }

    public function generateComment(Request $request)
    {
        $data = (object)[
            'campaign_id' => $request->campaignId,
            'post_id' => $request->postId,
            'post' => $request->post,
            'comment_type' => $request->commentType
        ];

        $campaign = CommentFeedCampaign::findOrFail($data->campaign_id);

        $prompt = "
            Generate a linkedin comment for a linkedin post. 
            The comment should be constructed under the following rules:
            comment style: $campaign->ai_comment_style
            comment type: $data->comment_type
            actions to take: $campaign->what_ai_need_todo
            actions to avoid: $campaign->what_ai_should_avoid
            tone and style: $campaign->tone_style
        ";

        if(str_contains($data->comment_type, 'promo')){
            $prompt .= "
                product name and description: $campaign->product_name_description
                product unique selling proposition: $campaign->product_unique_selling_point
                persona description: $campaign->persona_description
            ";
        }

        $prompt .= "here is the linkedin post: $data->post";

        try {
            $chatgpt = new ChatGPT;
            $chatgpt->checkModeration($prompt);
            $gpt_comment = $chatgpt->generateContent($prompt);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ],422);
        }

        CommentFeedCampaignPost::where('id', $data->post_id)->update([
            'comment' => str_replace("'", "\\'", $gpt_comment['content'])
        ]);

        return $gpt_comment;
    }
}
