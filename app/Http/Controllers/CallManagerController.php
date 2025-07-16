<?php

namespace App\Http\Controllers;

use App\Models\CallStatus;
use App\Models\CallReminder;
use App\Models\CallReminderMessage;
use App\Models\User;
use App\Helpers\CampaignHelper;
use Illuminate\Http\Request;
use DB;

class CallManagerController extends Controller
{
    use CampaignHelper;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $callStatus = CallStatus::where('user_id', auth()->user()->id)->paginate(15);

        return view('callmanager.index', compact('callStatus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function callReminder()
    {
        $callReminder = CallReminder::select(DB::raw('call_reminder.*, campaigns.name'))
            ->join('campaigns','call_reminder.campaign','=','campaigns.id')
            ->where('call_reminder.user_id', auth()->user()->id)
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

        CallStatus::create([
            'recipient' => $request->recipient,
            'profile' => $request->profile,
            'sequence' => $request->sequence,
            'call_status' => $request->callStatus,
            'user_id' => $user->id
        ]);

        return response()->jsonify([
            'message' => 'Call status created successfully'
        ],201);
    }
}
