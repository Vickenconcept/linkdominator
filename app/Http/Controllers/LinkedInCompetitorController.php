<?php

namespace App\Http\Controllers;

use App\Jobs\FetchCompetitorFollowersJob;
use App\Jobs\FetchAudienceEmailJob;
use App\Models\Audience;
use App\Models\AudienceList;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LinkedInCompetitorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Debug: Log what we're querying
        $allAudiences = Audience::where('user_id', $user->id)->get();
        Log::info('CompetitorFollowers index - Full debug', [
            'user_id' => $user->id,
            'total_audiences' => $allAudiences->count(),
            'audiences_details' => $allAudiences->map(function($a) {
                return [
                    'id' => $a->id,
                    'audience_id' => $a->audience_id,
                    'name' => $a->audience_name,
                    'tag' => $a->tag,
                    'source' => $a->source,
                    'source_meta' => $a->source_meta,
                    'created_at' => $a->created_at
                ];
            })->toArray()
        ]);

        // Find all audience_ids that have followers for this user
        $allUserAudienceIds = Audience::where('user_id', $user->id)->pluck('audience_id')->toArray();
        $audienceIdsWithFollowers = AudienceList::whereIn('audience_id', $allUserAudienceIds)
            ->distinct()
            ->pluck('audience_id')
            ->toArray();
        
        // First, update ALL audiences that look like competitor audiences (have linkedin.com in name)
        // These are competitor audiences created before we added tag/source fields
        $audiencesToUpdate = Audience::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('audience_name', 'LIKE', '%linkedin.com%')
                  ->orWhere('source', 'linkedin_company_followers');
            })
            ->get();
        
        foreach ($audiencesToUpdate as $aud) {
            $needsSave = false;
            
            // Update tag/source if missing
            if (!$aud->tag) {
                $aud->tag = 'competitor_active_followers';
                $needsSave = true;
            }
            if (!$aud->source) {
                $aud->source = 'linkedin_company_followers';
                $needsSave = true;
            }
            
            // Try to extract company name from source_meta and update audience_name
            if ($aud->source_meta) {
                $meta = json_decode($aud->source_meta, true);
                if ($meta && isset($meta['company_url'])) {
                    $url = $meta['company_url'];
                    if (preg_match('/\/company\/([^\/\?]+)/', $url, $matches)) {
                        $companySlug = $matches[1];
                        $newName = ucfirst($companySlug) . ' - Active Engagers';
                        if ($aud->audience_name !== $newName) {
                            $aud->audience_name = $newName;
                            $needsSave = true;
                        }
                    }
                }
            } elseif ($aud->audience_name && str_contains(strtolower($aud->audience_name), 'linkedin.com')) {
                // If no source_meta but has linkedin.com in name, try to guess from other data
                // Keep it as is for now, but ensure tag/source are set
            }
            
            if ($needsSave) {
                $aud->save();
                Log::info('Updated competitor audience', [
                    'audience_id' => $aud->audience_id,
                    'tag' => $aud->tag,
                    'source' => $aud->source,
                    'new_name' => $aud->audience_name
                ]);
            }
        }
        
        // Query audiences - check by tag, source, OR if they have followers and look like competitors
        $audiences = Audience::where('user_id', $user->id)
            ->where(function($query) use ($audienceIdsWithFollowers) {
                $query->where('source', 'linkedin_company_followers')
                      ->orWhere('tag', 'competitor_active_followers')
                      // Include audiences with followers that have linkedin.com in name
                      ->orWhere(function($q) use ($audienceIdsWithFollowers) {
                          $q->whereIn('audience_id', $audienceIdsWithFollowers)
                            ->where('audience_name', 'LIKE', '%linkedin.com%');
                      });
            })
            ->latest()
            ->paginate(10);

        // Add follower counts to each audience
        foreach ($audiences as $audience) {
            $count = AudienceList::where('audience_id', $audience->audience_id)->count();
            $audience->followers_count = $count;
            
            // Debug log to check counts
            Log::info('Audience follower count', [
                'audience_id' => $audience->audience_id,
                'name' => $audience->audience_name,
                'count' => $count
            ]);
        }

        $hasLinkedInSession = Integration::where('user_id', $user->id)
            ->where('oauth_provider', 'linkedin')
            ->whereNotNull('linkedin_session_cookie')
            ->exists();

        return view('competitor_followers.index', compact('audiences', 'hasLinkedInSession'));
    }

    public function fetch(Request $request)
    {
        $data = $request->validate([
            'company_url' => ['required', 'url'],
        ]);

        $user = Auth::user();

        $integration = Integration::where('user_id', $user->id)
            ->where('oauth_provider', 'linkedin')
            ->whereNotNull('linkedin_session_cookie')
            ->latest('linkedin_session_verified_at')
            ->first();

        if (!$integration) {
            return redirect()
                ->route('competitor-followers.index')
                ->with('error', __('competitor_followers.session_missing'));
        }

        // Extract company name from URL for better naming
        $companySlug = null;
        $companyName = 'Competitor Followers';
        $parsedUrl = parse_url($data['company_url']);
        if (isset($parsedUrl['path'])) {
            // Extract company name from path like /company/microsoft
            if (preg_match('/\/company\/([^\/\?]+)/', $parsedUrl['path'], $matches)) {
                $companySlug = $matches[1];
                $companyName = ucfirst($companySlug) . ' - Active Engagers';
            } elseif ($parsedUrl['host']) {
                $companyName = str_replace('www.', '', $parsedUrl['host']) . ' - Active Engagers';
            }
        }

        // Normalize company URL (remove trailing slash, query params)
        $normalizedUrl = rtrim(parse_url($data['company_url'], PHP_URL_SCHEME) . '://' . parse_url($data['company_url'], PHP_URL_HOST) . parse_url($data['company_url'], PHP_URL_PATH), '/');

        // Check if an audience already exists for this company URL
        $existingAudience = Audience::where('user_id', $user->id)
            ->where('source', 'linkedin_company_followers')
            ->where('tag', 'competitor_active_followers')
            ->get()
            ->first(function($aud) use ($normalizedUrl) {
                $meta = $aud->source_meta ? json_decode($aud->source_meta, true) : null;
                if ($meta && isset($meta['company_url'])) {
                    $existingUrl = rtrim(parse_url($meta['company_url'], PHP_URL_SCHEME) . '://' . parse_url($meta['company_url'], PHP_URL_HOST) . parse_url($meta['company_url'], PHP_URL_PATH), '/');
                    return $existingUrl === $normalizedUrl;
                }
                return false;
            });

        if ($existingAudience) {
            // Use existing audience - update name if needed
            if ($existingAudience->audience_name !== $companyName) {
                $existingAudience->audience_name = $companyName;
                $existingAudience->save();
            }
            $audience = $existingAudience;
            
            Log::info('Using existing competitor audience', [
                'audience_id' => $audience->audience_id,
                'company_url' => $data['company_url']
            ]);
        } else {
            // Create new audience
            $audience = Audience::create([
                'audience_name' => $companyName,
                'audience_id' => now()->timestamp . $user->id,
                'audience_type' => 'LI',
                'user_id' => $user->id,
                'tag' => 'competitor_active_followers',
                'source' => 'linkedin_company_followers',
                'source_meta' => json_encode([
                    'company_url' => $normalizedUrl
                ])
            ]);
            
            Log::info('Created new competitor audience', [
                'audience_id' => $audience->audience_id,
                'company_url' => $normalizedUrl
            ]);
        }

        FetchCompetitorFollowersJob::dispatch(
            $user->id,
            $audience->id,
            $data['company_url'],
            $integration->linkedin_session_cookie,
            $integration->linkedin_user_agent ?? config('services.phantombuster.linkedin_user_agent')
        );

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
            'con_first_name', 'con_last_name', 'con_job_title', 'con_company_name', 'con_location', 'con_profile_url', 'con_email'
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="competitor_followers_' . $audience->id . '.csv"'
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Job Title', 'Company', 'Location', 'Profile URL', 'Email']);
            foreach ($rows as $r) {
                $name = trim(($r->con_first_name ?? '') . ' ' . ($r->con_last_name ?? ''));
                fputcsv($handle, [
                    $name,
                    $r->con_job_title,
                    $r->con_company_name,
                    $r->con_location,
                    $r->con_profile_url,
                    $r->con_email ?? ''
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function fetchEmail(Request $request, $audienceId)
    {
        $user = Auth::user();
        $audience = Audience::where('user_id', $user->id)->where('id', $audienceId)->firstOrFail();

        $request->validate([
            'audience_list_id' => 'required|integer|exists:audience_lists,id',
        ]);

        $audienceListItem = AudienceList::where('id', $request->audience_list_id)
            ->where('audience_id', $audience->audience_id)
            ->firstOrFail();

        // Check if email already exists
        if (!empty($audienceListItem->con_email)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email already exists',
                'email' => $audienceListItem->con_email
            ], 200);
        }

        // Check if we have public identifier or profile URL
        $publicIdentifier = $audienceListItem->con_public_identifier;
        
        if (empty($publicIdentifier) && !empty($audienceListItem->con_profile_url)) {
            // Extract public identifier from profile URL
            if (preg_match('/\/in\/([^\/\?]+)/', $audienceListItem->con_profile_url, $matches)) {
                $publicIdentifier = $matches[1];
            }
        }

        if (empty($publicIdentifier)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile identifier not found. Cannot fetch email.'
            ], 400);
        }

        // Dispatch job to fetch email
        try {
            FetchAudienceEmailJob::dispatch($audienceListItem->id, $publicIdentifier)
                ->onQueue('default');

            return response()->json([
                'status' => 'success',
                'message' => 'Email fetch job dispatched. Please refresh the page in a few moments.'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to dispatch email fetch job', [
                'audience_list_id' => $audienceListItem->id,
                'error' => $th->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch email: ' . $th->getMessage()
            ], 500);
        }
    }

    public function checkEmail($audienceId, $audienceListId)
    {
        $user = Auth::user();
        $audience = Audience::where('user_id', $user->id)->where('id', $audienceId)->firstOrFail();

        $audienceListItem = AudienceList::where('id', $audienceListId)
            ->where('audience_id', $audience->audience_id)
            ->firstOrFail();

        // Check if email fetch was attempted but no email found
        $emailFetchCompleted = !empty($audienceListItem->email_fetch_attempted_at) && empty($audienceListItem->con_email);

        return response()->json([
            'status' => 'success',
            'has_email' => !empty($audienceListItem->con_email),
            'email' => $audienceListItem->con_email ?? null,
            'email_fetch_completed' => $emailFetchCompleted
        ], 200);
    }
}


