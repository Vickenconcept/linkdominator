<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CalendlyController extends Controller
{
    /**
     * Redirect user to Calendly OAuth
     */
    public function redirect()
    {
        $clientId = config('services.calendly.client_id');
        $redirectUri = config('services.calendly.redirect');
        
        // Debug logging
        Log::info('Calendly OAuth redirect:', [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'config_exists' => !empty($clientId)
        ]);
        
        if (empty($clientId)) {
            Log::error('Calendly client_id is missing from config');
            return redirect()->route('dashboard')->with('error', 'Calendly configuration is missing. Please contact support.');
        }
        
        if (empty($redirectUri)) {
            Log::error('Calendly redirect_uri is missing from config');
            return redirect()->route('dashboard')->with('error', 'Calendly redirect URI is missing. Please contact support.');
        }

        $params = [
            'client_id'     => $clientId,
            'response_type' => 'code',
            'redirect_uri'  => $redirectUri,
            'scope'         => 'default'
        ];
        
        $url = "https://auth.calendly.com/oauth/authorize?" . http_build_query($params);

        Log::info('Calendly OAuth URL:', ['url' => $url]);

        return redirect($url);
    }

    /**
     * Handle Calendly OAuth callback
     */
    public function callback(Request $request)
    {
        try {
            $code = $request->get('code');
            
            if (!$code) {
                return redirect()->route('dashboard')->with('error', 'Calendly authorization failed');
            }

            // Exchange code for access token
            $response = Http::asForm()->post('https://auth.calendly.com/oauth/token', [
                'grant_type'    => 'authorization_code',
                'client_id'     => config('services.calendly.client_id'),
                'client_secret' => config('services.calendly.client_secret'),
                'redirect_uri'  => config('services.calendly.redirect'),
                'code'          => $code,
            ]);

            if (!$response->successful()) {
                Log::error('Calendly token exchange failed:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return redirect()->route('dashboard')->with('error', 'Failed to connect Calendly account');
            }

            $data = $response->json();

            // Get user's organization info
            $userInfo = $this->getUserInfo($data['access_token']);
            
            Log::info('Calendly user info extracted:', $userInfo);
            
            // Save tokens for the logged-in user
            $user = Auth::user();
            $user->update([
                'calendly_access_token'  => $data['access_token'],
                'calendly_refresh_token' => $data['refresh_token'] ?? null,
                'calendly_token_expires' => now()->addSeconds($data['expires_in']),
                'calendly_organization_uri' => $userInfo['organization_uri'] ?? null
            ]);

            // Refresh the user model to get the updated organization URI
            $user->refresh();
            
            Log::info('User after refresh:', [
                'user_id' => $user->id,
                'calendly_organization_uri' => $user->calendly_organization_uri
            ]);

            // Subscribe to webhooks for this user
            $this->subscribeToWebhooks($user, $data['access_token']);

            return redirect()->route('dashboard')->with('success', 'Calendly account connected successfully!');

        } catch (\Throwable $th) {
            Log::error('Calendly OAuth callback error:', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            
            return redirect()->route('dashboard')->with('error', 'Failed to connect Calendly account');
        }
    }

    /**
     * Get user's Calendly organization info
     */
    private function getUserInfo($accessToken)
    {
        try {
            $response = Http::withToken($accessToken)
                ->get('https://api.calendly.com/users/me');

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Calendly user info response:', $data);
                
                // Extract organization URI from the response
                $organizationUri = $data['resource']['current_organization'] ?? null;
                
                return [
                    'organization_uri' => $organizationUri,
                    'user_uri' => $data['resource']['uri'] ?? null,
                    'email' => $data['resource']['email'] ?? null,
                    'name' => $data['resource']['name'] ?? null
                ];
            } else {
                Log::error('Failed to get Calendly user info:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Throwable $th) {
            Log::error('Failed to get Calendly user info:', [
                'error' => $th->getMessage()
            ]);
        }

        return [];
    }

    /**
     * Subscribe to Calendly webhooks for this user
     */
    private function subscribeToWebhooks($user, $accessToken)
    {
        try {
            if (!$user->calendly_organization_uri) {
                Log::warning('No organization URI for user, skipping webhook subscription');
                return;
            }

            $webhookUrl = config('services.calendly.webhook_url');
            
            $response = Http::withToken($accessToken)
                ->post('https://api.calendly.com/webhook_subscriptions', [
                    'url' => $webhookUrl,
                    'events' => [
                        'invitee.created',
                        'invitee.canceled',
                        'invitee_no_show.created'
                    ],
                    'scope' => 'organization',
                    'organization' => $user->calendly_organization_uri
                ]);

            if ($response->successful()) {
                Log::info('Calendly webhook subscription created for user:', [
                    'user_id' => $user->id,
                    'webhook_url' => $webhookUrl
                ]);
            } else {
                Log::error('Failed to create Calendly webhook subscription:', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

        } catch (\Throwable $th) {
            Log::error('Error subscribing to Calendly webhooks:', [
                'user_id' => $user->id,
                'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * Disconnect Calendly account
     */
    public function disconnect()
    {
        try {
            $user = Auth::user();
            
            $user->update([
                'calendly_access_token' => null,
                'calendly_refresh_token' => null,
                'calendly_token_expires' => null,
                'calendly_organization_uri' => null
            ]);

            return redirect()->route('dashboard')->with('success', 'Calendly account disconnected successfully');

        } catch (\Throwable $th) {
            Log::error('Error disconnecting Calendly:', [
                'user_id' => Auth::id(),
                'error' => $th->getMessage()
            ]);
            
            return redirect()->route('dashboard')->with('error', 'Failed to disconnect Calendly account');
        }
    }

    /**
     * Check if user has Calendly connected
     */
    public function status()
    {
        $user = Auth::user();
        
        return response()->json([
            'connected' => !empty($user->calendly_access_token),
            'expires_at' => $user->calendly_token_expires
        ]);
    }
}