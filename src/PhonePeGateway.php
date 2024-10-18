<?php

namespace PhonePe;
class PhonePeGateway
{
    private mixed $merchantId;
    private mixed $merchantUserId;
    private string $baseUrl;
    private mixed $saltKey;
    private mixed $saltIndex;
    private mixed $callBackUrl;
    private Client $client;

    /**
     * @throws InvalidEnvironmentVariableException
     */
    public function __construct()
    {
        $this->merchantId = config('phonepe.merchantId');
        $this->merchantUserId = config('phonepe.merchantUserId');
        $this->baseUrl = config('phonepe.env') == 'production' ? 'https://api.phonepe.com/apis/hermes' : 'https://api-preprod.phonepe.com/apis/pg-sandbox';
        $this->saltKey = config('phonepe.saltKey');
        $this->saltIndex = config('phonepe.saltIndex');
        $this->callBackUrl = config('phonepe.callBackUrl');
        $this->checkEnvironment();
        $this->client = new Client();
    }

    /**
     * @throws InvalidEnvironmentVariableException
     */
    private function checkEnvironment(): void
    {
        if (empty($this->merchantId)) {
            throw new InvalidEnvironmentVariableException("Merchant Id is not added in .env file");
        }
        if (empty($this->merchantUserId)) {
            throw new InvalidEnvironmentVariableException("Merchant User Id is not added in .env file");
        }
        if (empty($this->saltKey)) {
            throw new InvalidEnvironmentVariableException("Salt Key is not added in .env file");
        }
        if (empty($this->saltIndex)) {
            throw new InvalidEnvironmentVariableException("Salt Index is not added in .env file");
        }
        if (empty($this->callBackUrl)) {
            throw new InvalidEnvironmentVariableException("Call Back Url is not added in .env file");
        }
    }

    /**
     * @throws PhonePeException
     */
    public function makePayment($amount, $redirectUrl, $merchantTransactionId, $phone, $email, $shortName, $message): string
    {
        $data = [
            'merchantId' => $this->merchantId,
            'merchantTransactionId' => $merchantTransactionId,
            'merchantUserId' => $this->merchantUserId,
            'amount' => $amount * 100, // Amount in paise
            'redirectUrl' => $redirectUrl,
            'redirectMode' => 'POST',
            'callbackUrl' => $this->callBackUrl,
            'mobileNumber' => $phone,
            'message' => $message,
            'email' => $email,
            'shortName' => $shortName,
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];
        $encodedData = base64_encode(json_encode($data));
        $stringToSign = $encodedData . '/pg/v1/pay' . $this->saltKey;
        $sha256 = hash('sha256', $stringToSign);
        $finalXHeader = $sha256 . '###' . $this->saltIndex;

        try {
            $response = $this->client->post($this->baseUrl . '/pg/v1/pay', [
                'json' => ['request' => $encodedData],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-VERIFY' => $finalXHeader,
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($responseData['success']) {
                return $responseData['data']['instrumentResponse']['redirectInfo']['url'];
            } else {
                throw new PhonePeException($responseData['message']);
            }
        } catch (GuzzleException $e) {
            dd($e->getMessage());
            Log::error('Payment initiation failed: ' . $e->getMessage());
            throw new PhonePeException('Payment initiation failed. Please try again.');
        }
    }

    public function getTransactionStatus(array $request): bool
    {
        $stringToSign = '/pg/v1/status/' . $request['merchantId'] . '/' . $request['transactionId'] . $this->saltKey;
        $finalXHeader = hash('sha256', $stringToSign) . '###' . $this->saltIndex;

        try {
            $response = $this->client->get($this->baseUrl . '/pg/v1/status/' . $request['merchantId'] . '/' . $request['transactionId'], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'accept' => 'application/json',
                    'X-VERIFY' => $finalXHeader,
                    'X-MERCHANT-ID' => $request['merchantId'],
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            return $responseData['success'];
        } catch (GuzzleException $e) {
            Log::error('Transaction status check failed: ' . $e->getMessage());
            return false;
        }
    }
}
