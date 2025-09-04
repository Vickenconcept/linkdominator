<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Campaign;
use App\Models\CampaignList;
use App\Models\CampaignLeadgenRunning;
use App\Models\CampaignSequenceEndorse;
use App\Models\CallReminder;
use App\Models\CallReminderMessage;
use App\Models\Lead;
use App\Models\SnLead;
use App\Http\Resources\CampaignResource;
use App\Helpers\CampaignHelper;
use App\Models\AudienceList;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    use CampaignHelper;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth()->user()->id;

        $query = "campaigns.id, campaigns.name, campaigns.status, date(campaigns.created_at) as created_at, campaigns.sequence_type, campaigns.process_condition, campaigns.user_id, sum(case when campaign_lists.campaign_id=campaigns.id then 1 else 0 end) as total_lead_list";

        $campaigns = Campaign::select(DB::raw($query))
            ->join('campaign_lists', 'campaigns.id', '=', 'campaign_lists.campaign_id')
            ->leftJoin('audiences', 'campaign_lists.list_hash', '=', 'audiences.audience_id')
            ->where('campaigns.user_id', $userId)
            ->groupBy('id', 'name', 'status', 'created_at', 'sequence_type', 'process_condition', 'user_id')
            ->orderBy('campaigns.id', 'desc')
            ->paginate(12);

        foreach ($campaigns as $key => $campaign) {
            // Append leads
            $query1 = "campaign_lists.id, sn_leads_lists.name as name, campaign_lists.list_hash as list_hash, count(sn_leads.id) as leads, 'sn' as source, date(sn_leads_lists.created_at) as created_date";
            $first = CampaignList::select(DB::raw($query1))
                ->join('campaigns', 'campaign_lists.campaign_id', '=', 'campaigns.id')
                ->join('sn_leads_lists', 'campaign_lists.list_hash', '=', 'sn_leads_lists.list_hash')
                ->leftJoin('sn_leads', 'campaign_lists.list_hash', '=', 'sn_leads.sn_list_id')
                ->where('campaigns.user_id', $userId)
                ->where('campaigns.id', $campaign->id)
                ->groupBy('campaign_lists.id', 'name', 'list_hash', 'source', 'created_date');

            $query2 = "campaign_lists.id, audiences.audience_name as name, audiences.audience_id as list_hash, count(audience_lists.id) as leads, 'aud' as source, date(audiences.created_at) as created_date";
            $clist = CampaignList::select(DB::raw($query2))
                ->join('campaigns', 'campaign_lists.campaign_id', '=', 'campaigns.id')
                ->join('audiences', 'campaign_lists.list_hash', '=', 'audiences.audience_id')
                ->leftJoin('audience_lists', 'campaign_lists.list_hash', '=', 'audience_lists.audience_id')
                ->where('campaigns.user_id', $userId)
                ->where('campaigns.id', $campaign->id)
                ->groupBy('campaign_lists.id', 'audiences.audience_name', 'audiences.audience_id', 'source', 'created_date')
                ->union($first)
                ->get();

            $totalList = 0;

            if (count($clist) > 0) {
                foreach ($clist as $clist) {
                    $totalList += $clist->leads;
                }
            }
            $campaigns[$key]['total_leads'] = $totalList;

            // Acceptance rate - Show percentage of invites sent (not accepted)
            $leadgen = CampaignLeadgenRunning::select('campaign_id', 'accept_status', 'status_last_id')
                ->where('campaign_id', $campaign->id)
                ->get();
            $totalLeadgen = $leadgen->count();
            $acceptRate = 0;

                    // Debug: Log all records for this campaign
        logger("🔍 Campaign {$campaign->id} - All leadgen records:", [
            'campaign_id' => $campaign->id,
            'total_records' => $totalLeadgen,
            'records' => $leadgen->toArray()
        ]);
        
        // Also log the raw database query to see what's actually stored
        $rawRecords = DB::table('campaign_leadgen_running')
            ->where('campaign_id', $campaign->id)
            ->get(['campaign_id', 'lead_id', 'accept_status', 'status_last_id', 'current_node_key', 'next_node_key']);
        logger("🔍 Campaign {$campaign->id} - Raw database records:", [
            'campaign_id' => $campaign->id,
            'raw_records' => $rawRecords->toArray()
        ]);

            if ($totalLeadgen > 0) {
                $totalInvitesSent = 0;
                $totalAccepted = 0;
                
                foreach ($leadgen as $leadgen) {
                    // Debug: Log what we're checking
                    // status_last_id values: 1 = initial, 2 = sent, 3 = accepted, 4 = error
                    // Count as "invite sent" if status_last_id is 2 (sent)
                    $isInviteSent = false;
                    
                    // Method 1: Check if status_last_id is 2 (sent)
                    if ($leadgen->status_last_id == 2) {
                        $isInviteSent = true;
                    }
                    
                    // Method 2: Fallback - check if current_node_key is not 0 (indicates processing)
                    if (!$isInviteSent && $leadgen->current_node_key && $leadgen->current_node_key != 0) {
                        $isInviteSent = true;
                        logger("🔍 Using fallback method - current_node_key indicates processing");
                    }
                    
                    // Count invites that have been sent (only if explicitly marked as sent)
                    if ($isInviteSent) {
                        $totalInvitesSent += 1;
                    }
                    // Count accepted invites
                    if ($leadgen->accept_status == 1) {
                        $totalAccepted += 1;
                    }
                    
                    // Debug: Log each record check
                    logger("🔍 Record check:", [
                        'campaign_id' => $campaign->id,
                        'status_last_id' => $leadgen->status_last_id,
                        'status_last_id_type' => gettype($leadgen->status_last_id),
                        'accept_status' => $leadgen->accept_status,
                        'current_node_key' => $leadgen->current_node_key,
                        'is_invite_sent' => $isInviteSent,
                        'total_invites_sent_so_far' => $totalInvitesSent
                    ]);
                }
                
                // Calculate percentage of invites sent (not accepted)
                if ($totalInvitesSent > 0) {
                    $acceptRate = round(($totalInvitesSent / $totalLeadgen) * 100);
                }
                
                // Log for debugging
                logger("📊 Campaign {$campaign->id} accept rate calculation:", [
                    'campaign_id' => $campaign->id,
                    'total_leadgen' => $totalLeadgen,
                    'total_invites_sent' => $totalInvitesSent,
                    'total_accepted' => $totalAccepted,
                    'accept_rate' => $acceptRate
                ]);
            } else {
                logger("⚠️ Campaign {$campaign->id} - No leadgen records found");
            }
            $campaigns[$key]['accept_rate'] = $acceptRate;
        }

        return view('campaign.index', compact('campaigns'));
    }

    /**
     * Debug accept rate calculation for a specific campaign
     */
    public function debugAcceptRate(Request $request, string $id)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        $campaign = Campaign::findOrFail($id);
        
        // Get all leadgen records for this campaign
        $leadgen = CampaignLeadgenRunning::select('campaign_id', 'lead_id', 'accept_status', 'status_last_id', 'current_node_key', 'next_node_key')
            ->where('campaign_id', $id)
            ->get();
        
        $totalLeadgen = $leadgen->count();
        $totalInvitesSent = 0;
        $totalAccepted = 0;
        
        $detailedRecords = [];
        
        foreach ($leadgen as $record) {
            $isInviteSent = false;
            
            // status_last_id values: 1 = initial, 2 = sent, 3 = accepted, 4 = error
            if ($record->status_last_id == 2) {
                $isInviteSent = true;
            }
            
            if ($isInviteSent) {
                $totalInvitesSent += 1;
            }
            if ($record->accept_status == 1) {
                $totalAccepted += 1;
            }
            
            $detailedRecords[] = [
                'lead_id' => $record->lead_id,
                'status_last_id' => $record->status_last_id,
                'status_last_id_type' => gettype($record->status_last_id),
                'accept_status' => $record->accept_status,
                'is_invite_sent' => $isInviteSent,
                'current_node_key' => $record->current_node_key,
                'next_node_key' => $record->next_node_key
            ];
        }
        
        $acceptRate = $totalLeadgen > 0 ? round(($totalInvitesSent / $totalLeadgen) * 100) : 0;
        
        return response()->json([
            'campaign_id' => $id,
            'campaign_name' => $campaign->name,
            'total_records' => $totalLeadgen,
            'total_invites_sent' => $totalInvitesSent,
            'total_accepted' => $totalAccepted,
            'accept_rate' => $acceptRate,
            'detailed_records' => $detailedRecords,
            'calculation_logic' => [
                'condition' => 'status_last_id == 2 (sent)',
                'formula' => "($totalInvitesSent / $totalLeadgen) * 100 = $acceptRate%"
            ]
        ]);
    }

    /**
     * Get real-time campaign status updates for AJAX requests
     */
    public function getCampaignStatusUpdates(Request $request)
    {
        try {
            $userId = auth()->user()->id;

            $query = "campaigns.id, campaigns.name, campaigns.status, date(campaigns.created_at) as created_at, campaigns.sequence_type, campaigns.process_condition, campaigns.user_id, sum(case when campaign_lists.campaign_id=campaigns.id then 1 else 0 end) as total_lead_list";

            $campaigns = Campaign::select(DB::raw($query))
                ->join('campaign_lists', 'campaigns.id', '=', 'campaign_lists.campaign_id')
                ->leftJoin('audiences', 'campaign_lists.list_hash', '=', 'audiences.audience_id')
                ->where('campaigns.user_id', $userId)
                ->groupBy('id', 'name', 'status', 'created_at', 'sequence_type', 'process_condition', 'user_id')
                ->orderBy('campaigns.id', 'desc')
                ->get();

            foreach ($campaigns as $key => $campaign) {
                // Append leads
                $query1 = "campaign_lists.id, sn_leads_lists.name as name, campaign_lists.list_hash as list_hash, count(sn_leads.id) as leads, 'sn' as source, date(sn_leads_lists.created_at) as created_date";
                $first = CampaignList::select(DB::raw($query1))
                    ->join('campaigns', 'campaign_lists.campaign_id', '=', 'campaigns.id')
                    ->join('sn_leads_lists', 'campaign_lists.list_hash', '=', 'sn_leads_lists.list_hash')
                    ->leftJoin('sn_leads', 'campaign_lists.list_hash', '=', 'sn_leads.sn_list_id')
                    ->where('campaigns.user_id', $userId)
                    ->where('campaigns.id', $campaign->id)
                    ->groupBy('campaign_lists.id', 'name', 'list_hash', 'source', 'created_date');

                $query2 = "campaign_lists.id, audiences.audience_name as name, audiences.audience_id as list_hash, count(audience_lists.id) as leads, 'aud' as source, date(audiences.created_at) as created_date";
                $clist = CampaignList::select(DB::raw($query2))
                    ->join('campaigns', 'campaign_lists.campaign_id', '=', 'campaigns.id')
                    ->join('audiences', 'campaign_lists.list_hash', '=', 'audiences.audience_id')
                    ->leftJoin('audience_lists', 'campaign_lists.list_hash', '=', 'audience_lists.audience_id')
                    ->where('campaigns.user_id', $userId)
                    ->where('campaigns.id', $campaign->id)
                    ->groupBy('campaign_lists.id', 'audiences.audience_name', 'audiences.audience_id', 'source', 'created_date')
                    ->union($first)
                    ->get();

                $totalList = 0;

                if (count($clist) > 0) {
                    foreach ($clist as $clist) {
                        $totalList += $clist->leads;
                    }
                }
                $campaigns[$key]['total_leads'] = $totalList;

                // Acceptance rate - Show percentage of invites sent (not accepted)
                $leadgen = CampaignLeadgenRunning::select('campaign_id', 'accept_status', 'status_last_id')
                    ->where('campaign_id', $campaign->id)
                    ->get();
                $totalLeadgen = $leadgen->count();
                $acceptRate = 0;

                if ($totalLeadgen > 0) {
                    $totalInvitesSent = 0;
                    $totalAccepted = 0;
                    
                    foreach ($leadgen as $leadgen) {
                        // Count invites that have been sent (status_last_id = 2 means sent)
                        if ($leadgen->status_last_id == 2) {
                            $totalInvitesSent += 1;
                        }
                        // Count accepted invites
                        if ($leadgen->accept_status == 1) {
                            $totalAccepted += 1;
                        }
                    }
                    
                    // Calculate percentage of invites sent (not accepted)
                    if ($totalInvitesSent > 0) {
                        $acceptRate = round(($totalInvitesSent / $totalLeadgen) * 100);
                    }
                }
                $campaigns[$key]['accept_rate'] = $acceptRate;
            }

            return response()->json([
                'success' => true,
                'campaigns' => $campaigns,
                'timestamp' => now()
            ]);

        } catch (\Exception $e) {
            logger('Error getting campaign status updates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to get campaign updates',
                'timestamp' => now()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $userId = auth()->user()->id;
        $cid = $request->query('cid');

        $sequenceType = null;
        $campaignlist = [];
        $dbSequenceNode = [];
        $dbSequenceLink = [];
        $campaign = (object)[];

        $leadlistquery = "select id, audience_name as list_name, audience_id as list_hash, (select count(*) from audience_lists where audience_id=audiences.audience_id) as total_leads, concat(audience_id,'-aud') as type, 'Audience' as source, 'aud' as src, created_at  
        from audiences 
        where user_id = {$userId}
        union
        select id, name as list_name, list_hash, (select count(*) from sn_leads where sn_list_id = sn_leads_lists.list_hash) as total_leads, concat(list_hash,'-sn') as type, 'Sales Navigator' as source, 'sn' as src, created_at
        from sn_leads_lists 
        where user_id = {$userId}
        order by list_name";

        $leadlist = DB::select($leadlistquery);

        if (isset($cid)) {
            $query1 = "campaign_lists.id, sn_leads_lists.name as name, campaign_lists.list_hash as list_hash, count(sn_leads.id) as leads, 'sn' as source, date(sn_leads_lists.created_at) as created_date";
            $first = CampaignList::select(DB::raw($query1))
                ->join('campaigns', 'campaign_lists.campaign_id', '=', 'campaigns.id')
                ->join('sn_leads_lists', 'campaign_lists.list_hash', '=', 'sn_leads_lists.list_hash')
                ->leftJoin('sn_leads', 'campaign_lists.list_hash', '=', 'sn_leads.sn_list_id')
                ->where('campaigns.user_id', $userId)
                ->where('campaigns.id', $cid)
                ->groupBy('campaign_lists.id', 'name', 'list_hash', 'source', 'created_date');

            $query2 = "campaign_lists.id, audiences.audience_name as name, audiences.audience_id as list_hash, count(audience_lists.id) as leads, 'aud' as source, date(audiences.created_at) as created_date";
            $campaignlist = CampaignList::select(DB::raw($query2))
                ->join('campaigns', 'campaign_lists.campaign_id', '=', 'campaigns.id')
                ->join('audiences', 'campaign_lists.list_hash', '=', 'audiences.audience_id')
                ->leftJoin('audience_lists', 'campaign_lists.list_hash', '=', 'audience_lists.audience_id')
                ->where('campaigns.user_id', $userId)
                ->where('campaigns.id', $cid)
                ->groupBy('campaign_lists.id', 'audiences.audience_name', 'audiences.audience_id', 'source', 'created_date')
                ->union($first)
                ->paginate(12);

            $campaign = Campaign::find($cid);
            $sequenceType = $campaign->sequence_type;

            if ($sequenceType) {
                $sequence = CampaignSequenceEndorse::where('campaign_id', $cid)->first();
                $dbSequenceNode = json_decode($sequence->node_model);
                $dbSequenceLink = json_decode($sequence->link_model);
            }
        }

        $sequenceTypes = $this->sequenceTypes();

        return view(
            'campaign.create',
            compact('leadlist', 'campaignlist', 'cid', 'campaign', 'sequenceTypes', 'sequenceType', 'dbSequenceNode', 'dbSequenceLink')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $campaignId = $request->campaign_id;

        if (!$campaignId) {
            $campaign = Campaign::create([
                'name' => 'campaign',
                'user_id' => auth()->user()->id
            ]);
        } else {
            $campaign = Campaign::findOrFail($campaignId);
        }

        $listType = explode('-', $request->type);

        CampaignList::create([
            'campaign_id' => $campaign->id,
            'list_hash' => $listType[0],
            'list_source' => $listType[1],
        ]);

        return redirect()->route('campaign.create', ['step' => 'lead', 'cid' => $campaign->id]);
    }

    public function storeSequence(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->id;

        try {
            $this->saveOrUpdateSequence($data);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 422);
        }

        if (in_array($data['sequence_type'], ['lead_gen', 'custom'])) {
            $node_model = json_decode($data['node_model']);
            $has_call = false;

            foreach ($node_model as $node) {
                if ($node->value == 'call')
                    $has_call = true;
            }

            if ($has_call) {
                // Get total leads
                $clist = $this->getCampaignList($data['cid'], $userId);
                $total_leads = 0;

                foreach ($clist as $leads) {
                    $total_leads += (int)$leads->leads;
                }

                $reminder_data = [
                    'campaign' => $data['cid'],
                    'requests' => 0,
                    'recipients' => $total_leads,
                    'contacted' => 0,
                    'replies' => 0,
                    'not_reached' => 0,
                    'user_id' => $userId
                ];

                $call_reminder = new CallReminder;
                $reminder = $call_reminder->where('campaign', $data['cid'])->first();

                if (!$reminder) {
                    $reminder = $call_reminder->create($reminder_data);

                    CallReminderMessage::create([
                        'call_reminder_id' => $reminder->id,
                        '16_24_hours_before_message' => "Hey @firstName,\nJust a heads up about the call scheduled for tomorrow. Hope its a fruitful discussion. Catch you there",
                        'couple_hours_before_message' => "Hello @firstName,\nQuick reminder: Theres a call lined up in about 2 hours. See you soon, Take care.",
                        '10_40_minutes_before_message' => "Hi @firstName,\nThe call is pretty soon. Hoping for a great conversation."
                    ]);
                } else {
                    $call_reminder->where('campaign', $data['cid'])->update($reminder_data);

                    $reminder_message = new CallReminderMessage;
                    $r_message = $reminder_message->where('call_reminder_id', $reminder['id'])->first();

                    if (!$r_message) {
                        $reminder_message->create([
                            'call_reminder_id' => $reminder->id,
                            '16_24_hours_before_message' => "Hey @firstName,\nJust a heads up about the call scheduled for tomorrow. Hope its a fruitful discussion. Catch you there",
                            'couple_hours_before_message' => "Hello @firstName,\nQuick reminder: Theres a call lined up in about 2 hours. See you soon, Take care.",
                            '10_40_minutes_before_message' => "Hi @firstName,\nThe call is pretty soon. Hoping for a great conversation."
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'redirect' => route('campaign.create', ['step' => 'summarize', 'cid' => $data['cid']]),
            'message' => 'Saved successfully',
            'status' => 201
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $campaign = Campaign::findOrFail($id);

        $campaign->update([
            'name' => $request->campaign_name,
            'status' => 'active',
            'process_condition' => json_encode($request->process_condition)
        ]);

        notify()->success('Campaign saved successfully');
        return redirect()->route('campaign');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CampaignSequenceEndorse::where('campaign_id', $id)->delete();
        CampaignList::where('campaign_id', $id)->delete();
        Campaign::where('id', $id)->delete();

        // remove associated call reminder and status
        $reminder = new CallReminder;
        $call_reminder = $reminder->where('campaign', $id)->first();

        if ($call_reminder) {
            CallReminderMessage::where('call_reminder_id', $call_reminder->id)->delete();

            $reminder->where('id', $call_reminder->id)->delete();
        }

        notify()->success('Campaign deleted successfully');
        return redirect()->route('campaign');
    }

    public function removelist(Request $request, string $id)
    {
        $campaignList = CampaignList::findOrFail($id);

        $campaignList->delete();

        notify()->error('List removed successfully.');
        return redirect()->route('campaign.create', ['step' => 'lead', 'cid' => $request->cid]);
    }

    public function saveOrUpdateSequence($data): void
    {
        $campaign = Campaign::findOrFail($data['cid']);

        $sequenceEndorse = new CampaignSequenceEndorse;

        if (!$campaign->sequence_type) {
            $sequenceEndorse->create([
                'campaign_id' => $data['cid'],
                'node_model' => $data['node_model'],
                'link_model' => $data['link_model']
            ]);
        } else {
            $sequenceEndorse->where('campaign_id', $data['cid'])
                ->update([
                    'node_model' => $data['node_model'],
                    'link_model' => $data['link_model']
                ]);
        }

        $campaign->update([
            'sequence_type' => $data['sequence_type']
        ]);
    }

    /**
     * Campaign api endpoints for chrome extension
     * 
     * Display a listing of campaigns resource.
     */
    public function campaign(Request $request)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        $lkId = $request->header('lk-id');
        $user = User::where('linkedin_id', $lkId)->first();
        $campaign_data = [];

        if ($user) {
            $query = "campaigns.id, campaigns.name, campaigns.status, date(campaigns.created_at) as created_at, campaigns.sequence_type, campaigns.process_condition, campaigns.user_id, sum(case when campaign_lists.campaign_id=campaigns.id then 1 else 0 end) as total_lead_list";

            $campaigns = Campaign::select(DB::raw($query))
                ->join('campaign_lists', 'campaigns.id', '=', 'campaign_lists.campaign_id')
                ->leftJoin('audiences', 'campaign_lists.list_hash', '=', 'audiences.audience_id')
                ->where('campaigns.user_id', $user->id)
                ->groupBy('id', 'name', 'status', 'created_at', 'sequence_type', 'process_condition', 'user_id')
                ->orderBy('campaigns.id', 'desc')
                ->get();

            $campaign_list = new CampaignList;

            foreach ($campaigns as $key => $item) {
                $clist = $this->getCampaignList($item->id, $user->id);

                $item['campaign_list'] = $clist;

                array_push($campaign_data, $item);
            }
        } else {
            return response()->json([
                "message" => "Unauthorized",
                "status" => 400
            ], 400);
        }

        return response()->json([
            'data' => $this->campaignResource($campaign_data),
            'status' => 200
        ]);
    }

    /**
     * Display a listing of the specified campaign leads resource.
     */
    public function campaignLeads(Request $request, string $id)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        $campaign = Campaign::findOrFail($id);
        $leads = [];

        $clist = $this->getCampaignList($campaign->id, $campaign->user_id);

        foreach ($clist as $item) {
            foreach ($this->getLeads($item->list_hash, $item->source) as $l) {
                array_push($leads, $l);
            }
        }

        return response()->json([
            'data' => $this->leadResource($leads),
            'status' => 200
        ]);
    }

    /**
     * Display a listing of the specified campaign sequence resource nodes.
     */
    public function campaignSequence(Request $request, string $id)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        $campaign = Campaign::findOrFail($id);
        $campaignSequence = CampaignSequenceEndorse::where('campaign_id', $id)->first();
        $campaignSequence['name'] = $campaign->sequence_type;

        return response()->json([
            'data' => [
                'id' => $campaignSequence->id,
                'name' => $campaignSequence->name,
                'nodeModel' => json_decode($campaignSequence->node_model),
                'linkModel' => json_decode($campaignSequence->link_model),
            ],
            'status' => 200
        ]);
    }

    /**
     * Update all specified resource of the specified campaign (status).
     */
    public function campaignUpdate(Request $request, string $id)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        $lkId = $request->header('lk-id');
        $user = User::where('linkedin_id', $lkId)->first();

        if ($user) {
            $campaign = new Campaign;

            if ($request->has('campaign_name')) {
                $campaign->where('id', $id)->update(['name' => $request->campaign_name]);
            }
            if ($request->has('status')) {
                $campaign->where('id', $id)->update(['status' => $request->status]);
            }
            if ($request->has('process_condition')) {
                $campaign->where('id', $id)->update(['process_condition' => $request->process_condition]);
            }
        } else {
            return response()->json([
                "message" => "Unauthorized",
                "status" => 400
            ], 400);
        }

        return response()->json([
            'status' => 200
        ]);
    }

    /**
     * Update the specified campaign sequence node model
     */
    public function campaignSequenceUpdate(Request $request, string $id)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        $data = [
            'node_key' => (int)$request->nodeKey,
            'node_run_status' => (bool)$request->runStatus
        ];

        $sequenceModel = new CampaignSequenceEndorse;
        $sequence = $sequenceModel->where('campaign_id', $id)->first();
        $node_model = json_decode($sequence->node_model);

        // foreach ($node_model as $key => $item) {
        //     if ($item['key'] == $data['node_key']) {
        //         $node_model[$key]['runStatus'] = $data['node_run_status'];
        //     }
        // }


        $node_model = $this->objectToArray($node_model);

        // Continue your logic...
        foreach ($node_model as $key => $item) {
            if (isset($item['key']) && $item['key'] == $data['node_key']) {
                $node_model[$key]['runStatus'] = $data['node_run_status'];
            }
        }


        try {
            $sequenceModel->where('campaign_id', $id)
                ->update([
                    'node_model' => json_encode($node_model),
                    'link_model' => $sequence->link_model,
                ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 422);
        }

        return response()->json([
            'status' => 200
        ], 201);
    }

    /**
     * Create lead gen sequence running 
     */
    public function createLeadGenRunning(Request $request, string $id)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        $campaign = Campaign::findOrFail($id);
        
        // Check if leadgen running already exists for this campaign
        $existingCount = CampaignLeadgenRunning::where('campaign_id', $id)->count();
        if ($existingCount > 0) {
            return response()->json([
                'message' => 'Leadgen running already exists for this campaign',
                'existing_count' => $existingCount,
                'status' => 200
            ], 200);
        }
        
        $leads = [];

        $clist = $this->getCampaignList($campaign->id, $campaign->user_id);

        foreach ($clist as $item) {
            foreach ($this->getLeads($item->list_hash, $item->source) as $l) {
                array_push($leads, $l);
            }
        }

        $leads = $this->leadResource($leads);

        // Remove duplicates based on lead_id
        $uniqueLeads = collect($leads)->unique('id')->values()->all();
        
        foreach ($uniqueLeads as $key => $lead) {
            // Double-check to prevent race conditions
            $exists = CampaignLeadgenRunning::where('campaign_id', $id)
                ->where('lead_id', $lead['id'])
                ->exists();
                
            if (!$exists) {
                CampaignLeadgenRunning::create([
                    'lead_id' => $lead['id'],
                    'lead_src' => $lead['source'],
                    'lead_list' => $lead['listId'],
                    'campaign_id' => (int)$id,
                    'current_node_key' => 0,
                    'next_node_key' => 0,
                    'accept_status' => 0, // Initialize as not accepted
                    'status_last_id' => 1, // Start at step 1
                ]);
            }
        }

        return response()->json([
            'message' => 'Saved successful',
            'status' => 201
        ], 201);
    }

    /**
     * Update lead gen sequence running
     * current_node_key, next_node_key, accept_status
     */
    public function updateLeadGenRunning(Request $request, string $campaignId, string $leadId)
    {
        logger('🔄 updateLeadGenRunning called with:', [
            'campaignId' => $campaignId,
            'leadId' => $leadId,
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'url' => $request->url()
        ]);
        
        try {
            $user = $this->checkAuthorization($request);
            logger('✅ Authorization successful for user:', ['user_id' => $user->id, 'linkedin_id' => $user->linkedin_id]);
        } catch (\Throwable $th) {
            logger('❌ Authorization failed:', ['error' => $th->getMessage()]);
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        logger('📝 Updating CampaignLeadgenRunning with data:', [
            'campaign_id' => $campaignId,
            'lead_id' => $leadId,
            'accept_status' => $request->acceptedStatus,
            'current_node_key' => $request->currentNodeKey,
            'next_node_key' => $request->nextNodeKey,
            'status_last_id' => $request->statusLastId
        ]);

        $result = CampaignLeadgenRunning::where('campaign_id', $campaignId)
            ->where('lead_id', $leadId)
            ->update([
                'accept_status' => $request->acceptedStatus,
                'current_node_key' => $request->currentNodeKey,
                'next_node_key' => $request->nextNodeKey,
                'status_last_id' => (string) $request->statusLastId // Force string storage
            ]);

        logger('✅ Update result:', ['rows_affected' => $result]);

        return response()->json([
            'message' => 'Updated successful',
            'status' => 201
        ], 201);
    }

    /**
     * Display all lead gen sequence running of the specified campaign
     */
    public function getLeadGenRunning(Request $request, string $campaignId)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        $campaignLeadgen = new CampaignLeadgenRunning;

        $campaignLeadgens = $campaignLeadgen->select(DB::raw('DISTINCT lead_list, lead_src'))
            ->where('campaign_id', $campaignId)
            ->get();

        $leads = [];

        foreach ($campaignLeadgens as $item) {
            foreach ($this->getRunningLeads($item->lead_list, $item->lead_src) as $value) {
                array_push($leads, $value);
            }
        }

        return response()->json([
            'data' => $this->leadResource($leads),
            'status' => 200
        ]);
    }

    public function updateLeadNetworkDegree(Request $request, string $leadId)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        if ($request->leadSrc == 'aud') {
            AudienceList::where('id', $leadId)
                ->update([
                    'con_distance' => $request->networkDegree
                ]);
        } else {
            SnLead::where('id', $leadId)
                ->update([
                    'degree' => explode('_', $request->networkDegree)[1]
                ]);
        }

        return response()->json([
            'message' => 'Updated successful',
            'status' => 201
        ], 201);
    }

    /**
     * Recursively convert objects to arrays.
     */
    private function objectToArray($data)
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (is_array($data)) {
            return array_map([$this, 'objectToArray'], $data); // recursive call
        }

        return $data;
    }
}

