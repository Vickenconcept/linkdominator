<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Log;

class LeadShareService
{
    public function leadShare($provider, $data)
    {
        if ($data['apikey'] && $data['listid']){
            switch ($provider){
                case 'mailchimp':
                    $datalist = [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'mailchimpKey' => $data['apikey'],
                        'mailchimpList' => $data['listid']
                    ];
                    $this->shareWithMailchimp($datalist);
                    break;
                case 'getresponse':
                    $datalist = [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'apiKey' => $data['apikey'],
                        'campaignId' => $data['listid']
                    ];
                    $this->shareWithGetresponse($datalist);
                    break;
                case 'emailoctopus':
                    $datalist = [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'apiKey' => $data['apikey'],
                        'listid' => $data['listid']
                    ];
                    $this->shareWithEmailOctopus($datalist);
                    break;
                case 'converterkit':
                    $datalist = [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'apiKey' => $data['apikey'],
                        'formId' => $data['listid']
                    ];
                    $this->shareWithConverterKit($datalist);
                    break;
                case 'mailerlite':
                    $datalist = [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'apiKey' => $data['apikey'],
                        'groupId' => $data['listid']
                    ];
                    $this->shareWithMailerLite($datalist);
                    break;
            }
        }
    }

    protected function shareWithMailchimp($data)
    {
        $explode = explode('-', $data['mailchimpKey']);

        if(count($explode) < 2) {
            die();
        }

        $dc = $explode[1];
        $url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $data['mailchimpList'] . '/members';

        /* Try to subscribe the user to mailchimp list */
        $response = Http::withToken($data['mailchimpKey'])
            ->post($url, [
                'email_address' => $data['email'],
                'status' => 'subscribed',
                'merge_fields' => [
                    'FNAME' => $data['name']
                ],
            ]);

        $responseBody = json_decode($response->getBody());
        Log::info($response->getBody());

        return $responseBody;
    }

    protected function shareWithGetresponse($data)
    {
        $response = Http::withHeaders([
            "X-Auth-Token" => "api-key ".$data['apiKey']
        ])->post('https://api.getresponse.com/v3/contacts', [ 
            "body" => [
                "name" => $data['name'],
                "email" => $data['email'],
                "campaign" => [
                    "campaignId" => $data["campaignId"]
                ]
            ]
        ]);

        $responseBody = json_decode($response->getBody());
        Log::info($response->getBody());

        return $responseBody;
    }

    protected function shareWithEmailOctopus($data)
    {
        $response = Http::withHeaders([
            "Content-Type" => "application/json"
        ])->post('https://emailoctopus.com/api/1.6/lists/'.$data['listId'].'/contacts', [ 
            "api_key" => $data['apiKey'],
            "email_address" => $data['email'],
            "fields" => [
                "FirstName" => $data['name'],
                "status" => "SUBSCRIBED"
            ]
        ]);

        $responseBody = json_decode($response->getBody());
        Log::info($response->getBody());

        return $responseBody;
    }

    protected function shareWithConverterKit($data)
    {
        $response = Http::withHeaders([
            "Content-Type" => "application/json; charset=utf-8"
        ])->post('https://api.convertkit.com/v3/forms/'.$data['formId'].'/subscribe', [ 
            "api_key" => $data['apiKey'],
            "email" => $data['email'],
        ]);

        $responseBody = json_decode($response->getBody());
        Log::info($response->getBody());

        return $responseBody;
    }

    protected function shareWithMailerLite($data)
    {
        $response = Http::withHeaders([
                "Content-Type" => "application/json",
                "Accept" => "application/json",
                "X-Version" => "2038-01-19"
            ])
            ->withToken($data['apiKey'])
            ->post("https://connect.mailerlite.com/api/subscribers", [
                "email" => $data["email"],
                "fields" => [
                    "name" => $data["name"],
                    "groups" => [
                        $data["groupId"]
                    ]
                ],
            ]);

        $responseBody = json_decode($response->getBody());
        Log::info($response->getBody());

        return $responseBody;
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