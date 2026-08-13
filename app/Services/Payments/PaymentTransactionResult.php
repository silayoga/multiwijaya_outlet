<?php

namespace App\Services\Payments;

final class PaymentTransactionResult
{
    public function __construct(
        public readonly string $redirectUrl,
        public readonly string $gatewayReference,
        public readonly array $rawResponse = [],
    ) {
    }
}
