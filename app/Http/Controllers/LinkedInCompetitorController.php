<?php

namespace App\Http\Controllers;

use App\Jobs\FetchCompetitorFollowersJob;
use App\Models\Audience;
use App\Models\AudienceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LinkedInCompetitorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $audiences = Audience::where('user_id', $user->id)
            ->where('tag', 'competitor_active_followers')
            ->latest()
            ->paginate(10);

        return view('competitor_followers.index', compact('audiences'));
    }

    public function fetch(Request $request)
    {
        $data = $request->validate([
            'company_url' => ['required', 'url'],
            'audience_name' => ['nullable', 'string', 'max:255']
        ]);

        $user = Auth::user();

        $audience = Audience::create([
            'audience_name' => $data['audience_name'] ?? parse_url($data['company_url'], PHP_URL_HOST) ?: 'Competitor Followers',
            'audience_id' => now()->timestamp . $user->id,
            'audience_type' => 'LI',
            'user_id' => $user->id,
            'tag' => 'competitor_active_followers',
            'source' => 'linkedin_company_followers',
            'source_meta' => json_encode([
                'company_url' => $data['company_url']
            ])
        ]);

        FetchCompetitorFollowersJob::dispatch($user->id, $audience->id, $data['company_url']);

        return redirect()->route('competitor-followers.index')
            ->with('status', __('competitor_followers.fetch_started'));
    }

    public function show($audienceId)
    {
        $user = Auth::user();
        $audience = Audience::where('user_id', $user->id)->where('id', $audienceId)->firstOrFail();

        $list = AudienceList::where('audience_id', $audience->audience_id)->latest()->paginate(25);

        return view('competitor_followers.show', compact('audience', 'list'));
    }

    public function exportCsv($audienceId): StreamedResponse
    {
        $user = Auth::user();
        $audience = Audience::where('user_id', $user->id)->where('id', $audienceId)->firstOrFail();

        $rows = AudienceList::where('audience_id', $audience->audience_id)->get([
            'con_first_name', 'con_last_name', 'con_job_title', 'con_company_name', 'con_location', 'con_profile_url', 'con_last_activity'
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="competitor_followers_' . $audience->id . '.csv"'
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Job Title', 'Company', 'Location', 'Profile URL', 'Last Activity']);
            foreach ($rows as $r) {
                $name = trim(($r->con_first_name ?? '') . ' ' . ($r->con_last_name ?? ''));
                fputcsv($handle, [
                    $name,
                    $r->con_job_title,
                    $r->con_company_name,
                    $r->con_location,
                    $r->con_profile_url,
                    optional($r->con_last_activity)->toDateTimeString()
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}


