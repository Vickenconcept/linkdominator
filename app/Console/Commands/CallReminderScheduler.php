<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CallStatus;
use App\Models\CallReminderMessage;
use App\Services\ChatGPT;
use Illuminate\Support\Facades\Log;

class CallReminderScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated call reminders based on scheduled times';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🕐 Starting call reminder scheduler...');
        
        // Process 16-24 hours before reminders
        $this->processReminders('16_24', 16, 24);
        
        // Process 2 hours before reminders
        $this->processReminders('2_hours', 1.5, 2.5);
        
        // Process 10-40 minutes before reminders
        $this->processReminders('10_40_min', 0.17, 0.67); // 10-40 minutes in hours
        
        $this->info('✅ Call reminder scheduler completed');
    }

    /**
     * Process reminders for a specific time range
     */
    private function processReminders($reminderType, $minHours, $maxHours)
    {
        $this->info("📅 Processing {$reminderType} reminders...");
        
        $calls = CallStatus::needsReminder($reminderType)
            ->where('scheduled_time', '>=', now()->addHours($minHours))
            ->where('scheduled_time', '<=', now()->addHours($maxHours))
            ->get();

        $this->info("Found {$calls->count()} calls needing {$reminderType} reminders");

        foreach ($calls as $call) {
            try {
                $this->sendReminder($call, $reminderType);
                $this->info("✅ Sent {$reminderType} reminder to {$call->recipient}");
            } catch (\Throwable $th) {
                $this->error("❌ Failed to send reminder to {$call->recipient}: " . $th->getMessage());
                Log::error("Call reminder failed", [
                    'call_id' => $call->id,
                    'recipient' => $call->recipient,
                    'reminder_type' => $reminderType,
                    'error' => $th->getMessage()
                ]);
            }
        }
    }

    /**
     * Send reminder to a specific call
     */
    private function sendReminder($call, $reminderType)
    {
        // Get reminder message from campaign or use default
        $reminderMessage = $this->getReminderMessage($call, $reminderType);
        
        if (!$reminderMessage) {
            $this->warn("No reminder message configured for call {$call->id}");
            return;
        }

        // Generate AI-enhanced reminder if needed
        $enhancedMessage = $this->enhanceReminderWithAI($reminderMessage, $call);
        
        // TODO: Send message via extension or LinkedIn API
        // For now, we'll just log it and mark as sent
        $this->logReminderSent($call, $reminderType, $enhancedMessage);
        
        // Mark reminder as sent
        $call->update([
            "reminder_{$reminderType}_sent" => true
        ]);
    }

    /**
     * Get reminder message for a call
     */
    private function getReminderMessage($call, $reminderType)
    {
        // Try to get from campaign reminder messages
        if ($call->campaign_id) {
            $reminder = CallReminderMessage::where('call_reminder_id', $call->campaign_id)->first();
            if ($reminder) {
                $messageField = "{$reminderType}_message";
                if ($reminder->$messageField) {
                    return $reminder->$messageField;
                }
            }
        }

        // Fallback to default messages
        return $this->getDefaultReminderMessage($reminderType, $call);
    }

    /**
     * Get default reminder message
     */
    private function getDefaultReminderMessage($reminderType, $call)
    {
        $timeUntilCall = $call->scheduled_time->diffForHumans();
        
        switch ($reminderType) {
            case '16_24':
                return "Hi {$call->recipient}, this is a friendly reminder that we have a call scheduled for {$call->scheduled_time->format('M j, Y \a\t g:i A')}. Looking forward to our conversation!";
                
            case '2_hours':
                return "Hi {$call->recipient}, just a quick reminder that our call is coming up in about 2 hours at {$call->scheduled_time->format('g:i A')}. See you soon!";
                
            case '10_40_min':
                return "Hi {$call->recipient}, our call is starting in about 30 minutes at {$call->scheduled_time->format('g:i A')}. I'll be ready to connect!";
                
            default:
                return "Hi {$call->recipient}, this is a reminder about our upcoming call at {$call->scheduled_time->format('M j, Y \a\t g:i A')}.";
        }
    }

    /**
     * Enhance reminder message with AI
     */
    private function enhanceReminderWithAI($message, $call)
    {
        try {
            $chatGPT = new ChatGPT();
            
            $enhancementPrompt = "Enhance this call reminder message to be more personalized and engaging:

Original Message: {$message}
Recipient: {$call->recipient}
Company: {$call->company}
Industry: {$call->industry}
Call Topic: {$call->sequence}

Make it more personal, professional, and engaging while keeping the same core message. Keep it under 150 words.";

            $result = $chatGPT->generateContent($enhancementPrompt);
            return $result['content'];
            
        } catch (\Throwable $th) {
            Log::warning("AI enhancement failed for call reminder", [
                'call_id' => $call->id,
                'error' => $th->getMessage()
            ]);
            return $message; // Return original message if AI fails
        }
    }

    /**
     * Log reminder as sent (placeholder for actual sending)
     */
    private function logReminderSent($call, $reminderType, $message)
    {
        Log::info("Call reminder sent", [
            'call_id' => $call->id,
            'recipient' => $call->recipient,
            'reminder_type' => $reminderType,
            'scheduled_time' => $call->scheduled_time,
            'message' => $message
        ]);
        
        // TODO: Implement actual message sending via extension or LinkedIn API
        // This could involve:
        // 1. Sending to extension to deliver via LinkedIn
        // 2. Direct LinkedIn API call
        // 3. Email notification
        // 4. SMS notification
    }
}