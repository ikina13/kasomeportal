<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DpoService
{
    protected string $companyToken;
    protected string $apiUrl;

    public function __construct()
    {
        $this->companyToken = "55B69320-7B2D-451F-9846-4790DA901616";
        $this->apiUrl = "https://secure.3gdirectpay.com/API/v6/";

        if (!$this->companyToken || !$this->apiUrl) {
            throw new \Exception('DPO API credentials are not configured in config/services.php');
        }
    }

    /**
     * Verifies a transaction token with the DPO API.
     *
     * @param string $transactionToken The token to verify.
     * @return bool True if the payment is successful, false otherwise.
     */
    public function verifyToken(string $transactionToken): bool
    {
        $xmlData = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <API3G>
          <CompanyToken>{$this->companyToken}</CompanyToken>
          <Request>verifyToken</Request>
          <TransactionToken>{$transactionToken}</TransactionToken>
        </API3G>
        XML;

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/xml'])
                ->withBody($xmlData, 'application/xml')
                ->post($this->apiUrl);

            if ($response->successful()) {
                $xmlResponse = simplexml_load_string($response->body());
                
                // UPDATED CHECK:
                // A payment is only successful if the Result is '000' AND MobilePaymentRequest is 'Paid'.
                $isPaid = isset($xmlResponse->Result) && $xmlResponse->Result == '000'
                          && isset($xmlResponse->MobilePaymentRequest) && $xmlResponse->MobilePaymentRequest == 'Paid';
                
                return $isPaid;
            }

            // If the HTTP call itself fails, log it and return false
            Log::error('DPO API HTTP Error: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('DPO Service Exception: ' . $e->getMessage());
            return false; // Any exception means verification failed
        }
    }
}
