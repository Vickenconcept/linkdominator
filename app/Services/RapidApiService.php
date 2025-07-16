<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RapidApiService
{
    /**
     * Linkedin API Service By RapidApi
     */
    const linkedin_provider_api = "https://fresh-linkedin-profile-data.p.rapidapi.com";

    public function search_posts($keyword)
    {
        $url = self::linkedin_provider_api . '/search-posts';

        $headers = [
            "x-rapidapi-key" => config('services.rapidapi.key'),
            "x-rapidapi-host" => "fresh-linkedin-profile-data.p.rapidapi.com",
            "Content-Type" => "application/json"
        ];

        $payload = [
            "search_keywords" => $keyword,
            "sort_by" => "Latest",
            "date_posted" => "",
            "content_type" => "",
            "from_member" => [],
            "from_company" => [],
            "mentioning_member" => [],
            "mentioning_company" => [],
            "author_company" => [],
            "author_industry" => [],
            "author_keyword" => "",
            "page" => 1
        ];

        return Http::withHeaders($headers)
            ->post($url, $payload)
            ->throw()
            ->json();
    }

    public function fetch_profile_posts($profile_url)
    {
        $url = self::linkedin_provider_api . '/get-profile-posts';

        $headers = [
            "x-rapidapi-key" => config('services.rapidapi.key'),
            "x-rapidapi-host" => "fresh-linkedin-profile-data.p.rapidapi.com"
        ];

        $params = [
            "linkedin_url" => $profile_url,
            "type" => "posts"
        ];

        return Http::withHeaders($headers)
            ->get($url, $params)
            ->throw()
            ->json();
    }
}