<?php

namespace App\Services\Payment;

use App\Services\Payment\Gateways\BkashGateway;
use App\Services\Payment\Gateways\CashGateway;

class PaymentService
{
    protected array $gateways = [];

    public function __construct()
    {
        $this->gateways = [
            'bkash' => BkashGateway::class,
            'cash'  => CashGateway::class,
        ];
    }

    public function register(string $key, string $gatewayClass): void
    {
        $this->gateways[$key] = $gatewayClass;
    }

    public function supportedGateways(): array
    {
        return array_keys($this->gateways);
    }

    public function process(string $paymentType, array $data): array
    {
        if (!isset($this->gateways[$paymentType])) {
            throw new \Exception("Payment gateway [{$paymentType}] is not supported.");
        }

        $gateway = app($this->gateways[$paymentType]);

        if (!$gateway instanceof PaymentInterface) {
            throw new \Exception("Payment gateway [{$paymentType}] must implement PaymentInterface.");
        }

        return $gateway->pay($data);
    }
}
