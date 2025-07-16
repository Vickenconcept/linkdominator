<?php

namespace App\Services;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Uri;
use Illuminate\Support\Facades\Http;
use App\Models\Post;
use Log;

class LinkedInService
{
    public $api;
    private $client;
    private $secret;
    public $state;

    public function __construct()
    {
        $this->api = config('services.linkedin.api');
        $this->client = config('services.linkedin.client');
        $this->secret = config('services.linkedin.secret');
        $this->state = config('services.linkedin.state');
    }

    public function login()
    {
        $url = 'https://www.linkedin.com/oauth/v2/authorization';

        $callback_url = URL::route('integration.callback');

        return "{$url}?response_type=code&client_id={$this->client}&redirect_uri={$callback_url}&state={$this->state}&scope=openid%20profile%20email%20w_member_social%20r_liteprofile%20r_organization_social%20w_organization_social%20rw_organization_admin";
    }

    public function getAccessToken($oauth_code)
    {
        $url = 'https://www.linkedin.com/oauth/v2/accessToken';

        $callback_url = URL::route('integration.callback');

        $params = [
            'grant_type' => 'authorization_code',
            'code' => $oauth_code,
            'redirect_uri' => $callback_url,
            'client_id' => $this->client,
            'client_secret' => $this->secret
        ];

        return Http::asForm()
            ->post($url, $params)
            ->throw()
            ->json();
    }

    public function refreshAccessToken($refresh_token)
    {
        $url = 'https://www.linkedin.com/oauth/v2/accessToken';

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id' => $this->client,
            'client_secret' => $this->secret
        ];

        return Http::asForm()
            ->post($url, $params)
            ->throw()
            ->json();
    }

    public function getUserProfile($access_token)
    {
        $api_url = $this->api . '/me';

        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Connection' => 'Keep-Alive',
            'Content-Type' => 'application/json',
        ];

        return Http::withHeaders($headers)
            ->get($api_url)
            ->throw()
            ->json();
    }

    public function getUserProfileImg($access_token)
    {
        $api_url = $this->api . '/me';

        $params = [
            'projection' => '(id,profilePicture(displayImage~digitalmediaAsset:playableStreams))'
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Connection' => 'Keep-Alive',
            'Content-Type' => 'application/json',
        ];

        return Http::withHeaders($headers)
            ->withQueryParameters($params)
            ->get($api_url)
            ->throw()
            ->json();
    }

    public function getOpenIDProfile($access_token)
    {
        $api_url = $this->api . '/userinfo';

        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Connection' => 'Keep-Alive',
            'Content-Type' => 'application/json',
        ];

        return Http::withHeaders($headers)
            ->get($api_url)
            ->throw()
            ->json();
    }

    public function publishPost($data, $access_token)
    {
        $api_url = $this->api."/ugcPosts";
        $author = "urn:li:person:" . $data['oauth_uid'];
        $content = $data['content'];
        $article_link = $data['article'];
        $post_type = $data['post_type'];

        $headers = [
            'Authorization' => 'Bearer '.$access_token,
            'Connection' => 'Keep-Alive',
            'Content-Type' => 'application/json',
            'X-Restli-Protocol-Version' => '2.0.0'
        ];

        $post = new Post;

        if ($post_type == 'text only'){
            $post_body = [
                "author" => $author,
                "lifecycleState" => "PUBLISHED",
                "specificContent" => [
                    "com.linkedin.ugc.ShareContent" => [
                        "shareCommentary" => [
                            "text" => $content
                        ],
                        "shareMediaCategory" => "NONE"
                    ]
                ],
                "visibility" => [
                    "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
                ]
            ];
        }elseif ($post_type == 'text with article'){
            $post_body = [
                'author' => $author,
                'lifecycleState' => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => [
                            'text' => $content,
                        ],
                        'shareMediaCategory' => 'ARTICLE',
                        'media' => [
                            [
                                'status' => 'READY',
                                'description' => [
                                    'text' => $content,
                                ],
                                'originalUrl' => $article_link,
                            ],
                        ],
                    ]
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                ],
            ];
        }elseif ($post_type == 'text with image'){
            try {
                $register = $this->registerUpload($data, $access_token);
            } catch (\Throwable $th) {
                Log::debug($th);
                $post->where('id', $data['id'])->update([
                    'publish_status' => 'failed',
                    'comment' => $th->getMessage()
                ]);
            }
            
            $uploadUrl = $register['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];
            $asset = $register['value']['asset'];

            try {
                $this->uploadImage($data, $uploadUrl, $access_token);
            } catch (\Throwable $th) {
                Log::debug($th);
                $post->where('id', $data['id'])->update([
                    'publish_status' => 'failed',
                    'comment' => $th->getMessage()
                ]);
            }
            
            $post_body = [
                "author" => $author,
                "lifecycleState" => "PUBLISHED",
                "specificContent" => [
                    "com.linkedin.ugc.ShareContent" => [
                        "shareCommentary" => [
                            "text" => $content
                        ],
                        "shareMediaCategory" => "IMAGE",
                        "media" => [
                            [
                                "status" => "READY",
                                "description" => [
                                    "text" => $content
                                ],
                                "media" => $asset,
                            ]
                        ]
                    ]
                ],
                "visibility" => [
                    "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
                ]
            ];
        }elseif ($post_type == 'carousel'){
            try {
                $post_body = $this->carouselPost($access_token, $data);
            } catch (\Throwable $th) {
                Log::debug($th);
                $post->where('id', $data['id'])->update([
                    'publish_status' => 'failed',
                    'comment' => $th->getMessage()
                ]);
            }
        }

        return Http::withHeaders($headers)
            ->post($api_url, $post_body)
            ->throw()
            ->json();
    }

    public function registerUpload($data, $access_token)
    {
        $url = $this->api."/assets?action=registerUpload";
        $author = "urn:li:person:".$data['oauth_uid'];
        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Connection' => 'Keep-Alive',
            'Content-Type' => 'application/json',
        ];
        $payload = [
            "registerUploadRequest" => [
                "recipes" => [
                    "urn:li:digitalmediaRecipe:feedshare-image"
                ],
                "owner" => $author,
                "serviceRelationships" => [
                    [
                        "relationshipType" => "OWNER",
                        "identifier" => "urn:li:userGeneratedContent"
                    ]
                ]
            ]
        ];

        return Http::withHeaders($headers)
            ->post($url, $payload)
            ->throw()
            ->json();
    }

    public function uploadImage($data, $uploadUrl, $access_token)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Connection' => 'Keep-Alive',
            'Content-Type' => 'application/json',
        ];
        $payload = [
            'upload-file' => URL::to($data['image'])
        ];

        return Http::withHeaders($headers)
            ->post($uploadUrl, $payload)
            ->throw()
            ->json();
    }

    public function carouselPost($access_token, $data)
    {
        $register = $this->registerUpload($data, $access_token);
        $uploadUrl = $register['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];
        $asset = $register['value']['asset'];
        $this->uploadImage($data, $uploadUrl, $access_token);

        $payload = [
            "author" => "urn:li:person:" . $data['oauth_uid'],
            "commentary" => $data['content'],
            "visibility" => "PUBLIC",
            "distribution" => [
                "feedDistribution" => "NONE",
                "targetEntities" => [],
                "thirdPartyDistributionChannels" => []
            ],
            "lifecycleState" => "PUBLISHED",
            "isReshareDisabledByAuthor" => False,
            "content" => [
                "carousel" => [
                    "cards" => [
                        [
                            "media" => [
                                "id" => $asset,
                                "title" => $data['content']
                            ],
                            "landingPage" => "http://www.linkedin.com/"
                        ]
                    ]
                ]
            ],
            "contentLandingPage" => "http://www.linkedin.com/contentLandingPage"
        ];

        return $payload;
    }
}