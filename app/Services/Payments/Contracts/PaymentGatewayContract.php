<?php

namespace App\Services\Payments\Contracts;

use App\Models\Order;
use App\Services\Payments\PaymentTransactionResult;
use Illuminate\Http\Request;

interface PaymentGatewayContract
{
    /**
     * Start a new payment attempt for the order and record it as a Payment
     * row. Throws on failure — callers must not treat a thrown exception as
     * success.
     */
    public function createTransaction(Order $order): PaymentTransactionResult;

    /**
     * Verify and process an incoming webhook notification from the gateway.
     * Implementations MUST verify the request's authenticity before making
     * any Payment/Order state changes — this is the only trust boundary
     * between "the internet" and "a real payment happened."
     */
    public function handleWebhook(Request $request): void;
}
