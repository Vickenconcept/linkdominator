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
}