<?php

namespace App\Http\Controllers;

use App\Models\CallStatus;
use App\Models\CallReminder;
use App\Models\CallReminderMessage;
use App\Models\User;
use App\Helpers\CampaignHelper;
use App\Services\ChatGPT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class CallManagerController extends Controller
{
    use CampaignHelper;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $callStatus = CallStatus::where('user_id', Auth::id())->paginate(15);

        return view('callmanager.index', compact('callStatus'));
    }

    /**
     * Display AI-powered call management dashboard
     */
    public function aiDashboard()
    {
        $userId = Auth::id();
        
        // Get statistics
        $totalCalls = CallStatus::where('user_id', $userId)->count();
        $hotLeads = CallStatus::where('user_id', $userId)->where('lead_category', 'hot_lead')->count();
        $warmLeads = CallStatus::where('user_id', $userId)->where('lead_category', 'warm_lead')->count();
        $aiMessages = CallStatus::where('user_id', $userId)->whereNotNull('original_message')->count();
        
        // Get recent calls with AI data
        $recentCalls = CallStatus::where('user_id', $userId)
            ->with('campaign')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        return view('callmanager.ai-dashboard', compact(
            'totalCalls', 'hotLeads', 'warmLeads', 'aiMessages', 'recentCalls'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function callReminder()
    {
        $callReminder = CallReminder::select(DB::raw('call_reminder.*, campaigns.name'))
            ->join('campaigns','call_reminder.campaign','=','campaigns.id')
            ->where('call_reminder.user_id', Auth::id())
            ->paginate(15);

        return view('callmanager.call-reminder', compact('callReminder'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reminderMessages = CallReminderMessage::where('call_reminder_id', $id)->first();

        return response()->json([
            'data' => $reminderMessages
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'call_status_id' => ['required'],
            'call_status' => ['required']
        ]);

        $callStatus = CallStatus::findOrFail($data['call_status_id']);

        $callStatus->update([
            'call_status' => $data['call_status']
        ]);

        notify()->success('Call status updated successfully');
        return redirect()->route('calls');
    }

    public function updateCallReminder(Request $request)
    {
        CallReminderMessage::where('call_reminder_id', $request->id)
            ->update([
                '16_24_hours_before_status' => (int)$request['16_24_hours_before_status'],
                '16_24_hours_before_message' => $request['16_24_hours_before_message'],
                'couple_hours_before_status' => (int)$request['couple_hours_before_status'],
                'couple_hours_before_message' => $request['couple_hours_before_message'],
                '10_40_minutes_before_status' => (int)$request['10_40_minutes_before_status'],
                '10_40_minutes_before_message' => $request['10_40_minutes_before_message']
            ]);

        notify()->success('Call reminder updated successfully');
        return redirect()->route('calls.reminders');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function storeCallStatus(Request $request)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ],400);
        }

        $user = User::where('linkedin_id', $request->header('lk-id'))->first();

        // Generate AI-powered call message if not provided
        $originalMessage = $request->original_message;
        if (!$originalMessage && $request->recipient) {
            try {
                $chatGPT = new ChatGPT();
                $aiResult = $chatGPT->generateCallMessage(
                    $request->recipient,
                    $request->company ?? null,
                    $request->industry ?? null
                );
                $originalMessage = $aiResult['content'];
            } catch (\Throwable $th) {
                // Fallback to default message
                $originalMessage = "Hi {$request->recipient}, I'd like to schedule a call to discuss how we can help your business grow. Are you available for a brief conversation this week?";
            }
        }

        // Base attributes that should exist
        $attributes = [
            'recipient' => $request->recipient,
            'profile' => $request->profile,
            'sequence' => $request->sequence,
            'call_status' => $request->callStatus ?? 'suggested',
            'user_id' => optional($user)->id,
            'original_message' => $originalMessage,
            'campaign_id' => $request->campaign_id,
            'campaign_name' => $request->campaign_name ?? $request->sequence,
            'last_interaction_at' => now(),
            'interaction_count' => 1
        ];

        // Conditionally include optional fields if columns exist
        $optionalFields = [
            'company' => $request->company,
            'industry' => $request->industry,
            'job_title' => $request->job_title,
            'location' => $request->location,
            'linkedin_profile_url' => $request->linkedin_profile_url,
            'connection_id' => $request->connection_id,
            'conversation_urn_id' => $request->conversation_urn_id,
        ];

        foreach ($optionalFields as $column => $value) {
            if (Schema::hasColumn('call_status', $column)) {
                $attributes[$column] = $value;
            }
        }

        $callStatus = CallStatus::create($attributes);

        return response()->json([
            'message' => 'Call status created successfully',
            'call_id' => $callStatus->id
        ],201);
    }

    /**
     * Generate AI-powered call booking message
     */
    public function generateCallMessage(Request $request)
    {
        $data = $request->validate([
            'recipient_name' => 'required|string',
            'company' => 'nullable|string',
            'industry' => 'nullable|string'
        ]);

        try {
            $chatGPT = new ChatGPT();
            $result = $chatGPT->generateCallMessage(
                $data['recipient_name'],
                $data['company'] ?? null,
                $data['industry'] ?? null
            );

            return response()->json([
                'message' => $result['content'],
                'words' => $result['words']
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to generate AI message: ' . $th->getMessage()
            ], 422);
        }
    }

    /**
     * Process call reply with AI analysis
     */
    public function processCallReply(Request $request)
    {
        $data = $request->validate([
            'call_id' => 'required|exists:call_status,id',
            'message' => 'required|string',
            'profile_id' => 'required|string',
            'sender' => 'required|string'
        ]);

        try {
            $call = CallStatus::findOrFail($data['call_id']);
            
            // Update conversation history
            $conversationHistory = $call->conversation_history ? json_decode($call->conversation_history, true) : [];
            $conversationHistory[] = [
                'sender' => $data['sender'],
                'message' => $data['message'],
                'timestamp' => now()->toISOString()
            ];
            
            // Analyze reply with AI
            $chatGPT = new ChatGPT();
            $analysisPrompt = "Analyze this LinkedIn message reply for call scheduling intent:\n\nOriginal Call Message: {$call->original_message}\nReply: {$data['message']}\n\nDetermine:\n1. Intent: interested, not_interested, needs_more_info, reschedule_request, busy\n2. Sentiment: positive, neutral, negative\n3. Next Action: schedule_call, send_info, follow_up_later, end_conversation, ask_availability\n4. Suggested Response: Generate appropriate follow-up message\n5. Lead Score: 1-10 (10 being highest conversion potential)\n\nRespond in JSON format only.";

            $aiAnalysis = $chatGPT->generateContent($analysisPrompt);
            $analysis = json_decode($aiAnalysis['content'], true);
            
            // Update call status based on AI analysis
            $newStatus = $this->determineCallStatus($analysis['intent'] ?? 'unknown');
            
            $call->update([
                'call_status' => $newStatus,
                'conversation_history' => json_encode($conversationHistory),
                'ai_analysis' => $analysis,
                'lead_category' => $this->categorizeLead($analysis),
                'lead_score' => $analysis['lead_score'] ?? 5,
                'last_interaction_at' => now(),
                'interaction_count' => $call->interaction_count + 1
            ]);
            
            // Trigger appropriate action based on AI analysis
            $this->triggerAIAction($call, $analysis);
            
            // If scheduling was initiated, include details so the extension can act
            $schedulingDetails = null;
            if ($call->refresh()->call_status === 'scheduling_initiated') {
                $schedulingDetails = [
                    'calendar_link' => $call->calendar_link,
                    'scheduling_message' => $this->generateSchedulingMessage($call)
                ];
            }
            
            return response()->json([
                'message' => 'Reply processed successfully',
                'analysis' => $analysis,
                'new_status' => $newStatus,
                'suggested_response' => $analysis['suggested_response'] ?? null,
                'scheduling' => $schedulingDetails
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to process reply: ' . $th->getMessage()
            ], 422);
        }
    }

    /**
     * Determine call status based on AI analysis
     */
    private function determineCallStatus($intent)
    {
        return match($intent) {
            'interested' => 'interested',
            'not_interested' => 'not_interested',
            'needs_more_info' => 'needs_info',
            'reschedule_request' => 'reschedule_request',
            'busy' => 'busy',
            default => 'replied'
        };
    }

    /**
     * Categorize lead based on AI analysis
     */
    private function categorizeLead($analysis)
    {
        $score = $analysis['lead_score'] ?? 5;
        $sentiment = $analysis['sentiment'] ?? 'neutral';
        
        if ($score >= 8 && $sentiment === 'positive') {
            return 'hot_lead';
        } elseif ($score >= 6) {
            return 'warm_lead';
        } elseif ($score >= 4) {
            return 'cold_lead';
        } else {
            return 'not_qualified';
        }
    }

    /**
     * Trigger AI-driven action based on analysis
     */
    private function triggerAIAction($call, $analysis)
    {
        $nextAction = $analysis['next_action'] ?? 'follow_up_later';
        
        switch($nextAction) {
            case 'schedule_call':
                $this->initiateCallScheduling($call);
                break;
                
            case 'send_info':
                $this->sendAdditionalInfo($call);
                break;
                
            case 'follow_up_later':
                $this->scheduleFollowUp($call);
                break;
                
            case 'end_conversation':
                $this->endConversation($call);
                break;
        }
    }

    /**
     * Initiate call scheduling process
     */
    private function initiateCallScheduling($call)
    {
        // Generate calendar link (placeholder - integrate with actual calendar service)
        $calendarLink = $this->generateCalendarLink($call);
        
        // Generate AI-powered scheduling message
        $schedulingMessage = $this->generateSchedulingMessage($call);
        
        // Update call status
        $call->update([
            'call_status' => 'scheduling_initiated',
            'calendar_link' => $calendarLink
        ]);
        
        // TODO: Send scheduling message via extension
        Log::info("Call scheduling initiated", [
            'call_id' => $call->id,
            'recipient' => $call->recipient,
            'calendar_link' => $calendarLink,
            'scheduling_message' => $schedulingMessage
        ]);
    }

    /**
     * Generate calendar link for scheduling
     */
    private function generateCalendarLink($call)
    {
        // Placeholder - integrate with Calendly, Google Calendar, or other scheduling service
        $baseUrl = config('app.url');
        return "{$baseUrl}/schedule-call/{$call->id}";
    }

    /**
     * Generate AI-powered scheduling message
     */
    private function generateSchedulingMessage($call)
    {
        try {
            $chatGPT = new ChatGPT();
            
            $prompt = "Generate a professional message to schedule a call with this lead:

Lead: {$call->recipient}
Company: {$call->company}
Industry: {$call->industry}
Original Message: {$call->original_message}

Create a message that:
1. Acknowledges their interest
2. Proposes specific time slots
3. Includes a calendar link
4. Is professional and engaging

Keep it under 100 words.";

            $result = $chatGPT->generateContent($prompt);
            return $result['content'];
            
        } catch (\Throwable $th) {
            return "Hi {$call->recipient}, I'd love to schedule a call to discuss how we can help your business. Please let me know your availability for this week, or you can book directly here: {$call->calendar_link}";
        }
    }

    /**
     * Send additional information to lead
     */
    private function sendAdditionalInfo($call)
    {
        // TODO: Implement sending additional information
        Log::info("Additional info requested for call", ['call_id' => $call->id]);
    }

    /**
     * Schedule follow-up message
     */
    private function scheduleFollowUp($call)
    {
        // TODO: Implement follow-up scheduling
        Log::info("Follow-up scheduled for call", ['call_id' => $call->id]);
    }

    /**
     * End conversation
     */
    private function endConversation($call)
    {
        $call->update(['call_status' => 'conversation_ended']);
        Log::info("Conversation ended for call", ['call_id' => $call->id]);
    }

    /**
     * Schedule a call (manual scheduling)
     */
    public function scheduleCall(Request $request)
    {
        $data = $request->validate([
            'call_id' => 'required|exists:call_status,id',
            'scheduled_time' => 'required|date|after:now',
            'timezone' => 'required|string',
            'meeting_link' => 'nullable|url'
        ]);

        $call = CallStatus::findOrFail($data['call_id']);
        
        $call->update([
            'scheduled_time' => $data['scheduled_time'],
            'timezone' => $data['timezone'],
            'meeting_link' => $data['meeting_link'],
            'call_status' => 'scheduled'
        ]);

        return response()->json([
            'message' => 'Call scheduled successfully',
            'call' => $call
        ]);
    }

    /**
     * Get AI-generated message for a call
     */
    public function getCallMessage($id)
    {
        $call = CallStatus::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'message' => $call->original_message ?? 'No AI message generated yet',
            'call_id' => $call->id,
            'recipient' => $call->recipient
        ]);
    }

    /**
     * Fetch scheduling info for a call (calendar link + suggested message)
     */
    public function getSchedulingInfo($id)
    {
        $call = CallStatus::findOrFail($id);

        return response()->json([
            'call_id' => $call->id,
            'status' => $call->call_status,
            'calendar_link' => $call->calendar_link,
            'scheduling_message' => $this->generateSchedulingMessage($call)
        ]);
    }

    /**
     * Test reminder system
     */
    public function testReminderSystem()
    {
        try {
            // Run the reminder scheduler
            Artisan::call('calls:send-reminders');
            $output = Artisan::output();
            
            return response()->json([
                'message' => 'Reminder system test completed successfully',
                'output' => $output
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Reminder system test failed: ' . $th->getMessage()
            ], 422);
        }
    }

    /**
     * Manually trigger AI message generation for existing calls
     */
    public function triggerAIMessages()
    {
        try {
            $userId = Auth::id();
            $calls = CallStatus::where('user_id', $userId)
                ->whereNull('original_message')
                ->get();

            $generated = 0;
            foreach ($calls as $call) {
                try {
                    $chatGPT = new ChatGPT();
                    $aiResult = $chatGPT->generateCallMessage(
                        $call->recipient,
                        $call->company ?? null,
                        $call->industry ?? null
                    );
                    
                    $call->update([
                        'original_message' => $aiResult['content'],
                        'lead_score' => rand(6, 9), // Random score for testing
                        'lead_category' => rand(6, 9) >= 8 ? 'hot_lead' : 'warm_lead'
                    ]);
                    
                    $generated++;
                } catch (\Throwable $th) {
                    // Skip this call if AI generation fails
                    continue;
                }
            }
            
            return response()->json([
                'message' => "Successfully generated AI messages for {$generated} calls",
                'generated' => $generated,
                'total' => $calls->count()
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to trigger AI messages: ' . $th->getMessage()
            ], 422);
        }
    }
}
