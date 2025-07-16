<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Services\LinkedInService;
use Illuminate\Http\Request;
use Log;

class IntegrationController extends Controller
{
    public function login()
    {
        $linkedin = new LinkedInService;

        return redirect()->away($linkedin->login());
    }

    public function callback(Request $request)
    {
        $oauth_code = $request->query('code');
        $oauth_state = $request->query('state');
        $oauth_error = $request->query('error');

        // Initialize linkedin service
        $linkedin = new LinkedInService;

        // Check if params values are flagged
        if (isset($oauth_error)){
            notify()->error('Connection to linkedin was cancelled.');
            return redirect()->route('social-account.index');
        }elseif (isset($oauth_state) && $oauth_state != $linkedin->state){
            notify()->error('Unauthorized');
            return redirect()->route('social-account.index');
        }

        // Get access token
        try {
            $access_token = $linkedin->getAccessToken($oauth_code);
        } catch (\Throwable $th) {
            Log::debug($th);
            notify()->error($th->getMessage());
            return redirect()->route('social-account.index');
        }

        // Get profile info
        try {
            $profile = $linkedin->getUserProfile($access_token['access_token']);
        } catch (\Throwable $th) {
            Log::debug($th);
            notify()->error($th->getMessage());
            return redirect()->route('social-account.index');
        }

        // Get profile image
        try {
            $profile_img = $linkedin->getUserProfileImg($access_token['access_token']);
        } catch (\Throwable $th) {
            Log::debug($th);
            notify()->error($th->getMessage());
            return redirect()->route('social-account.index');
        }

        // Get profile email
        try {
            $openIdProfile = $linkedin->getOpenIDProfile($access_token['access_token']);
        } catch (\Throwable $th) {
            Log::debug($th);
            notify()->error($th->getMessage());
            return redirect()->route('social-account.index');
        }

        Integration::create([
            "oauth_provider" => "linkedin",
            'access_token' => $access_token['access_token'],
            'refresh_token' => $access_token['refresh_token'] ?? null,
            'expires_in' => $access_token['expires_in'],
            # 'refresh_token_expires_in' => $access_token['refresh_token_expires_in'],
            'oauth_uid' => $profile['id'],
            'first_name' => $profile['localizedFirstName'],
            'last_name' => $profile['localizedLastName'],
            'email' => $openIdProfile['email'],
            'picture' => $profile_img['profilePicture']['displayImage~']['elements'][0]['identifiers'][0]['identifier'],
            'connected_status' => 1,
            'user_id' => auth()->user()->id
        ]);

        notify()->success('LinkedIn account connected successfully.');
        return redirect()->route('social-account.index');
    }
}
