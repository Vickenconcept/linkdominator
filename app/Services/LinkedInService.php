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

    /**
     * 🔥 NEW: Publish post using LinkedIn API v2 (Official /rest/posts endpoint)
     * This allows posting from backend without user being online!
     */
    public function publishPostV2($linkedInPost, $integration)
    {
        // LinkedIn's new /rest/posts endpoint doesn't use /v2/ prefix
        $api_url = "https://api.linkedin.com/rest/posts";
        $access_token = $integration->access_token;
        $author = "urn:li:person:" . $integration->oauth_uid;

        Log::info('🔧 Starting publishPostV2', [
            'post_id' => $linkedInPost->id,
            'api_url' => $api_url,
            'author' => $author,
            'has_access_token' => !empty($access_token),
            'token_length' => strlen($access_token ?? ''),
            'integration_id' => $integration->id
        ]);

        // Check if token needs refresh
        if ($this->isTokenExpired($integration)) {
            Log::info('🔄 Access token expired, refreshing...', [
                'integration_id' => $integration->id,
                'expires_in' => $integration->expires_in,
                'updated_at' => $integration->updated_at
            ]);
            
            try {
                $newTokens = $this->refreshAccessToken($integration->refresh_token);
                
                $integration->update([
                    'access_token' => $newTokens['access_token'],
                    'refresh_token' => $newTokens['refresh_token'] ?? $integration->refresh_token,
                    'expires_in' => $newTokens['expires_in'],
                    'updated_at' => now()
                ]);
                
                $access_token = $newTokens['access_token'];
                Log::info('✅ Token refreshed successfully');
            } catch (\Exception $e) {
                Log::error('❌ Token refresh failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } else {
            Log::info('✅ Access token is still valid');
        }

        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
            'LinkedIn-Version' => '202401',
            'X-Restli-Protocol-Version' => '2.0.0'
        ];

        Log::info('🔨 Building post body', [
            'post_type' => $linkedInPost->post_type,
            'has_image' => !empty($linkedInPost->image_url),
            'has_video' => !empty($linkedInPost->video_url),
            'has_carousel' => !empty($linkedInPost->carousel_images)
        ]);

        // Build post body based on type
        $post_body = $this->buildPostBodyV2($linkedInPost, $author, $access_token);

        Log::info('📤 Publishing post via LinkedIn API v2', [
            'post_id' => $linkedInPost->id,
            'post_type' => $linkedInPost->post_type,
            'author' => $author,
            'api_url' => $api_url,
            'post_body_keys' => array_keys($post_body),
            'content_preview' => substr($linkedInPost->content, 0, 100)
        ]);

        try {
            $httpResponse = Http::withHeaders($headers)->post($api_url, $post_body);
            
            Log::info('📥 LinkedIn API HTTP Response', [
                'status_code' => $httpResponse->status(),
                'headers' => $httpResponse->headers(),
                'body' => $httpResponse->body()
            ]);

            $response = $httpResponse->throw()->json();

            Log::info('✅ LinkedIn API response parsed', ['response' => $response]);

            return $response;
        } catch (\Exception $e) {
            Log::error('❌ LinkedIn API call failed', [
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Build post body for LinkedIn API v2
     */
    private function buildPostBodyV2($post, $author, $access_token)
    {
        $postBody = [
            "author" => $author,
            "commentary" => $post->content,
            "visibility" => "PUBLIC",
            "distribution" => [
                "feedDistribution" => "MAIN_FEED",
                "targetEntities" => [],
                "thirdPartyDistributionChannels" => []
            ],
            "lifecycleState" => "PUBLISHED",
            "isReshareDisabledByAuthor" => false
        ];

        // Add media based on post type
        if ($post->post_type === 'image' && $post->image_url) {
            Log::info('📸 Uploading image for post');
            $imageId = $this->uploadImageV2($post->image_url, $author, $access_token);
            
            $postBody['content'] = [
                "media" => [
                    "title" => substr($post->content, 0, 100),
                    "id" => $imageId
                ]
            ];
        } elseif ($post->post_type === 'carousel' && $post->carousel_images) {
            Log::info('🎠 Uploading carousel images');
            $postBody['content'] = $this->buildCarouselContentV2($post, $author, $access_token);
        } elseif ($post->post_type === 'video' && $post->video_url) {
            Log::info('🎥 Uploading video');
            $videoId = $this->uploadVideoV2($post->video_url, $author, $access_token);
            
            $postBody['content'] = [
                "media" => [
                    "title" => substr($post->content, 0, 100),
                    "id" => $videoId
                ]
            ];
        }
        // For text-only posts, no content block needed

        return $postBody;
    }

    /**
     * Upload image using new LinkedIn API v2
     */
    private function uploadImageV2($imageUrl, $author, $access_token)
    {
        // Step 1: Initialize upload
        $initResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
            'LinkedIn-Version' => '202401'
        ])->post('https://api.linkedin.com/rest/images?action=initializeUpload', [
            "initializeUploadRequest" => [
                "owner" => $author
            ]
        ])->throw()->json();

        $uploadUrl = $initResponse['value']['uploadUrl'];
        $imageId = $initResponse['value']['image'];

        Log::info('📤 Uploading image binary', ['imageId' => $imageId]);

        // Step 2: Upload image binary
        $imageContent = file_get_contents($imageUrl);
        
        Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
        ])->withBody($imageContent, 'application/octet-stream')
          ->put($uploadUrl)
          ->throw();

        Log::info('✅ Image uploaded successfully', ['imageId' => $imageId]);

        return $imageId;
    }

    /**
     * Upload video using new LinkedIn API v2
     */
    private function uploadVideoV2($videoUrl, $author, $access_token)
    {
        $videoContent = file_get_contents($videoUrl);
        
        // Step 1: Initialize upload
        $initResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
            'LinkedIn-Version' => '202401'
        ])->post('https://api.linkedin.com/rest/videos?action=initializeUpload', [
            "initializeUploadRequest" => [
                "owner" => $author,
                "fileSizeBytes" => strlen($videoContent),
                "uploadCaptions" => false,
                "uploadThumbnail" => false
            ]
        ])->throw()->json();

        $uploadUrl = $initResponse['value']['uploadUrl'];
        $videoId = $initResponse['value']['video'];

        Log::info('📤 Uploading video binary', ['videoId' => $videoId]);

        // Step 2: Upload video binary
        Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
        ])->withBody($videoContent, 'application/octet-stream')
          ->put($uploadUrl)
          ->throw();

        Log::info('✅ Video uploaded successfully', ['videoId' => $videoId]);

        return $videoId;
    }

    /**
     * Build carousel content for API v2
     */
    private function buildCarouselContentV2($post, $author, $access_token)
    {
        $images = [];
        
        foreach ($post->carousel_images as $index => $imageUrl) {
            $imageId = $this->uploadImageV2($imageUrl, $author, $access_token);
            
            $images[] = [
                "id" => $imageId,
                "altText" => "Slide " . ($index + 1)
            ];
        }

        return [
            "multiImage" => [
                "images" => $images
            ]
        ];
    }

    /**
     * Check if access token is expired
     */
    private function isTokenExpired($integration)
    {
        if (!$integration->expires_in || !$integration->updated_at) {
            return false;
        }

        $expiresAt = $integration->updated_at->addSeconds($integration->expires_in);
        return now()->greaterThan($expiresAt->subMinutes(5)); // Refresh 5 minutes before expiry
    }

    /**
     * 🔥 OLD METHOD - Keep for backward compatibility
     */
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