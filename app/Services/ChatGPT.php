<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatGPT
{
    protected $params;
    protected $token;
    protected $temperature;
    protected $max_token;
    
    protected $ai_types = [
        'first_cold_email' => [
            'prompt' => 'Write a cold email to a prospect about: %s and please make it %s and comprehensive'
        ],
        'linkedin_connection_message' => [
            'prompt' => 'Write a linkedin connection message to someone: %s'
        ],
        'personalized_ice_breaker' => [
            'prompt' => 'write a personilized ice-breaker message for a business prospect'
        ],
        'linkedin_post' => [
            'prompt' => 'Write a linkedin post about: %s '
        ],
        'book_call_message' => [
            'prompt' => 'Write a professional LinkedIn message to book a call with %s from %s in the %s industry. Make it personalized, professional, and include a clear call-to-action for scheduling a meeting. Keep it under 200 words.'
        ]
    ];

    public function __construct($params = null)
    {
        $this->params = $params;
        $this->token = config('services.chatgpt.key');
        $this->temperature = 0.3; // Lower temperature for more consistent analysis
        $this->max_token = 1000; // Increased for more detailed analysis
    }

    public function generate()
    {
        $idea = $this->params['idea'] ?? '';
        $prompt = 'Write answer in ' . ($this->params['language'] ?? 'English') . '. ';

        if($this->params['aitype'] == 'first_cold_email'){
            $prompt .= sprintf($this->ai_types[$this->params['aitype']]['prompt'], $idea, $this->params['write_style']);
        
        }elseif($this->params['aitype'] == 'linkedin_connection_message') {

            switch ($this->params['connection_message_type']) {
                case 'location':
                    $prompt .= sprintf($this->ai_types[$this->params['aitype']]['prompt'], 'from '. $this->params['location']);
                    break;
                
                case 'industry':
                    $prompt .= sprintf($this->ai_types[$this->params['aitype']]['prompt'], 'who is in '. $this->params['industry'] . 'industry');
                    break;

                case 'jobtitle':
                    $prompt .= sprintf($this->ai_types[$this->params['aitype']]['prompt'], 'who is into '. $this->params['jobtitle']);
                    break;

                case 'random':
                    $prompt .= sprintf($this->ai_types[$this->params['aitype']]['prompt'], $idea);
                    break;

                case 'mutual_connection':
                    $prompt .= sprintf($this->ai_types[$this->params['aitype']]['prompt'], 'we share same mutual connections');
                    break;

                default:
                    $prompt .= sprintf($this->ai_types[$this->params['aitype']]['prompt'], 'we share same mutual interest');
                    break;
            }

        }elseif($this->params['aitype'] == 'linkedin_post') {
            $prompt .= sprintf($this->ai_types[$this->params['aitype']]['prompt'], $idea);
        }elseif($this->params['aitype'] == 'book_call_message') {
            $prompt .= sprintf($this->ai_types[$this->params['aitype']]['prompt'], 
                $this->params['recipient_name'] ?? 'a prospect',
                $this->params['company'] ?? 'their company',
                $this->params['industry'] ?? 'their industry'
            );
        }else{
            $prompt .= $this->ai_types[$this->params['aitype']]['prompt'];
        }

        // Check moderation
        $this->checkModeration($prompt);

        // Generate content
        return $this->generateContent($prompt);
    }

    public function checkModeration($prompt)
    {
        $moderation = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json'
        ])
            ->post('https://api.openai.com/v1/moderations', [
                'input' => $prompt,
            ])
            ->throw()
            ->json();

        if($moderation['results'][0]['flagged'] == true) {
            $categories = $moderation['results'][0]['categories'];
            $flagged = '';

            foreach($categories as $category) {
                if ($categories[$category] == true){
                    $flagged .= $category + ' ';
                }
            }

            throw new \Exception("Your idea was flagged as {$flagged}. kindly adjust it and regenerate.", 1);
        }
    }

    public function generateContent($prompt)
    {
        Log::info('🤖 ChatGPT generateContent called', [
            'prompt' => $prompt,
            'model' => 'gpt-4o-mini',
            'max_tokens' => $this->max_token,
            'temperature' => $this->temperature
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json'
            ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an expert LinkedIn conversation analyst specializing in lead qualification and call scheduling. You understand human communication patterns, context, and subtle cues. Analyze messages intelligently by understanding the underlying intent, sentiment, and context rather than relying on keyword matching. Provide structured, actionable insights for conversation management.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => $this->max_token,
                    'temperature' => $this->temperature,
                    'n' => 1,
                ])
                ->throw()
                ->json();
                
            Log::info('✅ ChatGPT API response successful', [
                'response' => $response
            ]);
        } catch (\Throwable $th) {
            Log::error('❌ ChatGPT API call failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }

        $words = 0;

        if($response['choices']) {
            $content = '';
            foreach ($response['choices'] as $key => $value) {
                // Chat Completions API returns content in message.content instead of text
                $text = $value['message']['content'] ?? '';
                $content .= trim($text) . "\r\n\r\n";
                $words += count(explode(" ", trim($text)));
            }
        }else {
            $text = $response['choices'][0]['message']['content'] ?? '';
            $content = trim($text);
            $words = count(explode(" ", $content));
        }

        // Clean up the content to ensure it's valid JSON
        $content = $this->cleanJsonResponse($content);

        return [
            'content' => $content,
            'words' => $words
        ];
    }

    /**
     * Clean and validate JSON response from AI
     */
    private function cleanJsonResponse($content)
    {
        // Remove any markdown formatting
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        
        // Remove any leading/trailing whitespace
        $content = trim($content);
        
        // Try to find JSON object in the response
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $content = $matches[0];
        }
        
        // Validate JSON
        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('⚠️ AI returned invalid JSON, attempting to fix', [
                'original_content' => $content,
                'json_error' => json_last_error_msg()
            ]);
            
            // Try to fix common JSON issues
            $content = str_replace(["\n", "\r"], '', $content);
            $content = preg_replace('/,\s*}/', '}', $content);
            $content = preg_replace('/,\s*]/', ']', $content);
        }
        
        return $content;
    }

    /**
     * Generate AI-powered call booking message
     */
    public function generateCallMessage($recipientName, $company = null, $industry = null)
    {
        $data = [
            'aitype' => 'book_call_message',
            'recipient_name' => $recipientName,
            'company' => $company,
            'industry' => $industry,
            'language' => 'English'
        ];

        Log::info('🤖 ChatGPT generateCallMessage called', [
            'data' => $data,
            'api_key_exists' => !empty($this->token),
            'api_key_length' => strlen($this->token ?? '')
        ]);

        $this->params = $data;
        $result = $this->generate();
        
        Log::info('🤖 ChatGPT generateCallMessage result', [
            'result' => $result
        ]);
        
        return $result;
    }

    /**
     * Analyze full conversation thread for enhanced context understanding
     */
    public function analyzeConversationThread($conversationThread, $originalMessage, $lastReply, $leadName)
    {
        Log::info('🤖 ChatGPT analyzeConversationThread called', [
            'conversation_count' => count($conversationThread),
            'lead_name' => $leadName,
            'has_original_message' => !empty($originalMessage),
            'has_last_reply' => !empty($lastReply)
        ]);

        try {
            // Build conversation summary
            $conversationSummary = $this->buildConversationSummary($conversationThread);
            
            $prompt = <<<EOD
You are an expert LinkedIn conversation analyst with deep understanding of human communication patterns, context, and conversation flow. Analyze this ENTIRE conversation thread to provide comprehensive insights.

CONVERSATION THREAD ANALYSIS:
{$conversationSummary}

ORIGINAL CALL MESSAGE: {$originalMessage}
LATEST REPLY: {$lastReply}
LEAD NAME: {$leadName}

ANALYSIS INSTRUCTIONS:
Analyze the FULL conversation context, not just the last message. Consider:

1. **Conversation Flow & Progression**:
   - How has the conversation evolved from the original message?
   - What patterns do you see in the lead's responses?
   - Are there repeated themes or concerns?
   - How has the lead's engagement level changed over time?

2. **Lead Behavior Analysis**:
   - What does the conversation reveal about the lead's communication style?
   - Are they being consistent in their responses or showing mixed signals?
   - What are their underlying concerns or interests?
   - How do they respond to different types of messages?

3. **Context Understanding**:
   - What is the lead really thinking based on ALL their responses?
   - Are there subtle cues that only become clear when viewing the full conversation?
   - What information have they shared that might be relevant?
   - How has their sentiment evolved throughout the conversation?

4. **Intent & Sentiment Evolution**:
   - How has their intent changed from message to message?
   - What is their current true sentiment considering the full context?
   - Are they showing genuine interest or just being polite?
   - What does their response pattern suggest about their decision-making process?

5. **Strategic Insights**:
   - What approach would work best based on their communication pattern?
   - What information do they need to make a decision?
   - How can we address their underlying concerns?
   - What would be the most appropriate next step?

REQUIRED OUTPUT (JSON format only - NO OTHER TEXT):
{
  "conversation_summary": "Brief summary of the conversation flow and key points",
  "lead_communication_style": "Description of how the lead communicates (direct, cautious, enthusiastic, etc.)",
  "engagement_pattern": "Analysis of how the lead's engagement has changed over time",
  "underlying_concerns": "Any concerns or hesitations the lead has expressed or implied",
  "current_intent": "available|interested|not_interested|needs_more_info|reschedule_request|busy|greeting|scheduling_request|hesitant|mixed_signals",
  "sentiment_evolution": "How sentiment has changed throughout the conversation",
  "context_insights": "Key insights that only become clear from full conversation context",
  "recommended_approach": "What approach would work best for this specific lead",
  "next_action": "schedule_call|send_calendar|send_info|follow_up_later|end_conversation|ask_availability|address_concerns",
  "suggested_response": "Personalized response that considers the full conversation context",
  "lead_score": 1-10,
  "is_positive": true|false,
  "confidence_level": "high|medium|low",
  "reasoning": "Detailed explanation of your analysis considering the full conversation"
}

CRITICAL: Return ONLY valid JSON. No explanations, no markdown, no additional text. The response must be parseable JSON.

Focus on understanding the human behind ALL the messages, not just the latest one.
EOD;

            $aiAnalysis = $this->generateContent($prompt);
            $analysis = json_decode($aiAnalysis['content'], true);
            
            Log::info('🤖 Conversation Thread Analysis Result', [
                'raw_content' => $aiAnalysis['content'],
                'json_decode_result' => $analysis,
                'json_last_error' => json_last_error_msg()
            ]);
            
            // Ensure we have the required fields with fallbacks
            if (!$analysis || json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('⚠️ Conversation Thread Analysis failed, using fallback', [
                    'raw_content' => $aiAnalysis['content'],
                    'json_error' => json_last_error_msg()
                ]);
                
                $analysis = [
                    'conversation_summary' => 'Unable to analyze full conversation',
                    'lead_communication_style' => 'Unknown',
                    'engagement_pattern' => 'Unknown',
                    'underlying_concerns' => 'None identified',
                    'current_intent' => 'unknown',
                    'sentiment_evolution' => 'Unknown',
                    'context_insights' => 'Limited analysis available',
                    'recommended_approach' => 'Standard follow-up',
                    'next_action' => 'follow_up_later',
                    'suggested_response' => 'Thank you for your response. I\'ll follow up with you soon.',
                    'lead_score' => 5,
                    'is_positive' => false,
                    'confidence_level' => 'low',
                    'reasoning' => 'Analysis failed - using fallback'
                ];
            } else {
                // Validate and merge with defaults
                $analysis = array_merge([
                    'conversation_summary' => 'Conversation analyzed',
                    'lead_communication_style' => 'Professional',
                    'engagement_pattern' => 'Consistent',
                    'underlying_concerns' => 'None identified',
                    'current_intent' => 'unknown',
                    'sentiment_evolution' => 'Stable',
                    'context_insights' => 'Standard conversation flow',
                    'recommended_approach' => 'Standard follow-up',
                    'next_action' => 'follow_up_later',
                    'suggested_response' => 'Thank you for your response. I\'ll follow up with you soon.',
                    'lead_score' => 5,
                    'is_positive' => false,
                    'confidence_level' => 'medium',
                    'reasoning' => 'Analysis completed'
                ], $analysis);
                
                Log::info('✅ Conversation Thread Analysis successful', [
                    'current_intent' => $analysis['current_intent'],
                    'lead_score' => $analysis['lead_score'],
                    'is_positive' => $analysis['is_positive'],
                    'confidence_level' => $analysis['confidence_level']
                ]);
            }

            return $analysis;

        } catch (\Throwable $th) {
            Log::error('❌ Conversation Thread Analysis failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            
            // Return fallback analysis
            return [
                'conversation_summary' => 'Analysis failed',
                'lead_communication_style' => 'Unknown',
                'engagement_pattern' => 'Unknown',
                'underlying_concerns' => 'None identified',
                'current_intent' => 'unknown',
                'sentiment_evolution' => 'Unknown',
                'context_insights' => 'Analysis unavailable',
                'recommended_approach' => 'Standard follow-up',
                'next_action' => 'follow_up_later',
                'suggested_response' => 'Thank you for your response. I\'ll follow up with you soon.',
                'lead_score' => 5,
                'is_positive' => false,
                'confidence_level' => 'low',
                'reasoning' => 'Analysis failed: ' . $th->getMessage()
            ];
        }
    }

    /**
     * Build a structured summary of the conversation thread
     */
    private function buildConversationSummary($conversationThread)
    {
        if (empty($conversationThread)) {
            return "No conversation history available.";
        }

        $summary = "CONVERSATION THREAD:\n\n";
        
        foreach ($conversationThread as $index => $message) {
            $messageType = $message['type'] ?? 'unknown';
            $messageText = $message['message'] ?? '';
            $timestamp = $message['timestamp'] ?? '';
            $messageTypeLabel = ucfirst($messageType);
            
            $summary .= "Message " . ($index + 1) . " ({$messageTypeLabel}):\n";
            $summary .= "Time: {$timestamp}\n";
            $summary .= "Content: {$messageText}\n";
            
            // Add any AI analysis if present
            if (isset($message['ai_analysis']) && is_array($message['ai_analysis'])) {
                $aiAnalysis = $message['ai_analysis'];
                $summary .= "AI Analysis: Intent={$aiAnalysis['intent']}, Sentiment={$aiAnalysis['sentiment']}, Score={$aiAnalysis['lead_score']}\n";
            }
            
            $summary .= "\n---\n\n";
        }
        
        return $summary;
    }
}