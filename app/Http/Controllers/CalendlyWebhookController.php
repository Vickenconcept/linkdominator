<?php

namespace App\Http\Controllers;

use App\Models\CallStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalendlyWebhookController extends Controller
{
    /**
     * Handle Calendly webhook events
     */
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            $headers = $request->headers->all();
            
            Log::info('📅 Calendly Webhook Received:', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $headers,
                'event' => $payload['event'] ?? 'unknown',
                'payload' => $payload
            ]);

            // Verify webhook signature (optional but recommended)
            // $this->verifyWebhookSignature($request);

            $event = $payload['event'] ?? null;
            
            switch ($event) {
                case 'invitee.created':
                    $this->handleInviteeCreated($payload);
                    break;
                    
                case 'invitee.canceled':
                    $this->handleInviteeCanceled($payload);
                    break;
                    
                case 'invitee.rescheduled':
                    $this->handleInviteeRescheduled($payload);
                    break;
                    
                default:
                    Log::info('📅 Unhandled Calendly event:', ['event' => $event]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Throwable $th) {
            Log::error('❌ Calendly webhook error:', [
                'error' => $th->getMessage(),
                'payload' => $request->all()
            ]);
            
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Handle invitee.created event - when someone books a call
     */
    private function handleInviteeCreated($payload)
    {
        try {
            $eventData = $payload['payload'] ?? [];
            
            Log::info('📅 Processing invitee.created event:', [
                'event_data' => $eventData
            ]);
            // Extract data from the correct structure
            $scheduledEvent = $eventData['scheduled_event'] ?? [];
            $eventId = $scheduledEvent['uri'] ?? null; // Use URI as event ID
            $inviteeId = $eventData['uri'] ?? null; // Use invitee URI as ID
            $scheduledTime = $scheduledEvent['start_time'] ?? null;
            $meetingUrl = $scheduledEvent['location']['location'] ?? null;
            $email = $eventData['email'] ?? null;
            $name = $eventData['name'] ?? null;
            
            if (!$eventId || !$inviteeId || !$scheduledTime) {
                Log::warning('📅 Missing required data in invitee.created event:', [
                    'eventId' => $eventId,
                    'inviteeId' => $inviteeId,
                    'scheduledTime' => $scheduledTime
                ]);
                return;
            }

            // Find the call record by Calendly event ID or by invitee email
            $call = $this->findCallRecord($eventId, $email);
            
            Log::info('📅 Call record lookup result:', [
                'call_found' => $call ? 'yes' : 'no',
                'call_id' => $call ? $call->id : null,
                'eventId' => $eventId,
                'email' => $email
            ]);
            
            if (!$call) {
                Log::warning('📅 No matching call record found for Calendly booking:', [
                    'eventId' => $eventId,
                    'email' => $email
                ]);
                return;
            }

            // Update the call record with booking details
            $updateData = [
                'scheduled_time' => Carbon::parse($scheduledTime),
                'calendly_event_id' => $eventId,
                'calendly_invitee_id' => $inviteeId,
                'calendly_meeting_url' => $meetingUrl,
                'call_status' => 'scheduled'
            ];
            
            Log::info('📅 Updating call record:', [
                'call_id' => $call->id,
                'update_data' => $updateData
            ]);
            
            $call->update($updateData);

            Log::info('✅ Call record updated with Calendly booking:', [
                'call_id' => $call->id,
                'recipient' => $call->recipient,
                'scheduled_time' => $scheduledTime,
                'event_id' => $eventId
            ]);

        } catch (\Throwable $th) {
            Log::error('❌ Error handling invitee.created:', [
                'error' => $th->getMessage(),
                'payload' => $payload
            ]);
        }
    }

    /**
     * Handle invitee.canceled event - when someone cancels a call
     */
    private function handleInviteeCanceled($payload)
    {
        try {
            $eventData = $payload['payload'] ?? [];
            $event = $eventData['event'] ?? [];
            $invitee = $eventData['invitee'] ?? [];
            
            $eventId = $event['uuid'] ?? null;
            
            if (!$eventId) {
                return;
            }

            // Find the call record
            $call = CallStatus::where('calendly_event_id', $eventId)->first();
            
            if ($call) {
                $call->update([
                    'call_status' => 'cancelled',
                    'scheduled_time' => null
                ]);

                Log::info('❌ Call cancelled via Calendly:', [
                    'call_id' => $call->id,
                    'recipient' => $call->recipient,
                    'event_id' => $eventId
                ]);
            }

        } catch (\Throwable $th) {
            Log::error('❌ Error handling invitee.canceled:', [
                'error' => $th->getMessage(),
                'payload' => $payload
            ]);
        }
    }

    /**
     * Handle invitee.rescheduled event - when someone reschedules a call
     */
    private function handleInviteeRescheduled($payload)
    {
        try {
            $eventData = $payload['payload'] ?? [];
            $event = $eventData['event'] ?? [];
            $invitee = $eventData['invitee'] ?? [];
            
            $eventId = $event['uuid'] ?? null;
            $scheduledTime = $event['start_time'] ?? null;
            $meetingUrl = $event['join_url'] ?? null;
            
            if (!$eventId || !$scheduledTime) {
                return;
            }

            // Find the call record
            $call = CallStatus::where('calendly_event_id', $eventId)->first();
            
            if ($call) {
                $call->update([
                    'scheduled_time' => Carbon::parse($scheduledTime),
                    'calendly_meeting_url' => $meetingUrl,
                    'call_status' => 'scheduled',
                    // Reset reminder flags since time changed
                    'reminder_16_24_sent' => false,
                    'reminder_2_hours_sent' => false,
                    'reminder_10_40_min_sent' => false
                ]);

                Log::info('🔄 Call rescheduled via Calendly:', [
                    'call_id' => $call->id,
                    'recipient' => $call->recipient,
                    'new_scheduled_time' => $scheduledTime,
                    'event_id' => $eventId
                ]);
            }

        } catch (\Throwable $th) {
            Log::error('❌ Error handling invitee.rescheduled:', [
                'error' => $th->getMessage(),
                'payload' => $payload
            ]);
        }
    }

    /**
     * Find call record by Calendly event ID or invitee email
     */
    private function findCallRecord($eventId, $email = null)
    {
        Log::info('📅 Searching for call record:', [
            'eventId' => $eventId,
            'email' => $email
        ]);
        
        // First try to find by Calendly event ID
        $call = CallStatus::where('calendly_event_id', $eventId)->first();
        
        if ($call) {
            Log::info('📅 Found call by event ID:', ['call_id' => $call->id]);
            return $call;
        }
        
        // Try to find by call_id from the Calendly link
        // Extract call_id from the event URI or use a different approach
        $callId = $this->extractCallIdFromEvent($eventId);
        if ($callId) {
            $call = CallStatus::where('id', $callId)->first();
            if ($call) {
                Log::info('📅 Found call by database ID:', ['call_id' => $call->id]);
                return $call;
            }
        }

        // If not found, try to find by name (if we have it)
        if ($email) {
            // First try by email
            $call = CallStatus::where('call_status', 'scheduling_initiated')
                ->where(function($query) use ($email) {
                    $query->where('recipient', 'like', "%{$email}%")
                          ->orWhere('original_message', 'like', "%{$email}%");
                })
                ->first();
                
            if ($call) {
                Log::info('📅 Found call by email:', ['call_id' => $call->id]);
                return $call;
            }
            
            // If not found by email, try by name
            $name = $this->extractNameFromEmail($email);
            if ($name) {
                $call = CallStatus::where('call_status', 'scheduling_initiated')
                    ->where('recipient', 'like', "%{$name}%")
                    ->first();
                    
                if ($call) {
                    Log::info('📅 Found call by name:', ['call_id' => $call->id, 'name' => $name]);
                    return $call;
                }
            }
            
            Log::warning('📅 No call found by email or name:', ['email' => $email, 'name' => $name ?? 'unknown']);
        }

        return $call;
    }

    /**
     * Extract call_id from Calendly event
     */
    private function extractCallIdFromEvent($eventId)
    {
        // The eventId is a URI like: https://api.calendly.com/scheduled_events/542d528a-2f52-4604-907a-079a97e13211
        // We need to find the call_id that was used in the original Calendly link
        // Since we can't get it from the webhook, we'll search for recent call records
        // that have scheduling_initiated status and no calendly_event_id yet
        
        $call = CallStatus::where('call_status', 'scheduling_initiated')
            ->whereNull('calendly_event_id')
            ->orderBy('updated_at', 'desc')
            ->first();
            
        if ($call) {
            Log::info('📅 Found recent call record for webhook matching:', [
                'call_id' => $call->id
            ]);
            return $call->id;
        }
        
        return null;
    }

    /**
     * Extract name from email address
     */
    private function extractNameFromEmail($email)
    {
        // Extract the part before @ from email
        $namePart = explode('@', $email)[0];
        
        // Replace dots, underscores, numbers with spaces
        $name = preg_replace('/[._0-9]+/', ' ', $namePart);
        
        // Capitalize first letter of each word
        $name = ucwords(trim($name));
        
        return $name;
    }

    /**
     * Verify webhook signature (optional security measure)
     */
    private function verifyWebhookSignature(Request $request)
    {
        // Implement webhook signature verification if needed
        // This helps ensure the webhook is actually from Calendly
    }
}