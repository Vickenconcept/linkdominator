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
            
            Log::info('📅 Calendly Webhook Received:', [
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
            $event = $eventData['event'] ?? [];
            $invitee = $eventData['invitee'] ?? [];
            
            $eventId = $event['uuid'] ?? null;
            $inviteeId = $invitee['uuid'] ?? null;
            $scheduledTime = $event['start_time'] ?? null;
            $meetingUrl = $event['join_url'] ?? null;
            
            if (!$eventId || !$inviteeId || !$scheduledTime) {
                Log::warning('📅 Missing required data in invitee.created event:', [
                    'eventId' => $eventId,
                    'inviteeId' => $inviteeId,
                    'scheduledTime' => $scheduledTime
                ]);
                return;
            }

            // Find the call record by Calendly event ID or by invitee email
            $call = $this->findCallRecord($eventId, $invitee['email'] ?? null);
            
            if (!$call) {
                Log::warning('📅 No matching call record found for Calendly booking:', [
                    'eventId' => $eventId,
                    'email' => $invitee['email'] ?? null
                ]);
                return;
            }

            // Update the call record with booking details
            $call->update([
                'scheduled_time' => Carbon::parse($scheduledTime),
                'calendly_event_id' => $eventId,
                'calendly_invitee_id' => $inviteeId,
                'calendly_meeting_url' => $meetingUrl,
                'call_status' => 'scheduled'
            ]);

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
        // First try to find by Calendly event ID
        $call = CallStatus::where('calendly_event_id', $eventId)->first();
        
        if ($call) {
            return $call;
        }

        // If not found, try to find by email (if we have it)
        if ($email) {
            $call = CallStatus::where('call_status', 'scheduling_initiated')
                ->where(function($query) use ($email) {
                    $query->where('recipient', 'like', "%{$email}%")
                          ->orWhere('original_message', 'like', "%{$email}%");
                })
                ->first();
        }

        return $call;
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