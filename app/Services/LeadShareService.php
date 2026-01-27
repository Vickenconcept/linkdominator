<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Log;

class LeadShareService
{
    public function leadShare($provider, $data)
    {
        Log::info('[BACKEND] LeadShareService: leadShare called', [
            'provider' => $provider,
            'email' => $data['email'] ?? 'N/A',
            'has_apikey' => !empty($data['apikey']),
            'has_listid' => !empty($data['listid']),
            'timestamp' => now()->toISOString()
        ]);
        
        if (!$data['apikey'] || !$data['listid']) {
            Log::warning('[BACKEND] LeadShareService: Missing required parameters', [
                'provider' => $provider,
                'has_apikey' => !empty($data['apikey']),
                'has_listid' => !empty($data['listid'])
            ]);
            return;
        }
        
        switch ($provider){
            case 'mailchimp':
                $datalist = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'mailchimpKey' => $data['apikey'],
                    'mailchimpList' => $data['listid']
                ];
                Log::info('[BACKEND] LeadShareService: Sharing with Mailchimp', [
                    'email' => $data['email'],
                    'list_id' => $data['listid']
                ]);
                $this->shareWithMailchimp($datalist);
                break;
            case 'getresponse':
                $datalist = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'apiKey' => $data['apikey'],
                    'campaignId' => $data['listid']
                ];
                Log::info('[BACKEND] LeadShareService: Sharing with GetResponse', [
                    'email' => $data['email'],
                    'campaign_id' => $data['listid']
                ]);
                $this->shareWithGetresponse($datalist);
                break;
            case 'emailoctopus':
                $datalist = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'apiKey' => $data['apikey'],
                    'listid' => $data['listid']
                ];
                Log::info('[BACKEND] LeadShareService: Sharing with EmailOctopus', [
                    'email' => $data['email'],
                    'list_id' => $data['listid']
                ]);
                $this->shareWithEmailOctopus($datalist);
                break;
            case 'converterkit':
                $datalist = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'apiKey' => $data['apikey'],
                    'formId' => $data['listid']
                ];
                Log::info('[BACKEND] LeadShareService: Sharing with ConverterKit', [
                    'email' => $data['email'],
                    'form_id' => $data['listid']
                ]);
                $this->shareWithConverterKit($datalist);
                break;
            case 'mailerlite':
                $datalist = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'apiKey' => $data['apikey'],
                    'groupId' => $data['listid']
                ];
                Log::info('[BACKEND] LeadShareService: Sharing with MailerLite', [
                    'email' => $data['email'],
                    'group_id' => $data['listid']
                ]);
                $this->shareWithMailerLite($datalist);
                break;
            default:
                Log::warning('[BACKEND] LeadShareService: Unknown provider', [
                    'provider' => $provider,
                    'email' => $data['email']
                ]);
                break;
        }
    }

    protected function shareWithMailchimp($data)
    {
        try {
            $apiKey = $data['mailchimpKey'] ?? '';
            
            // Log API key format info (without exposing full key)
            $keyLength = strlen($apiKey);
            $hasDash = strpos($apiKey, '-') !== false;
            $keyPreview = $keyLength > 0 ? substr($apiKey, 0, 10) . '...' : 'empty';
            
            Log::info('[BACKEND] LeadShareService: Mailchimp API key validation', [
                'key_length' => $keyLength,
                'has_dash' => $hasDash,
                'key_preview' => $keyPreview,
                'email' => $data['email']
            ]);
            
            $explode = explode('-', $apiKey);

            if(count($explode) < 2) {
                Log::error('[BACKEND] LeadShareService: Invalid Mailchimp API key format', [
                    'email' => $data['email'],
                    'key_length' => $keyLength,
                    'has_dash' => $hasDash,
                    'key_preview' => $keyPreview,
                    'expected_format' => 'apikey-datacenter (e.g., abc123-us21)'
                ]);
                return null;
            }

            $dc = $explode[1];
            // Use MD5 hash of email for PUT endpoint (Mailchimp's recommended way to handle duplicates)
            $emailHash = md5(strtolower($data['email']));
            $url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $data['mailchimpList'] . '/members/' . $emailHash;

            Log::info('[BACKEND] LeadShareService: Mailchimp API call', [
                'url' => $url,
                'email' => $data['email'],
                'list_id' => $data['mailchimpList'],
                'data_center' => $dc,
                'method' => 'PUT (upsert)'
            ]);

            /* Use PUT to insert or update - handles duplicates gracefully */
            $response = Http::withToken($data['mailchimpKey'])
                ->put($url, [
                    'email_address' => $data['email'],
                    'status' => 'subscribed',
                    'merge_fields' => [
                        'FNAME' => $data['name']
                    ],
                ]);

            $responseBody = json_decode($response->getBody());
            
            Log::info('[BACKEND] LeadShareService: Mailchimp response', [
                'email' => $data['email'],
                'status_code' => $response->status(),
                'response' => $responseBody,
                'success' => $response->successful()
            ]);

            return $responseBody;
        } catch (\Exception $e) {
            Log::error('[BACKEND] LeadShareService: Mailchimp error', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function shareWithGetresponse($data)
    {
        try {
            $url = 'https://api.getresponse.com/v3/contacts';
            
            Log::info('[BACKEND] LeadShareService: GetResponse API call', [
                'url' => $url,
                'email' => $data['email'],
                'campaign_id' => $data['campaignId']
            ]);
            
            $response = Http::withHeaders([
                "X-Auth-Token" => "api-key ".$data['apiKey']
            ])->post($url, [
                "name" => $data['name'],
                "email" => $data['email'],
                "campaign" => [
                    "campaignId" => $data["campaignId"]
                ]
            ]);

            $responseBody = json_decode($response->getBody());
            
            Log::info('[BACKEND] LeadShareService: GetResponse response', [
                'email' => $data['email'],
                'status_code' => $response->status(),
                'response' => $responseBody,
                'success' => $response->successful()
            ]);

            return $responseBody;
        } catch (\Exception $e) {
            Log::error('[BACKEND] LeadShareService: GetResponse error', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function shareWithEmailOctopus($data)
    {
        try {
            $url = 'https://emailoctopus.com/api/1.6/lists/'.$data['listid'].'/contacts';
            
            Log::info('[BACKEND] LeadShareService: EmailOctopus API call', [
                'url' => $url,
                'email' => $data['email'],
                'list_id' => $data['listid']
            ]);
            
            $response = Http::withHeaders([
                "Content-Type" => "application/json"
            ])->post($url, [ 
                "api_key" => $data['apiKey'],
                "email_address" => $data['email'],
                "fields" => [
                    "FirstName" => $data['name'],
                    "status" => "SUBSCRIBED"
                ]
            ]);

            $responseBody = json_decode($response->getBody());
            
            Log::info('[BACKEND] LeadShareService: EmailOctopus response', [
                'email' => $data['email'],
                'status_code' => $response->status(),
                'response' => $responseBody,
                'success' => $response->successful()
            ]);

            return $responseBody;
        } catch (\Exception $e) {
            Log::error('[BACKEND] LeadShareService: EmailOctopus error', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function shareWithConverterKit($data)
    {
        try {
            $url = 'https://api.convertkit.com/v3/forms/'.$data['formId'].'/subscribe';
            
            Log::info('[BACKEND] LeadShareService: ConverterKit API call', [
                'url' => $url,
                'email' => $data['email'],
                'form_id' => $data['formId']
            ]);
            
            $response = Http::withHeaders([
                "Content-Type" => "application/json; charset=utf-8"
            ])->post($url, [ 
                "api_key" => $data['apiKey'],
                "email" => $data['email'],
            ]);

            $responseBody = json_decode($response->getBody());
            
            Log::info('[BACKEND] LeadShareService: ConverterKit response', [
                'email' => $data['email'],
                'status_code' => $response->status(),
                'response' => $responseBody,
                'success' => $response->successful()
            ]);

            return $responseBody;
        } catch (\Exception $e) {
            Log::error('[BACKEND] LeadShareService: ConverterKit error', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function shareWithMailerLite($data)
    {
        try {
            $url = "https://connect.mailerlite.com/api/subscribers";
            
            Log::info('[BACKEND] LeadShareService: MailerLite API call', [
                'url' => $url,
                'email' => $data['email'],
                'group_id' => $data['groupId']
            ]);
            
            $response = Http::withHeaders([
                    "Content-Type" => "application/json",
                    "Accept" => "application/json",
                    "X-Version" => "2038-01-19"
                ])
                ->withToken($data['apiKey'])
                ->post($url, [
                    "email" => $data["email"],
                    "fields" => [
                        "name" => $data["name"],
                        "groups" => [
                            $data["groupId"]
                        ]
                    ],
                ]);

            $responseBody = json_decode($response->getBody());
            
            Log::info('[BACKEND] LeadShareService: MailerLite response', [
                'email' => $data['email'],
                'status_code' => $response->status(),
                'response' => $responseBody,
                'success' => $response->successful()
            ]);

            return $responseBody;
        } catch (\Exception $e) {
            Log::error('[BACKEND] LeadShareService: MailerLite error', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function shareWithWebhook($data)
    {
        $response = Http::asForm()->post($data['webhook'], [
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

        $responseBody = json_decode($response->getBody());
        Log::info($response->getBody());

        return $responseBody;
    }
}