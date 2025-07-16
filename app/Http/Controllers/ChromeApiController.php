<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Audience;
use App\Models\AudienceList;
use App\Models\EspIntegration;
use App\Models\AutoMessageResponse;
use App\Models\Ministat;
use App\Models\SnLead;
use App\Models\SnLeadList;
use App\Models\UserActivity;
use App\Helpers\CampaignHelper;
use App\Services\EmailFinder;
use App\Services\LeadShareService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
// use DB;
use Log;

class ChromeApiController extends Controller
{
    use CampaignHelper;

    public function accessCheck(Request $request)
    {
        try {
            $user = $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 401
            ]);
        }

        if (!$user->hasPermissionTo('FE')) {
            return response()->json([
                "message" => 'Unauthorized',
                "status" => 401
            ]);
        }

        return response()->json([
            "message" => 'Access granted',
            "status" => 200,
            "accessId" => 'FE'
        ]);
    }

    public function getAudience(Request $request)
    {
        $linkedin_Id = $request->query('linkedinId');

        $query = sprintf("
        SELECT 
            a.id, 
            a.audience_name, 
            a.audience_id, 
            a.audience_type, 
            COUNT(al.audience_id) AS total 
        FROM audiences a
        JOIN users u ON u.id = a.user_id 
        LEFT JOIN audience_lists al ON al.audience_id = a.audience_id
        WHERE u.linkedin_id = '%s' 
        GROUP BY a.id, a.audience_name, a.audience_id, a.audience_type
        ORDER BY DATE(a.created_at) DESC
    ", addslashes($linkedin_Id)); // optionally sanitize input

        $audiences = DB::select($query);

        return response()->json([
            'audience' => $audiences
        ]);
    }

    public function storeAudience(Request $request)
    {
        $audience_name = $request->audienceName;
        $audience_id = $request->audienceId;
        $audience_type = $request->audienceType;
        $linkedin_Id = $request->linkedInId;

        $user = User::where('linkedin_id', $linkedin_Id)->first();

        $audience_exists = Audience::where('audience_name', $audience_name)
            ->where('user_id', $user->id)
            ->first();

        if ($user) {
            if (!$audience_exists) {
                $audience_exists = Audience::create([
                    'audience_name' => $audience_name,
                    'audience_id' => $audience_id,
                    'audience_type' => $audience_type,
                    'user_id' => $user->id
                ]);
            }

            return response()->json([
                ['audienceName' => $audience_exists]
            ]);
        }

        return response()->json([
            'message' => 'Please signup!'
        ]);
    }

    public function deleteAudience(Request $request)
    {
        $audience_id = $request->query('audienceId');

        AudienceList::where('audience_id', $audience_id)->delete();
        Audience::where('audience_id', $audience_id)->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }

    public function getAudienceList(Request $request)
    {
        $audience_id = $request->query('audienceId');
        $total_count = $request->query('totalCount');

        if (isset($total_count)) {
            $user_list = sprintf("
                SELECT id, con_first_name, con_last_name, con_job_title, con_location, con_distance, 
                con_public_identifier, con_id, con_member_urn, con_tracking_id, created_at
                from audience_lists where audience_id = %s order by date(created_at) desc limit %s;
            ", $audience_id, (int)$total_count);
        } else {
            $user_list = sprintf("
            SELECT id, con_first_name, con_last_name, con_job_title, con_location, con_distance, 
            con_public_identifier, con_id, con_member_urn, con_tracking_id, created_at
            from audience_lists where audience_id = %s order by date(created_at) desc;
            ", $audience_id);
        }

        $audience_list = DB::select($user_list);

        return response()->json([
            'audience' => $audience_list
        ]);
    }

    public function storeAudienceList(Request $request)
    {
        $audience_id = $request->audienceId;
        $first_name = $request->firstName;
        $last_name = $request->lastName;
        $email = $request->email;
        $title = null;
        $locationName = null;

        if ($request->has('title')) {
            $title = $request->title;
        }
        if ($request->has('locationName')) {
            $locationName = $request->locationName;
        }

        $public_identifier = $request->publicIdentifier;
        $connection_id = $request->connectionId;
        $tracking_id = $request->trackingId;
        $member_urn = $request->memberUrn;

        $check_exists = AudienceList::where('con_id', $connection_id)
            ->where('audience_id', $audience_id)
            ->get();

        if ($check_exists->count() == 0) {
            AudienceList::create([
                'audience_id' => $audience_id,
                'con_first_name' => $first_name,
                'con_last_name' => $last_name,
                'con_email' => $email,
                'con_job_title' => $title,
                'con_location' => $locationName,
                'con_public_identifier' => $public_identifier,
                'con_id' => $connection_id,
                'con_tracking_id' => $tracking_id,
                'con_member_urn' => $member_urn
            ]);

            return response()->json([
                'message' => 'success'
            ], 201);
        }
        return response()->json([
            'message' => 'User already added to audience list'
        ]);
    }

    public function updateAudienceList(Request $request)
    {
        $audience_id = $request->audienceId;
        $connection_id = $request->connectionId;
        $ndistance = $request->networkDistance;
        $premium = $request->premium;
        $influencer = $request->influencer;
        $jobSeeker = $request->jobSeeker;
        $companyUrl = $request->companyUrl;

        $data = [
            'con_distance' => $ndistance,
            'con_premium' => $premium,
            'con_influencer' => $influencer,
            'con_jobseeker' => $jobSeeker,
            'con_company_url' => $companyUrl
        ];

        $lead = AudienceList::where('audience_id', $audience_id)
            ->where('con_id', $connection_id);

        $lead->update($data);

        // get audience data from db
        if ($lead->first()->con_first_name && $lead->first()->con_last_name && $lead->first()->con_company_url && !$lead->first()->con_email) {
            $domain = $this->trimDomain($lead->first()->con_company_url);

            // initiate email finder
            try {
                $getEmail = new EmailFinder([
                    'firstName' => $lead->first()->con_first_name,
                    'lastName' => $lead->first()->con_last_name,
                    'website' => $domain
                ]);

                $getEmail = $getEmail->findEmail();

                AudienceList::where('con_id', $connection_id)->update(['con_email' => $getEmail['email']]);
            } catch (\Throwable $th) {
                // throw $th;
                Log::info($th);
            }
        }

        return response()->json([
            'message' => 'success'
        ]);
    }

    public function deleteAudienceList(Request $request)
    {
        $row_id = $request->query('rowId');

        AudienceList::where('id', $row_id)->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }

    public function audienceListExport(Request $request)
    {
        $audience_id = $request->query('audienceId');

        $audience_list = sprintf("
            SELECT con_first_name as firstName, con_last_name as lastName, con_job_title as occupation, 
            concat('https://www.linkedin.com/in/',con_public_identifier) as Link, con_location as location, 
            con_id as id, case when con_premium = 0 then 'false' else 'true' end as premium, 
            case when con_influencer = 0 then 'false' else 'true' end as influencer, 
            case when con_jobseeker = 0 then 'false' else 'true' end as jobSeeker,
            created_at as createdAt, con_distance as memberDistance, con_company_url as companyURL 
            from audience_lists where audience_id = %s
        ", $audience_id);

        $audience_list = DB::select($audience_list);

        return response()->json([
            'audience' => $audience_list
        ]);
    }

    public function export(Request $request, $audience_id)
    {
        $exportType = $request->type;
        $espType = $request->espType;

        $query = sprintf("
            SELECT con_first_name as firstName, con_last_name as lastName, con_email as email, con_job_title as occupation, 
            concat('https://www.linkedin.com/in/',con_public_identifier) as Link, con_location as location,
            created_at as createdAt, con_distance as memberDistance, con_company_url as companyURL 
            from audience_lists 
            where audience_id = %s
        ", $audienceid);

        $audiences = DB::select($query);

        if ($exportType == 'csv') {
            return response()->json([
                'data' => $audiences
            ]);
        } else {
            $esp = EspIntegration::where('user_id', auth()->user()->id)->first();

            if ($esp) {
                $esp = [
                    'id' => $esp->id,
                    'mailchimp' => json_decode($esp->mailchimp),
                    'getresponse' => json_decode($esp->getresponse),
                    'emailoctopus' => json_decode($esp->emailoctopus),
                    'converterkit' => json_decode($esp->converterkit),
                    'mailerlite' => json_decode($esp->mailerlite),
                    'webhook' => $esp->webhook
                ];

                $leadShare = new LeadShareService;
                $listTypes = ['listid', 'campaignId', 'formId', 'groupId'];

                if ($espType == 'mailchimp' || $espType == 'emailoctopus')
                    $listkey = $listTypes[0];
                elseif ($espType == 'getresponse')
                    $listkey = $listTypes[1];
                elseif ($espType == 'converterkit')
                    $listkey = $listTypes[2];
                elseif ($espType == 'mailerlite')
                    $listkey = $listTypes[3];

                foreach ($audiences as $lead) {
                    if ($lead->email) {
                        $leadShare->leadShare($espType, [
                            'email' => $lead['email'],
                            'name' => $lead['first_name'],
                            'apikey' => $esp[$espType]['apikey'],
                            'listid' => $esp[$espType][$listkey]
                        ]);
                    }
                }
            }

            return response()->json([
                'message' => 'leads added to list'
            ]);
        }
    }

    public function audienceRecent(Request $request)
    {
        $audienceId = $request->query('audienceId');

        $todays_audience = AudienceList::where('audience_id', $audienceId)
            ->whereDate('created_at', Carbon::today())
            ->get();

        return response()->json([
            'total' => $audience_count,
            'message' => 'success'
        ]);
    }

    public function getAutoResponses(Request $request)
    {
        try {
            $user = $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 401
            ]);
        }

        $normal = [];
        $endorsement = [];
        $followup = [];

        $auto_responses = AutoMessageResponse::where('user_id', $user->id)->get();

        foreach ($auto_responses as $item) {
            if ($item->message_type == 'normal') {
                array_push($normal, $item);
            } elseif ($item->message_type == 'endorsement') {
                array_push($endorsement, $item);
            } elseif ($item->message_type == 'followup') {
                array_push($followup, $item);
            }
        }

        return response()->json([
            'normal' => $normal,
            'endorsement' => (object)$endorsement,
            'followup' => (object)$followup
        ]);
    }

    public function storeAutoResponse(Request $request)
    {
        try {
            $user = $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 401
            ]);
        }

        AutoMessageResponse::create([
            'message_type' => $request->message_type,
            'message_keywords' => $request->message_keywords,
            'total_endorse_skills' => $request->total_endorse_skills,
            'message_body' => $request->message_body,
            'attachement' => $request->attachement,
            'user_id' => $user->id
        ]);

        return response()->json([
            'message' => 'message created!'
        ], 201);
    }

    public function showAutoResponse(Request $request, string $id)
    {
        try {
            $user = $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 401
            ]);
        }

        $autoresponse = AutoMessageResponse::findOrFail($id);

        return response()->json([
            'data' => $autoresponse
        ]);
    }

    public function updateAutoResponse(Request $request, string $id)
    {
        try {
            $user = $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 401
            ]);
        }

        $autoresponse = AutoMessageResponse::findOrFail($id);

        $autoresponse->update([
            'message_type' => $request->message_type,
            'message_keywords' => $request->message_keywords,
            'total_endorse_skills' => $request->total_endorse_skills,
            'message_body' => $request->message_body,
            'attachement' => $request->attachement,
        ]);

        return response()->json([
            'message' => 'message updated!'
        ], 201);
    }

    public function deleteAutoResponse(Request $request, string $id)
    {
        try {
            $user = $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 401
            ]);
        }

        $autoresponse = AutoMessageResponse::findOrFail($id);

        $autoresponse->delete();

        return response()->json([
            'message' => 'message deleted!'
        ]);
    }

    public function langFilter(Request $request)
    {
        $lang = $request->query('lang');
        $languages = [];

        if (isset($lang)) {
            $languages = DB::select("SELECT * from lkd_languages where name like '%$lang%'");
        }

        return response()->json([
            'languages' => $languages
        ]);
    }

    public function LinkedInConfig(Request $request)
    {
        $connection = $request->query('connection');
        $sentInvite = $request->query('sentInvite');
        $profileView = $request->query('profileView');
        $searchAppear = $request->query('searchAppear');
        $linkedin_id = $request->query('profileId');

        if (isset($linkedin_id)) {
            $user = User::where('linkedin_id', $linkedin_id)->first();

            if ($user) {
                $ministat = new Ministat;

                $stats = $ministat->where('user_id', $user->id);

                if ($stats->first()) {
                    $stats->update([
                        'connections' => $connection ?? 0,
                        'pending_invites' => $sentInvite ?? 0,
                        'profile_views' => $profileView ?? 0,
                        'search_appearance' => $searchAppear ?? 0
                    ]);
                } else {
                    $ministat->create([
                        'connections' => $connection ?? 0,
                        'pending_invites' => $sentInvite ?? 0,
                        'profile_views' => $profileView ?? 0,
                        'search_appearance' => $searchAppear ?? 0,
                        'user_id' => $user->id
                    ]);
                }

                return response()->json([
                    'message' => 'report save.',
                    'status_code' => 200
                ], 201);
            } else {
                return response()->json([
                    'message' => 'link id not available.',
                    'status_code' => 401
                ], 401);
            }
        }
    }

    public function storeSnLeads(Request $request)
    {
        try {
            $user = $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 401
            ]);
        }

        $listId = $request->listId;
        $leads = $request->leads;

        SnLead::create([
            'first_name' => $leads['firstName'],
            'last_name' => $leads['lastName'],
            'headline' => $leads['headline'],
            'email' => $leads['email'],
            'lid' => $leads['profileId'],
            'picture' => $leads['picture'],
            'geolocation' => $leads['geoLocation'],
            'degree' => $leads['degree'],
            'object_urn' => $leads['objectUrn'],
            'sn_list_id' => $listId
        ]);

        if ($leads['firstName'] && $leads['lastName'] && $leads['website'] && $leads['profileId'] && !$leads['email']) {
            $leads['website'] = $this->trimDomain($leads['website']);

            // initiate email finder
            try {
                $getEmail = new EmailFinder([
                    'firstName' => $leads['firstName'],
                    'lastName' => $leads['lastName'],
                    'website' => $leads['website']
                ]);

                $getEmail = $getEmail->findEmail();

                SnLead::where('lid', $leads['profileId'])->update(['email' => $getEmail['email']]);
            } catch (\Throwable $th) {
                // throw $th;
                Log::info($th);
            }
        }

        return response()->json([
            'message' => 'Lead added'
        ], 201);
    }

    public function getSnLeadList(Request $request)
    {
        try {
            $user = $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 401
            ]);
        }

        $snlist = SnLeadList::select('name', 'list_hash')->where('user_id', $user->id)->get();

        return response()->json([
            'data' => $snlist
        ]);
    }

    public function storeSnLeadList(Request $request)
    {
        try {
            $user = $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 401
            ]);
        }

        $listName = $request->listName;

        $snlist = SnLeadList::create([
            'name' => $listName,
            'list_hash' => rand(1111111, 9999999),
            'user_id' => $user->id
        ]);

        return response()->json([
            "message" => 'List created',
            "listId" => $snlist->list_hash
        ], 201);
    }

    public function storeUserActivity(Request $request)
    {
        $module_name = $request->query('module');
        $stats = $request->query('stat');
        $linkedin_id = $request->query('identifier');

        $user = User::where('linkedin_id', $linkedin_Id)->first();

        UserActivity::create([
            'module_name' => $module_name,
            'stats' => $stats,
            'user_id' => $user->id
        ]);

        return response()->json([
            'status' => 'success',
            'createdAt' => Carbon::now()->toDateString()
        ], 201);
    }

    public function getWebsiteWizard() {}

    public function trimDomain($url)
    {
        if (str_contains($url, 'https://'))
            $url = str_replace('https://', '', $url);
        if (str_contains($url, 'http://'))
            $url = str_replace('http://', '', $url);
        if (str_contains($url, 'www.'))
            $url = str_replace('www.', '', $url);
        if (str_contains($url, '/'))
            $url = str_replace('/', '', $url);
        return $url;
    }
}
