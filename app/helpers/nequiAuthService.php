<?php
namespace App\Helpers;

use GuzzleHttp\Client;

Class NequiAuthService{

    private $clientId;
    private $clientSecret;
    private $authUri;
    // private $apiKey;
    // private $paymentUri;

    public function __construct(){
        $this->clientId = env('NEQUI_CLIENT_ID');
        $this->clientSecret = env('NEQUI_CLIENT_SECRET');
        $this->authUri = env('NEQUI_AUTH_URI');
        // $this->paymentUri = env('NEQUI_PAYMENT_URI');
        // $this->apiKey = env('NEQUI_API_KEY');
    }

    public function tokenGen(){
        try {
            $client = new Client;

            $response = $client->request('POST', $this->authUri, [
                'headers' => [
                    'Authorization' => "Basic ".base64_encode($this->clientId . ':' . $this->clientSecret),
                    'Content-Type' => "application/x-www-form-urlencoded"
                ],
                'query' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $responseObject = json_decode($response->getBody());

            return $responseObject->access_token;
        } catch (\Throwable $th) {
            $response = [
                'code' => 400,
                'status' => 'error',
                'message' => $th->getMessage()
            ];

            return response()->json($response, $response['code']);
        }
    }
}
