<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ForgotPasswordNotification;
use App\Models\User;
use Log;

class ForgotPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('auth.forgot-password');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required','email','string','exists:users,email']
        ]);

        $newPassword = Str::random(10);

        User::where('email', $request->email)
            ->update([
                'password' => bcrypt($newPassword)
            ]);

        $user = User::where('email', $request->email)->first();
        // Log::info($newPassword);
        
        // Send password notification
        Notification::send($user, new ForgotPasswordNotification([
            'name' => $user->name,
            'password' => $newPassword
        ]));

        notify()->success('Password has been sent to your mail');
        return redirect()->route('auth.login');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
