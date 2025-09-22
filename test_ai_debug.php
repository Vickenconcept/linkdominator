<?php

/**
 * Debug script to test AI analysis
 * This will help identify why the AI is returning fallback responses
 */

require_once 'vendor/autoload.php';

use App\Services\ChatGPT;

// Test message that should be positive
$testMessage = "Hey! I'd love to chat about this opportunity. When would work best for you?";

echo "🧪 AI Analysis Debug Test\n";
echo "========================\n\n";

echo "Test Message: \"$testMessage\"\n\n";

$chatGPT = new ChatGPT();

$analysisPrompt = "You are an expert LinkedIn conversation analyst. Analyze this message reply for call scheduling intent and lead qualification.

REPLY MESSAGE: $testMessage
LEAD NAME: Test Lead
CONTEXT: LinkedIn message response analysis

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
   - How does this reply relate to the call request?
   - Are there any subtle cues about their availability or interest level?
   - What might they be thinking or feeling based on their response?

4. **Actionable Insights**: What should be the next step?
   - If interested: How can we move forward with scheduling?
   - If hesitant: What information might help them decide?
   - If declining: Is there a way to maintain the relationship?
   - If asking questions: What do they need to know?

REQUIRED OUTPUT (JSON format only - NO OTHER TEXT):
{
  \"intent\": \"available|interested|not_interested|needs_more_info|reschedule_request|busy|greeting|scheduling_request\",
  \"sentiment\": \"positive|neutral|negative\",
  \"next_action\": \"schedule_call|send_calendar|send_info|follow_up_later|end_conversation|ask_availability\",
  \"suggested_response\": \"Appropriate follow-up message based on analysis\",
  \"lead_score\": 1-10,
  \"is_positive\": true|false,
  \"reasoning\": \"Brief explanation of your analysis\"
}

CRITICAL: Return ONLY valid JSON. No explanations, no markdown, no additional text. The response must be parseable JSON.

Focus on understanding the human behind the message, not just matching words.";

try {
    echo "🤖 Calling ChatGPT API...\n";
    $aiAnalysis = $chatGPT->generateContent($analysisPrompt);
    
    echo "✅ API Response received\n";
    echo "Raw Content Length: " . strlen($aiAnalysis['content']) . " characters\n";
    echo "Raw Content:\n";
    echo "---\n";
    echo $aiAnalysis['content'] . "\n";
    echo "---\n\n";
    
    // Try to parse JSON
    $analysis = json_decode($aiAnalysis['content'], true);
    $jsonError = json_last_error();
    
    echo "JSON Parse Results:\n";
    echo "- JSON Error Code: $jsonError\n";
    echo "- JSON Error Message: " . json_last_error_msg() . "\n";
    echo "- Parsed Successfully: " . ($jsonError === JSON_ERROR_NONE ? 'Yes' : 'No') . "\n\n";
    
    if ($jsonError === JSON_ERROR_NONE && $analysis) {
        echo "✅ JSON Parsed Successfully!\n";
        echo "Analysis Results:\n";
        echo "- Intent: " . ($analysis['intent'] ?? 'MISSING') . "\n";
        echo "- Sentiment: " . ($analysis['sentiment'] ?? 'MISSING') . "\n";
        echo "- Lead Score: " . ($analysis['lead_score'] ?? 'MISSING') . "\n";
        echo "- Is Positive: " . ($analysis['is_positive'] ?? 'MISSING') . "\n";
        echo "- Suggested Response: " . ($analysis['suggested_response'] ?? 'MISSING') . "\n";
        echo "- Reasoning: " . ($analysis['reasoning'] ?? 'MISSING') . "\n\n";
        
        // Test positive detection logic
        $intent = $analysis['intent'] ?? '';
        $sentiment = $analysis['sentiment'] ?? '';
        $leadScore = $analysis['lead_score'] ?? 0;
        $isPositiveFlag = $analysis['is_positive'] ?? false;
        
        $isPositive = $isPositiveFlag || 
                      ($intent && (
                          $intent === 'available' ||
                          $intent === 'interested' ||
                          $intent === 'scheduling_request'
                      )) ||
                      ($sentiment === 'positive') ||
                      ($leadScore >= 7);
        
        echo "🎯 Positive Detection Test:\n";
        echo "- Is Positive Flag: " . ($isPositiveFlag ? 'Yes' : 'No') . "\n";
        echo "- Intent Check: " . (in_array($intent, ['available', 'interested', 'scheduling_request']) ? 'Yes' : 'No') . "\n";
        echo "- Sentiment Check: " . ($sentiment === 'positive' ? 'Yes' : 'No') . "\n";
        echo "- Lead Score Check: " . ($leadScore >= 7 ? 'Yes' : 'No') . "\n";
        echo "- Final Is Positive: " . ($isPositive ? 'YES - CALENDAR LINK SHOULD BE SENT!' : 'No') . "\n";
        
    } else {
        echo "❌ JSON Parse Failed!\n";
        echo "This is why you're getting the fallback response.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "This is why you're getting the fallback response.\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "If you see 'YES - CALENDAR LINK SHOULD BE SENT!' above,\n";
echo "then the AI analysis is working correctly.\n";
echo "If you see JSON parse errors, that's the problem.\n";
