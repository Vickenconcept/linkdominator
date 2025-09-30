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
        
        // Debug: Show current time and time ranges
        $now = now();
        $minTime = $now->copy()->addHours($minHours);
        $maxTime = $now->copy()->addHours($maxHours);
        
        $this->info("🕐 Current time: {$now->format('Y-m-d H:i:s')}");
        $this->info("🕐 Looking for calls between: {$minTime->format('Y-m-d H:i:s')} and {$maxTime->format('Y-m-d H:i:s')}");
        
        // Query calls that need reminders based on scheduled_time from Calendly
        // We want calls that are coming up within the time window
        $calls = CallStatus::whereNotNull('scheduled_time')
            ->where('call_status', 'scheduled') // Only scheduled calls
            ->where('scheduled_time', '>=', $minTime) // At least minHours from now
            ->where('scheduled_time', '<=', $maxTime) // But not more than maxHours from now
            ->where(function($query) use ($reminderType) {
                switch($reminderType) {
                    case '16_24':
                        $query->where('reminder_16_24_sent', false);
                        break;
                    case '2_hours':
                        $query->where('reminder_2_hours_sent', false);
                        break;
                    case '10_40_min':
                        $query->where('reminder_10_40_min_sent', false);
                        break;
                }
            })
            ->get();

        $this->info("Found {$calls->count()} calls needing {$reminderType} reminders");
        
        // Debug: Show all scheduled calls for debugging
        $allScheduledCalls = CallStatus::whereNotNull('scheduled_time')
            ->where('call_status', 'scheduled')
            ->get();
            
        $this->info("📋 All scheduled calls:");
        foreach ($allScheduledCalls as $call) {
            $hoursUntilCall = $now->diffInHours($call->scheduled_time, false);
            $this->info("  - Call {$call->id}: {$call->recipient} at {$call->scheduled_time->format('Y-m-d H:i:s')} ({$hoursUntilCall} hours from now)");
            $this->info("    Reminder status: 16_24={$call->reminder_16_24_sent}, 2_hours={$call->reminder_2_hours_sent}, 10_40_min={$call->reminder_10_40_min_sent}");
            
            // Show when next reminders will be sent
            if ($hoursUntilCall > 0) {
                if (!$call->reminder_2_hours_sent && $hoursUntilCall <= 2.5) {
                    $this->info("    ⏰ 2-hour reminder will be sent when call is 1.5-2.5 hours away");
                }
                if (!$call->reminder_10_40_min_sent && $hoursUntilCall <= 0.67) {
                    $this->info("    ⏰ 10-40 min reminder will be sent when call is 10-40 minutes away");
                }
            }
        }

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
        // Get reminder message from call or use default
        $reminderMessage = $this->getReminderMessage($call, $reminderType);
        
        if (!$reminderMessage) {
            $this->warn("No reminder message configured for call {$call->id}");
            return;
        }

        // Generate AI-enhanced reminder if needed
        $enhancedMessage = $this->enhanceReminderWithAI($reminderMessage, $call);
        
        // Send message via LinkedIn API through the extension
        $this->sendLinkedInReminder($call, $enhancedMessage);
        
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
        // Check for custom reminder message in database
        $reminderMessage = \App\Models\CallReminderMessage::where('call_reminder_id', $call->id)->first();
        
        if ($reminderMessage) {
            switch($reminderType) {
                case '16_24':
                    if ($reminderMessage->{'16_24_hours_before_status'} && $reminderMessage->{'16_24_hours_before_message'}) {
                        return $reminderMessage->{'16_24_hours_before_message'};
                    }
                    break;
                case '2_hours':
                    if ($reminderMessage->couple_hours_before_status && $reminderMessage->couple_hours_before_message) {
                        return $reminderMessage->couple_hours_before_message;
                    }
                    break;
                case '10_40_min':
                    if ($reminderMessage->{'10_40_minutes_before_status'} && $reminderMessage->{'10_40_minutes_before_message'}) {
                        return $reminderMessage->{'10_40_minutes_before_message'};
                    }
                    break;
            }
        }
        
        // Fallback to default message
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
     * Send LinkedIn reminder message via extension
     */
    private function sendLinkedInReminder($call, $message)
    {
        try {
            // Get user's LinkedIn ID for the extension
            $user = \App\Models\User::find($call->user_id);
            if (!$user || !$user->linkedin_id) {
                throw new \Exception("User or LinkedIn ID not found for call {$call->id}");
            }

            // Prepare the message data for the extension
            $messageData = [
                'call_id' => $call->id,
                'recipient' => $call->recipient,
                'message' => $message,
                'conversation_urn_id' => $call->conversation_urn_id,
                'linkedin_id' => $user->linkedin_id,
                'reminder_type' => 'call_reminder'
            ];

            // Log the reminder being sent
            Log::info("📤 Sending LinkedIn reminder", [
                'call_id' => $call->id,
                'recipient' => $call->recipient,
                'scheduled_time' => $call->scheduled_time,
                'message' => $message,
                'linkedin_id' => $user->linkedin_id
            ]);

            // Send message via LinkedIn API using the existing infrastructure
            $this->sendLinkedInMessage($messageData);
            
            $this->info("📤 LinkedIn reminder sent to {$call->recipient}");
            
        } catch (\Throwable $th) {
            Log::error("Failed to send LinkedIn reminder", [
                'call_id' => $call->id,
                'error' => $th->getMessage()
            ]);
            throw $th;
        }
    }

    /**
     * Send LinkedIn message via extension webhook
     */
    private function sendLinkedInMessage($messageData)
    {
        try {
            // Create a webhook payload for the extension to process
            $webhookPayload = [
                'type' => 'reminder_message',
                'call_id' => $messageData['call_id'],
                'recipient' => $messageData['recipient'],
                'message' => $messageData['message'],
                'conversation_urn_id' => $messageData['conversation_urn_id'],
                'linkedin_id' => $messageData['linkedin_id'],
                'timestamp' => now()->toISOString()
            ];

            // Log the reminder being queued for the extension
            Log::info("📤 LinkedIn reminder queued for extension", [
                'call_id' => $messageData['call_id'],
                'recipient' => $messageData['recipient'],
                'message' => $messageData['message'],
                'linkedin_id' => $messageData['linkedin_id']
            ]);

            // TODO: Implement actual webhook/queue system for the extension
            // For now, we'll store the reminder in a queue table or use Laravel's queue system
            // The extension can poll this endpoint or we can use webhooks to notify it
            
            // Store reminder in database for extension to pick up
            \DB::table('reminder_queue')->insert([
                'call_id' => $messageData['call_id'],
                'recipient' => $messageData['recipient'],
                'message' => $messageData['message'],
                'conversation_urn_id' => $messageData['conversation_urn_id'],
                'linkedin_id' => $messageData['linkedin_id'],
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->info("📤 LinkedIn reminder queued for extension processing");
            
        } catch (\Throwable $th) {
            Log::error("Failed to queue LinkedIn reminder", [
                'call_id' => $messageData['call_id'],
                'error' => $th->getMessage()
            ]);
            throw $th;
        }
    }
}