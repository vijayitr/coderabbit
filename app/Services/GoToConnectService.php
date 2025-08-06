<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;


class GoToConnectService
{
    protected $client;
    protected $authUrl;
    protected $tokenUrl;
    protected $redirectUri;
    protected $apiUrl;

    public function __construct()
    {
        $this->client = new Client([
            'verify' => false, // ❌ Bypass SSL verification (optional)
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // ✅ Force IPv4
            ],
        ]);
        $this->authUrl = env('GOTO_AUTH_URL');
        $this->tokenUrl = env('GOTO_TOKEN_URL');
        $this->redirectUri = env('GOTO_REDIRECT_URI');
        $this->apiUrl = env('GOTO_API_URL');
    }

    /**
     * Generate OAuth Login URL
     */
    public function getAuthorizationUrl()
    {
        $queryParams = http_build_query([
            'client_id'     => env('GOTO_CLIENT_ID'),
            'response_type' => 'code',
            'redirect_uri'  => $this->redirectUri,
            ///'scope'         => 'read write',
            //'state'         => csrf_token(),
        ]);

        return $this->authUrl . '?' . $queryParams;
    }

    /**
     * Exchange Authorization Code for Access Token
     */
    public function getAccessToken($code)
{
    try {
        $response = $this->client->post($this->tokenUrl, [
            'headers' => [
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode(env('GOTO_CLIENT_ID') . ':' . env('GOTO_CLIENT_SECRET')),
                'Accept'        => 'application/json'
            ],
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $this->redirectUri,
                'client_id'     => env('GOTO_CLIENT_ID'),
                'client_secret' => env('GOTO_CLIENT_SECRET')
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        // ✅ Store token in Laravel session (or database if needed)
        session([
            'goto_access_token' => $data['access_token'],
            'goto_refresh_token' => $data['refresh_token'],
            'goto_token_expires' => now()->addSeconds($data['expires_in']),
        ]);

        return $data;
    } catch (\Exception $e) {
        return ['error' => 'Failed to get access token', 'message' => $e->getMessage()];
    }
}

    /**
     * Refresh Access Token
     */
    public function refreshAccessToken()
    {
        try {
            $refreshToken = Session::get('goto_refresh_token');

            $response = $this->client->post($this->tokenUrl, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode(env('GOTO_CLIENT_ID') . ':' . env('GOTO_CLIENT_SECRET')),
                    'Content-Type'  => 'application/x-www-form-urlencoded',
                    'Accept'        => 'application/json'
                ],
                'form_params' => [
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id'     => env('GOTO_CLIENT_ID'),
                    'client_secret' => env('GOTO_CLIENT_SECRET')
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            log::info("token",['data' => $data]);

            // Update token in session
            Session::put('goto_access_token', $data['access_token']);
            if (isset($data['refresh_token'])) {
                Session::put('goto_refresh_token', $data['refresh_token']);
            }  
            Session::put('goto_token_expires', now()->addSeconds($data['expires_in']));
            

            return $data;
        } catch (\Exception $e) {
            
            Log::error("Goto Error:Failed to refresh token " . $e->getMessage());

            return ['error' => 'Failed to refresh token', 'message' => $e->getMessage()];
        }
    }

    /**
     * Make an authenticated API request (Handles Authentication Automatically)
     */
    public function makeApiRequest($endpoint, $method = 'GET', $data = [])
    {

       
        // If no access token, redirect to login
        if (!Session::has('goto_access_token')) {
            return Redirect::to($this->getAuthorizationUrl());
        }
        
        // If token expired, refresh it
        if (now()->greaterThan(Session::get('goto_token_expires'))) {
            $this->refreshAccessToken();
        }

        try {
            $accessToken = Session::get('goto_access_token');

            $requestData=[
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => '*/*'
                ]
            ];

            if(count($data) > 0){
                $requestData['json'] = $data;
            }   

            // Check if the endpoint is for WebRTC
            $this->apiUrl = (strpos($endpoint, 'web-calls') !== false) ? env('GOTO_WEBRTC_API_URL') : env('GOTO_API_URL');

            $response = $this->client->request($method, $this->apiUrl . $endpoint,$requestData);

            return $response->getBody();
        } catch (\Exception $e) {

            Log::error("Goto Error: " . $e->getMessage()); 

            return ['error' => 'API request failed', 'message' => $e->getMessage()];
        }
    }
}
