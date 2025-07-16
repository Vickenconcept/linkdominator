<?php

namespace App\Http\Controllers;

use App\Models\TeamInvite;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use DB;
use Log;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tab = $request->query('tab');

        $teamInvites = [];
        $members = [];
        $team_user_type = $user->user_type;

        if(isset($tab) && $tab == 'invites') {
            $teamInvites = TeamInvite::where('invited_by', $user->id)
                ->with('invitedBy')
                ->latest()
                ->paginate(15);

        }elseif(isset($tab) && $tab == 'members') {
            $query = "team_members.*, ua.name as member, ub.name as owner, sum(case when c.user_id = team_members.member_id and c.status not in ('stop','completed') then 1 else 0 end) active_campaign";
            
            if($user->user_type == 'owner'){
                
                $members = TeamMember::select(DB::raw($query))
                    ->join('users as ua','ua.id','=','team_members.member_id')
                    ->join('users as ub','ub.id','=','team_members.team_owner_id')
                    ->leftJoin('campaigns as c','c.user_id','=','team_members.member_id')
                    ->where('team_members.team_owner_id', $user->id)
                    ->groupByRaw('1,2,3, ua.name, ub.name, team_members.team_owner_id, team_members.created_at, team_members.updated_at')
                    ->orderBy('team_members.id','desc')
                    ->paginate(15);
            }else {
                $teamOwner = TeamMember::where('member_id', $user->id)->first();

                $members = TeamMember::select(DB::raw($query))
                    ->join('users as ua','ua.id','=','team_members.member_id')
                    ->join('users as ub','ub.id','=','team_members.team_owner_id')
                    ->leftJoin('campaigns as c','c.user_id','=','team_members.member_id')
                    ->where('team_members.team_owner_id', $teamOwner->team_owner_id)
                    ->groupByRaw('1,2,3, ua.name, ub.name, team_members.team_owner_id, team_members.created_at, team_members.updated_at')
                    ->orderBy('team_members.id','desc')
                    ->paginate(15);
            }
        }

        return view('team.index', compact('teamInvites','members','team_user_type'));
    }

    public function destroy(string $id)
    {
        $member  = TeamMember::findOrFail($id);
        $tab = $request->query('tab');

        $member->delete();

        notify()->success('Member successfully removed');
        return redirect()->route('team.index',['tab' => $tab]);
    }
}
