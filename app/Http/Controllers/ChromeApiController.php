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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ChromeApiController extends Controller
{
    use CampaignHelper;

    /**
     * Constructor
     */
    public function __construct()
    {
        // CORS headers will be handled by middleware or individual methods
    }

    /**
     * Enhanced access check with better error handling
     */
    public function accessCheck(Request $request)
    {
        try {
            $user = $this->checkAuthorization($request);
            
            if (!$user->hasPermissionTo('FE')) {
                return $this->errorResponse('Unauthorized access', 403);
            }

            return $this->successResponse([
                'accessId' => 'FE',
                'user' => [
                    'id' => $user->id,
                    'linkedin_id' => $user->linkedin_id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 'Access granted');
            
        } catch (Exception $e) {
            Log::error('Access check failed: ' . $e->getMessage(), [
                'linkedin_id' => $request->header('lk-id'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    /**
     * Enhanced audience retrieval with error handling
     */
    public function getAudience(Request $request)
    {
        try {
            // Validate the linkedinId from query parameters
            $validator = Validator::make($request->query(), [
                'linkedinId' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                Log::warning('Audience API validation failed', [
                    'errors' => $validator->errors(),
                    'query_params' => $request->query(),
                    'all_params' => $request->all()
                ]);
                return $this->errorResponse('Invalid LinkedIn ID provided', 400, $validator->errors());
            }

            $linkedin_Id = $request->query('linkedinId');
            
            Log::info('Fetching audiences for LinkedIn ID', [
                'linkedin_id' => $linkedin_Id,
                'ip' => $request->ip()
            ]);

            // Use parameterized query to prevent SQL injection
            $audiences = DB::select("
                SELECT 
                    a.id, 
                    a.audience_name, 
                    a.audience_id, 
                    a.audience_type, 
                    COUNT(al.audience_id) AS total 
                FROM audiences a
                JOIN users u ON u.id = a.user_id 
                LEFT JOIN audience_lists al ON al.audience_id = a.audience_id
                WHERE u.linkedin_id = ? 
                GROUP BY a.id, a.audience_name, a.audience_id, a.audience_type
                ORDER BY DATE(a.created_at) DESC
            ", [$linkedin_Id]);

            Log::info('Audiences retrieved successfully', [
                'linkedin_id' => $linkedin_Id,
                'count' => count($audiences)
            ]);

            return $this->successResponse(['audience' => $audiences], 'Audiences retrieved successfully', 200);
            
        } catch (Exception $e) {
            Log::error('Failed to get audience: ' . $e->getMessage(), [
                'linkedin_id' => $request->query('linkedinId'),
                'query_params' => $request->query(),
                'all_params' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse('Failed to retrieve audience data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Enhanced audience storage with validation
     */
    public function storeAudience(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'audienceName' => 'required|string|max:255',
                'audienceId' => 'required|string',
                'audienceType' => 'required|string',
                'linkedInId' => 'required|string'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Invalid audience data provided', 400, $validator->errors());
            }

            $user = User::where('linkedin_id', $request->linkedInId)->first();
            
            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            // Check for existing audience
            $existingAudience = Audience::where('audience_name', $request->audienceName)
                ->where('user_id', $user->id)
                ->first();

            if ($existingAudience) {
                return $this->successResponse(['audience' => $existingAudience], 'Audience already exists');
            }

            // Create new audience
            $audience = Audience::create([
                'audience_name' => $request->audienceName,
                'audience_id' => $request->audienceId,
                'audience_type' => $request->audienceType,
                'user_id' => $user->id
            ]);

            Log::info('Audience created successfully', [
                'audience_id' => $audience->id,
                'user_id' => $user->id,
                'audience_name' => $request->audienceName
            ]);

            return $this->successResponse(['audience' => $audience], 'Audience created successfully');
            
        } catch (Exception $e) {
            Log::error('Failed to store audience: ' . $e->getMessage(), [
                'request' => $request->all()
            ]);
            
            return $this->errorResponse('Failed to create audience', 500);
        }
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
        try {
            // Log::info('Storing audience list item', [
            //     'request_data' => $request->all(),
            //     'linkedin_id' => $request->header('lk-id')
            // ]);

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

            Log::info('Checking for existing audience list item', [
                'audience_id' => $audience_id,
                'connection_id' => $connection_id
            ]);

            $check_exists = AudienceList::where('con_id', $connection_id)
                ->where('audience_id', $audience_id)
                ->get();

            if ($check_exists->count() == 0) {
                Log::info('Creating new audience list item', [
                    'audience_id' => $audience_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'connection_id' => $connection_id
                ]);

                $audienceListItem = AudienceList::create([
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

                Log::info('Audience list item created successfully', [
                    'id' => $audienceListItem->id,
                    'audience_id' => $audience_id
                ]);

                return response()->json([
                    'message' => 'success'
                ], 201);
            } else {
                Log::info('Audience list item already exists', [
                    'audience_id' => $audience_id,
                    'connection_id' => $connection_id
                ]);

                return response()->json([
                    'message' => 'User already added to audience list'
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to store audience list item: ' . $e->getMessage(), [
                'request' => $request->all(),
                'linkedin_id' => $request->header('lk-id')
            ]);
            
            return response()->json([
                'message' => 'Failed to store audience list item',
                'error' => $e->getMessage()
            ], 500);
        }
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
        try {
            $user = $this->checkAuthorization($request);
            $exportType = $request->type;
            $espType = $request->espType;

            $query = sprintf("
                SELECT con_first_name as firstName, con_last_name as lastName, con_email as email, con_job_title as occupation, 
                concat('https://www.linkedin.com/in/',con_public_identifier) as Link, con_location as location,
                created_at as createdAt, con_distance as memberDistance, con_company_url as companyURL 
                from audience_lists 
                where audience_id = %s
            ", $audience_id);

            $audiences = DB::select($query);

            if ($exportType == 'csv') {
                return $this->successResponse(['data' => $audiences]);
            } else {
                $esp = EspIntegration::where('user_id', $user->id)->first();

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
                                'email' => $lead->email,
                                'name' => $lead->firstName,
                                'apikey' => $esp[$espType]['apikey'],
                                'listid' => $esp[$espType][$listkey]
                            ]);
                        }
                    }
                }

                return $this->successResponse([], 'Leads added to list successfully');
            }
        } catch (Exception $e) {
            Log::error('Export failed: ' . $e->getMessage(), [
                'audience_id' => $audience_id,
                'request' => $request->all()
            ]);
            
            return $this->errorResponse('Failed to export audience data', 500);
        }
    }

    public function audienceRecent(Request $request)
    {
        try {
            $user = $this->checkAuthorization($request);
            $audienceId = $request->query('audienceId');

            $todays_audience = AudienceList::where('audience_id', $audienceId)
                ->whereDate('created_at', Carbon::today())
                ->get();

            $audience_count = $todays_audience->count();

            return $this->successResponse([
                'total' => $audience_count,
                'audience' => $todays_audience
            ], 'Recent audience data retrieved successfully');
        } catch (Exception $e) {
            Log::error('Failed to get recent audience: ' . $e->getMessage(), [
                'audience_id' => $request->query('audienceId'),
                'request' => $request->all()
            ]);
            
            return $this->errorResponse('Failed to retrieve recent audience data', 500);
        }
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

        $user = User::where('linkedin_id', $linkedin_id)->first();

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





    /**
     * Standardized success response
     */
    protected function successResponse($data = [], $message = 'Success', $status = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ], $status);
    }

    /**
     * Standardized error response
     */
    protected function errorResponse($message = 'Error occurred', $status = 400, $errors = null)
    {
        $responseData = [
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];

        if ($errors) {
            $responseData['errors'] = $errors;
        }

        return response()->json($responseData, $status);
    }
}
