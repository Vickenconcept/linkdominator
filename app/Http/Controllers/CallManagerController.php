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
        $originalMessage = $request->original_message ?? null;
        
        Log::info('🔍 Call message generation debug', [
            'original_message_provided' => $originalMessage,
            'recipient' => $request->recipient,
            'company' => $request->company,
            'industry' => $request->industry,
            'will_generate_ai' => (!$originalMessage && $request->recipient)
        ]);
        
        if (!$originalMessage && $request->recipient) {
            Log::info('🤖 Generating AI call message', [
                'recipient' => $request->recipient,
                'company' => $request->company,
                'industry' => $request->industry
            ]);
            
            try {
                $chatGPT = new ChatGPT();
                $aiResult = $chatGPT->generateCallMessage(
                    $request->recipient,
                    $request->company ?? null,
                    $request->industry ?? null
                );
                $originalMessage = $aiResult['content'];
                
                Log::info('✅ AI message generated successfully', [
                    'ai_message' => $originalMessage,
                    'ai_result' => $aiResult
                ]);
            } catch (\Throwable $th) {
                Log::error('❌ AI message generation failed', [
                    'error' => $th->getMessage(),
                    'trace' => $th->getTraceAsString()
                ]);
                
                // Fallback to default message
                $originalMessage = "Hi {$request->recipient}, I'd like to schedule a call to discuss how we can help your business grow. Are you available for a brief conversation this week?";
                
                Log::info('🔄 Using fallback message', [
                    'fallback_message' => $originalMessage
                ]);
            }
        } else {
            Log::info('⏭️ Skipping AI generation', [
                'reason' => $originalMessage ? 'original_message_provided' : 'no_recipient',
                'original_message' => $originalMessage,
                'recipient' => $request->recipient
            ]);
        }

        // Base attributes that should exist
        $attributes = [
            'recipient' => $request->recipient,
            'profile' => $request->profile,
            'sequence' => $request->sequence,
            'call_status' => $request->callStatus ?? 'suggested',
            'user_id' => optional($user)->id,
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
            'original_message' => $originalMessage,
            'campaign_id' => $request->campaign_id,
            'campaign_name' => $request->campaign_name ?? $request->sequence,
            'conversation_history' => json_encode([]), // Initialize empty conversation history
            'last_interaction_at' => now(),
            'interaction_count' => 1,
        ];

        foreach ($optionalFields as $column => $value) {
            if (Schema::hasColumn('call_status', $column)) {
                $attributes[$column] = $value;
            } else {
                Log::warning("⚠️ Column '{$column}' does not exist in call_status table", [
                    'column' => $column,
                    'value' => $value
                ]);
            }
        }

        Log::info('🔍 CallStatus creation debug', [
            'attributes' => $attributes,
            'original_message' => $originalMessage
        ]);

        $callStatus = CallStatus::create($attributes);

        Log::info('✅ CallStatus created', [
            'call_id' => $callStatus->id,
            'original_message_saved' => $callStatus->original_message,
            'conversation_history_initialized' => $callStatus->conversation_history,
            'connection_id' => $callStatus->connection_id
        ]);

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
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ],400);
        }

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
     * Analyze a message reply with AI (simple version for extension)
     */
    public function analyzeMessageReply(Request $request)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ],400);
        }

        $data = $request->validate([
            'message' => 'required|string',
            'leadName' => 'required|string',
            'context' => 'nullable|string',
            'original_message' => 'nullable|string',
            'call_id' => 'nullable|string',
            'connection_id' => 'nullable|string',
            'conversation_urn_id' => 'nullable|string'
        ]);

        try {
            // Get original message with robust lookup strategy
            $originalMessage = $data['original_message'] ?? null;
            $call = null;
            
            if (!$originalMessage) {
                // Try multiple lookup strategies
                if ($data['call_id']) {
                    // First try: direct ID lookup
                    if (is_numeric($data['call_id'])) {
                        $call = CallStatus::where('id', $data['call_id'])->first();
                    }
                    
                    // Second try: treat call_id as connection_id (for extension temporary IDs)
                    if (!$call && !is_numeric($data['call_id'])) {
                        $call = CallStatus::where('connection_id', $data['call_id'])->first();
                    }
                }
                
                // Third try: explicit connection_id parameter
                if (!$call && $data['connection_id']) {
                    $call = CallStatus::where('connection_id', $data['connection_id'])->first();
                }
                
                // Fourth try: conversation_urn_id parameter
                if (!$call && $data['conversation_urn_id']) {
                    $call = CallStatus::where('conversation_urn_id', $data['conversation_urn_id'])->first();
                }
                
                if ($call) {
                    $originalMessage = $call->original_message ?? 'No original message available';
                    Log::info('✅ Found call record for analysis:', [
                        'id' => $call->id,
                        'connection_id' => $call->connection_id,
                        'conversation_urn_id' => $call->conversation_urn_id,
                        'has_original_message' => !empty($call->original_message)
                    ]);
                } else {
                    Log::warning('⚠️ No call record found for analysis', [
                        'call_id' => $data['call_id'],
                        'connection_id' => $data['connection_id'],
                        'conversation_urn_id' => $data['conversation_urn_id']
                    ]);
                }
            }
            
            // Analyze reply with AI using intelligent context analysis
            $chatGPT = new ChatGPT();
            $context = $data['context'] ?? 'LinkedIn message response analysis';
            
            // Build the analysis prompt with original message context
            $originalMessageText = $originalMessage ?: 'Not provided - this appears to be the start of the conversation';
            
            // Use heredoc syntax for maximum reliability
            $analysisPrompt = <<<EOD
You are an expert LinkedIn conversation analyst. Analyze this message reply for call scheduling intent and lead qualification.

ORIGINAL CALL MESSAGE: {$originalMessageText}
REPLY MESSAGE: {$data['message']}
LEAD NAME: {$data['leadName']}
CONTEXT: {$context}

ANALYSIS INSTRUCTIONS:
Analyze the reply message by understanding the context, tone, and underlying intent. Look beyond simple keyword matching and focus on:

1. **Conversation Context**: First, understand how this reply relates to the original call message
   - What was the original purpose of the conversation?
   - How does this reply show progression in the conversation?
   - Is the person responding to the specific call request or going off-topic?

2. **Intent Analysis**: What is the person actually trying to communicate?
   - Are they expressing genuine interest in scheduling a call?
   - Are they politely declining or showing disinterest?
   - Are they asking for more information before deciding?
   - Are they suggesting alternative times or methods?
   - Are they being evasive or non-committal?

3. **Sentiment Analysis**: What is the emotional tone and attitude?
   - Positive: Enthusiastic, excited, eager, grateful
   - Neutral: Professional, matter-of-fact, cautious
   - Negative: Dismissive, frustrated, uninterested, annoyed

4. **Context Understanding**: Consider the full conversation context
   - How does this reply relate to the original call request?
   - Are there any subtle cues about their availability or interest level?
   - What might they be thinking or feeling based on their response?
   - Does their reply show they remember/understand the original message?

5. **Actionable Insights**: What should be the next step?
   - If interested: How can we move forward with scheduling?
   - If hesitant: What information might help them decide?
   - If declining: Is there a way to maintain the relationship?
   - If asking questions: What do they need to know?

REQUIRED OUTPUT (JSON format only - NO OTHER TEXT):
{
  "intent": "available|interested|not_interested|needs_more_info|reschedule_request|busy|greeting|scheduling_request",
  "sentiment": "positive|neutral|negative",
  "next_action": "schedule_call|send_calendar|send_info|follow_up_later|end_conversation|ask_availability",
  "suggested_response": "Appropriate follow-up message based on analysis",
  "lead_score": 1-10,
  "is_positive": true|false,
  "reasoning": "Brief explanation of your analysis"
}

CRITICAL: Return ONLY valid JSON. No explanations, no markdown, no additional text. The response must be parseable JSON.

Focus on understanding the human behind the message, not just matching words.
EOD;

            $aiAnalysis = $chatGPT->generateContent($analysisPrompt);
            $analysis = json_decode($aiAnalysis['content'], true);
            
            // Log the raw AI response for debugging
            Log::info('🤖 AI Analysis Raw Response', [
                'raw_content' => $aiAnalysis['content'],
                'json_decode_result' => $analysis,
                'json_last_error' => json_last_error_msg()
            ]);
            
            // Ensure we have the required fields
            if (!$analysis || json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('⚠️ AI Analysis failed or returned invalid JSON, using fallback', [
                    'raw_content' => $aiAnalysis['content'],
                    'json_error' => json_last_error_msg()
                ]);
                
                $analysis = [
                    'intent' => 'unknown',
                    'sentiment' => 'neutral',
                    'leadScore' => 5,
                    'isPositive' => false,
                    'nextAction' => 'follow_up_later',
                    'suggestedResponse' => 'Thank you for your response. I\'ll follow up with you soon.'
                ];
            } else {
                // Validate that we have the required fields
                $analysis = array_merge([
                    'intent' => 'unknown',
                    'sentiment' => 'neutral',
                    'leadScore' => 5,
                    'isPositive' => false,
                    'nextAction' => 'follow_up_later',
                    'suggestedResponse' => 'Thank you for your response. I\'ll follow up with you soon.'
                ], $analysis);
                
                Log::info('✅ AI Analysis successful', [
                    'intent' => $analysis['intent'],
                    'sentiment' => $analysis['sentiment'],
                    'leadScore' => $analysis['leadScore'],
                    'isPositive' => $analysis['isPositive']
                ]);
            }

            return response()->json([
                'success' => true,
                'analysis' => $analysis,
                'message' => 'Analysis completed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Message analysis failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Analysis failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process call reply with AI analysis
     */
    public function processCallReply(Request $request)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ],400);
        }

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
            
            // Analyze reply with AI using intelligent context analysis
            $chatGPT = new ChatGPT();
            
            // Use heredoc syntax for maximum reliability
            $originalMessageText = $call->original_message ?? 'No original message available';
            $analysisPrompt = <<<EOD
You are an expert LinkedIn conversation analyst. Analyze this message reply for call scheduling intent and lead qualification.

ORIGINAL CALL MESSAGE: {$originalMessageText}
REPLY MESSAGE: {$data['message']}

ANALYSIS INSTRUCTIONS:
Analyze the reply message by understanding the context, tone, and underlying intent. Look beyond simple keyword matching and focus on:

1. **Intent Analysis**: What is the person actually trying to communicate?
   - Are they expressing genuine interest in scheduling a call?
   - Are they politely declining or showing disinterest?
   - Are they asking for more information before deciding?
   - Are they suggesting alternative times or methods?
   - Are they being evasive or non-committal?

2. **Sentiment Analysis**: What is the emotional tone and attitude?
   - Positive: Enthusiastic, excited, eager, grateful
   - Neutral: Professional, matter-of-fact, cautious
   - Negative: Dismissive, frustrated, uninterested, annoyed

3. **Context Understanding**: Consider the full conversation context
   - How does this reply relate to the original call request?
   - Are there any subtle cues about their availability or interest level?
   - What might they be thinking or feeling based on their response?

4. **Actionable Insights**: What should be the next step?
   - If interested: How can we move forward with scheduling?
   - If hesitant: What information might help them decide?
   - If declining: Is there a way to maintain the relationship?
   - If asking questions: What do they need to know?

REQUIRED OUTPUT (JSON format only - NO OTHER TEXT):
{
  "intent": "available|interested|not_interested|needs_more_info|reschedule_request|busy|greeting|scheduling_request",
  "sentiment": "positive|neutral|negative",
  "next_action": "schedule_call|send_calendar|send_info|follow_up_later|end_conversation|ask_availability",
  "suggested_response": "Appropriate follow-up message based on analysis",
  "lead_score": 1-10,
  "is_positive": true|false,
  "reasoning": "Brief explanation of your analysis"
}

CRITICAL: Return ONLY valid JSON. No explanations, no markdown, no additional text. The response must be parseable JSON.

Focus on understanding the human behind the message, not just matching words.
EOD;

            $aiAnalysis = $chatGPT->generateContent($analysisPrompt);
            $analysis = json_decode($aiAnalysis['content'], true);
            
            // Log the raw AI response for debugging
            Log::info('🤖 AI Analysis Raw Response (processCallReply)', [
                'raw_content' => $aiAnalysis['content'],
                'json_decode_result' => $analysis,
                'json_last_error' => json_last_error_msg()
            ]);
            
            // Handle invalid JSON response
            if (!$analysis || json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('⚠️ AI Analysis failed or returned invalid JSON, using fallback (processCallReply)', [
                    'raw_content' => $aiAnalysis['content'],
                    'json_error' => json_last_error_msg()
                ]);
                
                $analysis = [
                    'intent' => 'unknown',
                    'sentiment' => 'neutral',
                    'lead_score' => 5,
                    'is_positive' => false,
                    'next_action' => 'follow_up_later',
                    'suggested_response' => 'Thank you for your response. I\'ll follow up with you soon.'
                ];
            } else {
                // Validate that we have the required fields
                $analysis = array_merge([
                    'intent' => 'unknown',
                    'sentiment' => 'neutral',
                    'lead_score' => 5,
                    'is_positive' => false,
                    'next_action' => 'follow_up_later',
                    'suggested_response' => 'Thank you for your response. I\'ll follow up with you soon.'
                ], $analysis);
                
                Log::info('✅ AI Analysis successful (processCallReply)', [
                    'intent' => $analysis['intent'],
                    'sentiment' => $analysis['sentiment'],
                    'lead_score' => $analysis['lead_score'],
                    'is_positive' => $analysis['is_positive']
                ]);
            }
            
            // Update call status based on AI analysis
            $newStatus = $this->determineCallStatus($analysis['intent'] ?? 'unknown');
            
            // Add AI response to conversation history
            $aiResponse = [
                'type' => 'ai_response',
                'message' => $analysis['suggested_response'] ?? 'Thank you for your response. I\'ll follow up with you soon.',
                'timestamp' => now()->toISOString(),
                'intent' => $analysis['intent'] ?? 'unknown',
                'sentiment' => $analysis['sentiment'] ?? 'neutral',
                'lead_score' => $analysis['lead_score'] ?? 5,
                'is_positive' => $analysis['is_positive'] ?? false,
                'next_action' => $analysis['next_action'] ?? 'follow_up_later',
                'reasoning' => $analysis['reasoning'] ?? ''
            ];
            
            $conversationHistory[] = $aiResponse;
            
            // Prepare update data
            $updateData = [
                'call_status' => $newStatus,
                'conversation_history' => json_encode($conversationHistory),
                'ai_analysis' => $analysis,
                'lead_category' => $this->categorizeLead($analysis),
                'lead_score' => $analysis['lead_score'] ?? 5,
                'last_interaction_at' => now(),
                'interaction_count' => $call->interaction_count + 1
            ];
            
            // If this is a positive response that should trigger calendar link, prepare for scheduling
            if (($analysis['is_positive'] ?? false) || 
                in_array($analysis['intent'] ?? '', ['available', 'interested', 'scheduling_request']) ||
                ($analysis['lead_score'] ?? 0) >= 7) {
                
                // Generate calendar link if not already present
                if (!$call->calendar_link) {
                    $calendarLink = $this->generateCalendarLink($call);
                    $updateData['calendar_link'] = $calendarLink;
                }
                
                // Update status to scheduling_initiated if it's a positive response
                if ($newStatus !== 'scheduling_initiated') {
                    $updateData['call_status'] = 'scheduling_initiated';
                }
            }
            
            $call->update($updateData);
            
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
            'available' => 'scheduling_initiated',
            'scheduling_request' => 'scheduling_initiated',
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
            case 'send_calendar':
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
        // Check if Calendly is enabled and configured
        if (config('services.calendly.enabled') && config('services.calendly.link')) {
            $calendlyLink = config('services.calendly.link');
            
            // Add recipient info as a parameter if it's a Calendly link
            if (strpos($calendlyLink, 'calendly.com') !== false) {
                $recipientName = urlencode($call->recipient);
                $company = urlencode($call->company ?? '');
                return "{$calendlyLink}?name={$recipientName}&email=&a1={$company}";
            }
            
            return $calendlyLink;
        }
        
        // Fallback to internal scheduling page
        $baseUrl = rtrim(config('app.url'), '/');
        return "{$baseUrl}/schedule-call/{$call->id}";
    }

    /**
     * Generate AI-powered scheduling message
     */
    private function generateSchedulingMessage($call)
    {
        try {
            $chatGPT = new ChatGPT();
            
            $originalMessageText = $call->original_message ?? 'No original message available';
            $prompt = "Generate a professional message to schedule a call with this lead:

Lead: {$call->recipient}
Company: {$call->company}
Industry: {$call->industry}
Original Message: {$originalMessageText}

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
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ],400);
        }

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
    public function getCallMessage(Request $request, $id)
    {
        // Get user from lk-id header (same as storeCallStatus)
        $user = User::where('linkedin_id', $request->header('lk-id'))->first();
        
        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 401);
        }

        $call = CallStatus::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$call) {
            return response()->json([
                'error' => 'Call not found'
            ], 404);
        }

        $originalMessage = $call->original_message ?? 'No AI message generated yet';

        $response = [
            'message' => $originalMessage,
            'call_id' => $call->id,
            'recipient' => $call->recipient,
            'original_message' => $originalMessage
        ];
        
        Log::info('🔍 getCallMessage response', [
            'call_id' => $call->id,
            'recipient' => $call->recipient,
            'original_message' => $originalMessage,
            'response' => $response
        ]);
        
        return response()->json($response);
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
     * Check for call responses and return analysis
     */
    public function checkCallResponse(Request $request, $callId)
    {
        try {
            $call = CallStatus::where('id', $callId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Check if there's a recent response (within last 24 hours)
            $hasRecentResponse = false;
            $responseData = null;

            if ($call->conversation_history) {
                $conversationHistory = json_decode($call->conversation_history, true);
                $lastMessage = end($conversationHistory);
                
                if ($lastMessage && $lastMessage['sender'] === 'lead') {
                    $lastResponseTime = \Carbon\Carbon::parse($lastMessage['timestamp']);
                    $hasRecentResponse = $lastResponseTime->isAfter(now()->subDay());
                    
                    if ($hasRecentResponse) {
                        $responseData = [
                            'hasResponse' => true,
                            'message' => $lastMessage['message'],
                            'timestamp' => $lastMessage['timestamp'],
                            'ai_analysis' => $call->ai_analysis,
                            'call_status' => $call->call_status,
                            'lead_score' => $call->lead_score,
                            'lead_category' => $call->lead_category,
                            'isPositive' => $this->isPositiveResponse($call->ai_analysis),
                            'needsAction' => $this->needsAction($call->call_status),
                            'scheduling_ready' => $call->call_status === 'scheduling_initiated'
                        ];
                    }
                }
            }

            return response()->json([
                'hasResponse' => $hasRecentResponse,
                'call_id' => $callId,
                'call_status' => $call->call_status,
                'last_interaction' => $call->last_interaction_at,
                'response_data' => $responseData
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'hasResponse' => false,
                'error' => 'Failed to check response: ' . $th->getMessage()
            ], 422);
        }
    }

    /**
     * Store conversation message in call_status table
     */
    public function storeConversationMessage(Request $request)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ],400);
        }

        $data = $request->validate([
            'call_id' => 'required|string',
            'message' => 'required|string',
            'sender' => 'required|string|in:lead,ai,user',
            'message_type' => 'nullable|string',
            'ai_analysis' => 'nullable|array',
            'lead_name' => 'nullable|string',
            'connection_id' => 'nullable|string',
            'conversation_urn_id' => 'nullable|string'
        ]);

        // Ensure ai_analysis is always set, even if null
        $data['ai_analysis'] = $data['ai_analysis'] ?? null;

        try {
            // Find the call record - MUST already exist from initial creation
            Log::info('🔍 Looking for call record with:', [
                'call_id' => $data['call_id'],
                'connection_id' => $data['connection_id'],
                'conversation_urn_id' => $data['conversation_urn_id']
            ]);

            $call = CallStatus::where('id', $data['call_id'])
                ->orWhere('connection_id', $data['call_id'])
                ->orWhere(function ($q) use ($data) {
                    if (!empty($data['connection_id'])) {
                        $q->where('connection_id', $data['connection_id']);
                    }
                })
                ->orWhere(function ($q) use ($data) {
                    if (!empty($data['conversation_urn_id'])) {
                        $q->where('conversation_urn_id', $data['conversation_urn_id']);
                    }
                })
                ->first();

            if (!$call) {
                // Do NOT create fallback records anymore; ensure single-row per conversation
                Log::warning('⚠️ Call record not found for storeConversationMessage', [
                    'call_id' => $data['call_id'],
                    'connection_id' => $data['connection_id'],
                    'conversation_urn_id' => $data['conversation_urn_id'],
                    'lead_name' => $data['lead_name']
                ]);

                // Let's check what records actually exist
                $existingRecords = CallStatus::where('connection_id', $data['connection_id'])
                    ->orWhere('conversation_urn_id', $data['conversation_urn_id'])
                    ->get(['id', 'connection_id', 'conversation_urn_id', 'recipient']);
                
                Log::info('🔍 Existing records found:', $existingRecords->toArray());

                return response()->json([
                    'message' => 'Call record not found. Please create the call via the initial endpoint and reuse its id.',
                    'error' => 'CALL_NOT_FOUND'
                ], 404);
            }

            Log::info('✅ Found call record:', [
                'id' => $call->id,
                'connection_id' => $call->connection_id,
                'conversation_urn_id' => $call->conversation_urn_id,
                'recipient' => $call->recipient
            ]);

            // Get existing conversation history
            $conversationHistory = json_decode($call->conversation_history ?? '[]', true) ?: [];

            // Create message entry
            $messageEntry = [
                'type' => $data['sender'],
                'message' => $data['message'],
                'timestamp' => now()->toISOString(),
                'message_type' => $data['message_type'] ?? 'text'
            ];

            // Add AI analysis if provided
            if (isset($data['ai_analysis']) && $data['ai_analysis'] && $data['sender'] === 'ai') {
                $messageEntry['ai_analysis'] = $data['ai_analysis'];
                $messageEntry['intent'] = $data['ai_analysis']['intent'] ?? 'unknown';
                $messageEntry['sentiment'] = $data['ai_analysis']['sentiment'] ?? 'neutral';
                $messageEntry['lead_score'] = $data['ai_analysis']['lead_score'] ?? 5;
                $messageEntry['is_positive'] = $data['ai_analysis']['is_positive'] ?? false;
            }

            // Add to conversation history
            $conversationHistory[] = $messageEntry;

            // Update call record
            $updateData = [
                'conversation_history' => json_encode($conversationHistory),
                'last_interaction_at' => now(),
                'interaction_count' => $call->interaction_count + 1
            ];

            // Update AI analysis and lead scoring if this is an AI response
            if ($data['sender'] === 'ai' && isset($data['ai_analysis']) && $data['ai_analysis']) {
                $analysis = $data['ai_analysis'];
                $updateData['ai_analysis'] = $analysis;
                $updateData['lead_category'] = $this->categorizeLead($analysis);
                $updateData['lead_score'] = $analysis['lead_score'] ?? 5;

                // Update call status based on analysis
                if (isset($analysis['intent'])) {
                    $updateData['call_status'] = $this->determineCallStatus($analysis['intent']);
                }

                // Generate calendar link for positive responses
                if (($analysis['is_positive'] ?? false) || 
                    in_array($analysis['intent'] ?? '', ['available', 'interested', 'scheduling_request']) ||
                    ($analysis['lead_score'] ?? 0) >= 7) {

                    if (!$call->calendar_link) {
                        $updateData['calendar_link'] = $this->generateCalendarLink($call);
                    }

                    if (($updateData['call_status'] ?? null) !== 'scheduling_initiated') {
                        $updateData['call_status'] = 'scheduling_initiated';
                    }
                }
            }

            $call->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Conversation message stored successfully',
                'call_id' => $call->id,
            ]);
        } catch (\Throwable $th) {
            Log::error('Failed to store conversation message: '.$th->getMessage(), [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store conversation message',
                'error' => $th->getMessage()
            ], 422);
        }
    }

    /**
     * Get conversation history for a call
     */
    public function getConversationHistory(Request $request, $callId)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ],400);
        }

        try {
            $call = CallStatus::where('id', $callId)
                ->orWhere('connection_id', $callId)
                ->firstOrFail();

            $conversationHistory = json_decode($call->conversation_history ?? '[]', true) ?: [];

            return response()->json([
                'success' => true,
                'call_id' => $call->id,
                'recipient' => $call->recipient,
                'call_status' => $call->call_status,
                'lead_category' => $call->lead_category,
                'lead_score' => $call->lead_score,
                'calendar_link' => $call->calendar_link,
                'conversation_history' => $conversationHistory,
                'total_messages' => count($conversationHistory),
                'last_interaction' => $call->last_interaction_at
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve conversation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate calendar link for positive responses
     */
    public function generateCalendarLinkForCall(Request $request, $callId)
    {
        try {
            // Check if this is a temporary ID (contains underscore and timestamp)
            if (strpos($callId, '_') !== false && is_numeric(substr($callId, strrpos($callId, '_') + 1))) {
                // This is a temporary ID, generate a simple calendar link
                $calendarLink = $this->generateSimpleCalendarLink();
                $schedulingMessage = $this->generateSimpleSchedulingMessage();
                
                return response()->json([
                    'calendar_link' => $calendarLink,
                    'scheduling_message' => $schedulingMessage,
                    'call_status' => 'scheduling_initiated'
                ]);
            }
            
            // Original logic for database call IDs
            $call = CallStatus::where('id', $callId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Generate calendar link
            $calendarLink = $this->generateCalendarLink($call);
            
            // Generate AI scheduling message
            $schedulingMessage = $this->generateSchedulingMessage($call);
            
            // Update call status
            $call->update([
                'call_status' => 'scheduling_initiated',
                'calendar_link' => $calendarLink
            ]);

            return response()->json([
                'calendar_link' => $calendarLink,
                'scheduling_message' => $schedulingMessage,
                'call_status' => 'scheduling_initiated'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to generate calendar link: ' . $th->getMessage()
            ], 422);
        }
    }

    /**
     * Generate simple calendar link for temporary call IDs
     */
    private function generateSimpleCalendarLink()
    {
        // Check if Calendly is enabled and configured
        if (config('services.calendly.enabled') && config('services.calendly.link')) {
            return config('services.calendly.link');
        }
        
        // Fallback to internal scheduling page
        $baseUrl = config('app.url');
        return "{$baseUrl}/schedule-call";
    }

    /**
     * Generate simple scheduling message for temporary call IDs
     */
    private function generateSimpleSchedulingMessage()
    {
        try {
            $chatGPT = new ChatGPT();
            
            $prompt = "Generate a professional message to schedule a call with a LinkedIn lead who has shown interest. Keep it concise, friendly, and include a calendar booking link. The message should be professional but not overly formal.";
            
            $response = $chatGPT->generateContent($prompt);
            
            return $response['content'] ?? "Great! I'd love to schedule a call with you to discuss further. Please use this link to book a time that works for you: [CALENDAR_LINK]";
            
        } catch (\Exception $e) {
            Log::error('Failed to generate simple scheduling message: ' . $e->getMessage());
            return "Great! I'd love to schedule a call with you to discuss further. Please use this link to book a time that works for you: [CALENDAR_LINK]";
        }
    }

    /**
     * Determine if response is positive based on AI analysis
     */
    private function isPositiveResponse($aiAnalysis)
    {
        if (!$aiAnalysis) return false;
        
        $analysis = is_string($aiAnalysis) ? json_decode($aiAnalysis, true) : $aiAnalysis;
        
        $intent = $analysis['intent'] ?? '';
        $sentiment = $analysis['sentiment'] ?? '';
        $leadScore = $analysis['lead_score'] ?? 0;
        
        return in_array($intent, ['interested', 'available', 'scheduling_request', 'reschedule_request']) && 
               $sentiment === 'positive' && 
               $leadScore >= 7;
    }

    /**
     * Check if call needs action based on status
     */
    private function needsAction($callStatus)
    {
        return in_array($callStatus, ['interested', 'needs_info', 'replied']);
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
