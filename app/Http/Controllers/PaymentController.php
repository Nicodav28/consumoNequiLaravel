<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Helpers\NequiAuthService;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PaymentController extends Controller
{
    private $apiKey;
    private $paymentUri;
    private $token;
    private $getStatus;
    private $cancelTransact;

    public function __construct()
    {
        $this->token = new NequiAuthService;
        $this->paymentUri = env('NEQUI_PAYMENT_URI');
        $this->apiKey = env('NEQUI_API_KEY');
        $this->getStatus = env('NEQUI_GET_STATUS_URI');
        $this->cancelTransact = env('NEQUI_CANCEL_TRANSACT_URI');
    }

    public function PaymentCreate(Request $req)
    {
        $client = new Client;
        $data = $req->json()->all();

        $uniqId = $this->uniqueCode(9);

        $validator = Validator::make($data, [
            'phoneNumber' => 'required',
            'paymentValue' => 'required'
        ]);

        if (!$validator->fails()) {
            $response = $client->request('POST', $this->paymentUri, [
                'headers' => [
                    'Authorization' => (string) $this->token->tokenGen(),
                    'x-api-key' => $this->apiKey
                ],
                'json' => [
                    'RequestMessage' => [
                        'RequestHeader' => [
                            'Channel' => 'PNP04-C001',
                            'RequestDate' => date("d-m-YTH:i:s"),
                            'MessageID' => $uniqId,
                            'ClientID' => 'ClaroTest',
                            'Destination' => [
                                'ServiceName' => 'PaymentsService',
                                'ServiceOperation' => 'unregisteredPayment',
                                'ServiceRegion' => 'C001',
                                'ServiceVersion' => '1.2.0',
                            ],
                        ],
                        'RequestBody' => [
                            'any' => [
                                'unregisteredPaymentRQ' => [
                                    'phoneNumber' => $data['phoneNumber'],
                                    'code' => 'NIT_1',
                                    'value' => $data['paymentValue'],
                                    'reference1' => 'reference1',
                                    'reference2' => 'reference2',
                                    'reference3' => 'reference3',
                                ],
                            ],
                        ],
                    ],
                ]
            ]);

            $nequiResponse = json_decode($response->getBody());
            $responseCode = $nequiResponse->ResponseMessage->ResponseHeader->Status->StatusCode;
            $responseDesc = $nequiResponse->ResponseMessage->ResponseHeader->Status->StatusDesc;

            $response = [
                'code' => 200,
                'status' => 'Success',
                'message' => $responseDesc,
                'response' => json_decode($response->getBody(), true)
            ];
        } else {
            $response = [
                'code' => 400,
                'status' => 'Error',
                'message' => 'Invalid parameters',
                'traceCode' => 'L401',
                'validatorResponse' => $validator->errors(),
            ];
        }

        return response()->json($response, $response['code']);
    }

    public function getPaymentStatus(Request $req)
    {
        try {
            $client = new Client;

            $data = $req->json()->all();
            $uniqId = $this->uniqueCode(9);

            $validator = Validator::make($data, [
                'transactionId' => 'required'
            ]);

            if (!$validator->fails()) {

                $response = $client->request(
                    'POST',
                    $this->getStatus,
                    [
                        'headers' => [
                            'Authorization' => (string) $this->token->tokenGen(),
                            'x-api-key' => $this->apiKey
                        ],
                        'json' => [
                            'RequestMessage' => [
                                'RequestHeader' => [
                                    'Channel' => 'PNP04-C001',
                                    'RequestDate' => date("d-m-YTH:i:s"),
                                    'MessageID' => $uniqId,
                                    'ClientID' => 'ClaroTest',
                                    'Destination' => [
                                        'ServiceName' => 'PaymentsService',
                                        'ServiceOperation' => 'unregisteredPayment',
                                        'ServiceRegion' => 'C001',
                                        'ServiceVersion' => '1.2.0',
                                    ],
                                ],
                                'RequestBody' => [
                                    'any' => [
                                        'getStatusPaymentRQ' => [
                                            'codeQR' => $data['transactionId']
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ],
                );


                $response = [
                    'code' => 200,
                    'status' => 'Success',
                    'response' => json_decode($response->getBody(), true)
                ];
            } else {
                $response = [
                    'code' => 400,
                    'status' => 'Error',
                    'message' => 'Invalid parameters',
                    'traceCode' => 'L404',
                    'validatorResponse' => $validator->errors(),
                ];
            }
        } catch (\Throwable $er) {
            // dd($er);
            $response = [
                'code' => 401,
                'status' => 'Error',
                'message' => 'There was an error processing the petition.',
                'traceCode' => 'L401',
                'error' => $er->getTrace()
            ];
        }

        return response()->json($response, $response['code']);
    }

    public function cancelTransact(Request $req)
    {
        $client = new Client;

        $data = $req->json()->all();
        $uniqId = $this->uniqueCode(9);

        $validator = Validator::make($data, [
            'phoneNumber' => 'required',
            'transactionId' => 'required'
        ]);

        if (!$validator->fails()) {
            $response = $client->request(
                'POST',
                $this->cancelTransact,
                [
                    'headers' => [
                        'Authorization' => (string) $this->token->tokenGen(),
                        'x-api-key' => $this->apiKey
                    ],
                    'json' => [
                        "RequestMessage" => [
                            "RequestHeader" => [
                                "Channel" => "PNP04-C001",
                                "RequestDate" => date("d-m-YTH:i:s"),
                                "MessageID" => $uniqId,
                                "ClientID" => "ClaroTest",
                                "Destination" => [
                                    "ServiceName" => "PaymentsService",
                                    "ServiceOperation" => "unregisteredPayment",
                                    "ServiceRegion" => "C001",
                                    "ServiceVersion" => '1.0.0',
                                ],
                            ],
                            "RequestBody" => [
                                "any" => [
                                    "cancelUnregisteredPaymentRQ" => [
                                        "code" => "1",
                                        "phoneNumber" => $data['phoneNumber'],
                                        "transactionId" => $data['transactionId']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            );

            $response = [
                'code' => 200,
                'status' => 'Success',
                'response' => json_decode($response->getBody(), true)
            ];
        }else{
            $response = [
                'code' => 400,
                'status' => 'Error',
                'message' => 'Invalid parameters',
                'traceCode' => 'L404',
                'validatorResponse' => $validator->errors(),
            ];
        }

        return response()->json($response, $response['code']);
    }

    public function uniqueCode($limit)
    {
        return substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
    }
}
