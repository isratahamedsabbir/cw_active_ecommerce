<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\PaymentInterface;

class BkashGateway implements PaymentInterface
{
    private string $base_url;

    public function __construct()
    {
        if (get_setting('bkash_sandbox', 1)) {
            $this->base_url = "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/";
        } else {
            $this->base_url = "https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/";
        }
    }

    public function pay(array $data): array
    {
        $token = $this->getToken();

        $payerReference = 'cart_payment-' . $data['combined_order_id'] . '-' . $data['user_id'];

        $requestbody = [
            'mode' => '0011',
            'payerReference' => $payerReference,
            'callbackURL' => route('api.bkash.callback'),
            'amount' => $data['amount'],
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => "Inv" . Date('YmdH') . rand(1000, 10000),
        ];

        $header = [
            'Content-Type:application/json',
            'Authorization:' . $token,
            'X-APP-Key:' . env('BKASH_CHECKOUT_APP_KEY'),
        ];

        $url = curl_init($this->base_url . 'checkout/create');
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, json_encode($requestbody));
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);

        $response = json_decode($resultdata);

        return [
            'gateway'      => 'bkash',
            'redirect_url' => $response->bkashURL ?? null,
            'payment_id'   => $response->paymentID ?? null,
        ];
    }

    private function getToken(): string
    {
        $request_data = [
            'app_key'    => env('BKASH_CHECKOUT_APP_KEY'),
            'app_secret' => env('BKASH_CHECKOUT_APP_SECRET'),
        ];

        $header = [
            'Content-Type:application/json',
            'username:' . env('BKASH_CHECKOUT_USER_NAME'),
            'password:' . env('BKASH_CHECKOUT_PASSWORD'),
        ];

        $url = curl_init($this->base_url . 'checkout/token/grant');
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, json_encode($request_data));
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);

        return json_decode($resultdata)->id_token;
    }
}
