<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\PaymentInterface;

class CashGateway implements PaymentInterface
{
    public function pay(array $data): array
    {
        return [
            'gateway'      => 'cash',
            'redirect_url' => null,
        ];
    }
}
