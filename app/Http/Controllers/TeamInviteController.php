<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TeamInvite;
use App\Models\TeamMember;
use App\Mail\SendInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TeamInviteController extends Controller
{
    public function sendInvite(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'role' => ['required']
        ]);
        
        TeamInvite::create([
            'email' => $data['email'],
            'role' => $data['role'],
            'invited_by' => auth()->user()->id
        ]);

        // Send Email Invite
        Mail::to($data['email'])->send(new SendInvitationMail(auth()->user()->name));

        notify()->success('Invitation sent successfully');
        return redirect()->route('team.index',['tab' => 'invites']);
    }

    public function resendInvite(string $id)
    {
        $invite = TeamInvite::findOrFail($id);
        
        Mail::to($invite->email)->send(new SendInvitationMail(auth()->user()->name));

        notify()->success('Invitation sent successfully');
        return redirect()->route('team.index',['tab' => 'invites']);
    }

    public function register()
    {
        return view('team.register');
    }

    public function acceptInvite(Request $request)
    {
        $data = $request->validate([
            'name' => ['required'],
            'email' => ['required','email'],
            'password' => ['required']
        ]);

        $invite = TeamInvite::where('email', $data['email'])->first();

        if($invite){
            $user = User::where('email', $data['email'])->first();

            if(!$user){
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt($data['password']),
                    'created_by' => $invite->invited_by,
                    'user_type' => 'team_member'
                ])->assignRole('User');

                // Assign permission FE
                $user->givePermissionTo('FE');
            }

            // Add user to team member table
            TeamMember::create([
                'member_id' => $user->id,
                'role' => $invite->role,
                'team_owner_id' => $invite->invited_by,
            ]);

            // Delete invite
            $invite = TeamInvite::where('email', $data['email'])->delete();

            notify()->success('You have successfully signed up.');
            return redirect()->route('auth.login');
        }else {
            notify()->error('Invitation does not exist. Please check your email is valid');
            return back()->withInput(['email','name']);
        }
    }

    public function destroy(string $id)
    {
        $invite = TeamInvite::findOrFail($id);

        $invite->delete();

        notify()->success('Invitation deleted successfully');
        return redirect()->route('team.index',['tab' => 'invites']);
    }
}
