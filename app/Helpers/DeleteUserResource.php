<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Audience;
use App\Models\AudienceList;
use App\Models\AutoMessageResponse;
use App\Models\SnLeadList;
use App\Models\SnLead;
use App\Models\SnLeadsCompany;
use App\Models\Campaign;
use App\Models\CampaignLeadgenRunning;
use App\Models\CampaignList;
use App\Models\CampaignSequenceEndorse;
use App\Models\UserActivity;
use App\Models\CallReminder;
use App\Models\CallReminderMessage;
use App\Models\CallStatus;
use App\Models\AiContent;
use App\Models\Post;
use App\Models\Integration;
use App\Models\ModelHasPermission;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Query\JoinClause;
use DB;

class DeleteUserResource
{
    public static function handle($id): void
    {
        $user = User::findOrFail($id);

        $audience = Audience::where('user_id', $id);
        if($audience->first()){
            AudienceList::where('audience_id', $audience->first()->audience_id)->delete();
            $audience->delete();
        }

        // Sn Leads, campaign, call manager, ai writer, posts
        $sn = SnLeadList::where('user_id', $id);
        if($sn->first()){
            SnLead::where('sn_list_id', $sn->first()->list_hash)->delete();
            SnLeadsCompany::where('sn_lead_id', $sn->first()->list_hash)->delete();
            $sn->delete();
        }

        $campaign = Campaign::where('user_id', $id);
        if($campaign->first()){
            CampaignLeadgenRunning::where('campaign_id', $campaign->first()->id)->delete();
            CampaignList::where('campaign_id', $campaign->first()->id)->delete();
            CampaignSequenceEndorse::where('campaign_id', $campaign->first()->id)->delete();
            $campaign->delete();
        }

        $callReminder = CallReminder::where('user_id', $id);
        if($callReminder->first()){
            CallReminderMessage::where('call_reminder_id', $callReminder->first()->id)->delete();
            $callReminder->delete();
        }
        CallStatus::where('user_id', $id)->delete();

        AiContent::where('user_id', $id)->delete();

        Post::where('user_id', $id)->delete();

        Integration::where('user_id', $id)->delete();

        UserActivity::where('user_id', $id)->delete();

        ModelHasPermission::where('model_id', $id)->delete();

        AutoMessageResponse::where('user_id', $id)->delete();

        $user->removeRole('User');

        $user->delete();
    }
}