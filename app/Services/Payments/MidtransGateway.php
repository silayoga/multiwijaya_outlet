<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\Contracts\PaymentGatewayContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransGateway implements PaymentGatewayContract
{
    public function createTransaction(Order $order): PaymentTransactionResult
    {
        // Distinct id per attempt — an order can be paid-for more than once
        // (e.g. a prior attempt expired), and Midtrans requires a fresh
        // order_id per Snap transaction.
        $attemptNumber = $order->payments()->count() + 1;
        $gatewayOrderId = "{$order->order_number}-{$attemptNumber}";

        try {
            $response = Http::withBasicAuth(config('services.midtrans.server_key'), '')
                ->acceptJson()
                ->timeout(15)
                ->post($this->baseUrl().'/snap/v1/transactions', [
                    'transaction_details' => [
                        'order_id' => $gatewayOrderId,
                        'gross_amount' => (int) round((float) $order->total),
                    ],
                    'customer_details' => [
                        'first_name' => $order->user->name,
                        'email' => $order->user->email,
                        'phone' => $order->user->phone_number,
                    ],
                    'item_details' => $order->items->map(fn ($item) => [
                        'id' => (string) ($item->listing_id ?? $item->id),
                        'name' => Str::limit($item->listing_name_snapshot, 50, ''),
                        'quantity' => $item->quantity,
                        'price' => (int) round((float) $item->unit_price_snapshot),
                    ])->all(),
                    // No `enabled_payments` key — leaving it unset enables every
                    // payment method configured on the Midtrans account (VA,
                    // e-wallet, QRIS, card), per the task's requirement.
                ]);
        } catch (\Throwable $e) {
            Log::error('Midtrans createTransaction failed: transport error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Could not start payment.', previous: $e);
        }

        if (! $response->successful() || ! $response->json('redirect_url')) {
            Log::error('Midtrans createTransaction failed: API error', [
                'order_id' => $order->id,
                'http_status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Could not start payment.');
        }

        $body = $response->json();

        Payment::create([
            'order_id' => $order->id,
            'gateway' => 'midtrans',
            'gateway_reference' => $gatewayOrderId,
            'status' => 'pending',
            'amount' => $order->total,
            'raw_response' => $body,
        ]);

        return new PaymentTransactionResult(
            redirectUrl: $body['redirect_url'],
            gatewayReference: $gatewayOrderId,
            rawResponse: $body,
        );
    }

    public function handleWebhook(Request $request): void
    {
        $payload = $request->all();

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (! $orderId || $statusCode === null || $grossAmount === null || ! $signatureKey) {
            Log::warning('Midtrans webhook rejected: missing required fields', ['payload' => $payload]);
            abort(400, 'Invalid payload.');
        }

        // The only real trust boundary here — never act on a webhook payload
        // without this check passing first, regardless of what it claims.
        $expectedSignature = hash(
            'sha512',
            $orderId.$statusCode.$grossAmount.config('services.midtrans.server_key')
        );

        if (! hash_equals($expectedSignature, $signatureKey)) {
            Log::warning('Midtrans webhook rejected: signature mismatch', ['order_id' => $orderId]);
            abort(403, 'Invalid signature.');
        }

        $payment = Payment::where('gateway', 'midtrans')
            ->where('gateway_reference', $orderId)
            ->first();

        if (! $payment) {
            Log::warning('Midtrans webhook: no matching payment found', ['order_id' => $orderId]);
            abort(404, 'Unknown transaction.');
        }

        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        // "capture" (card payments) needs fraud_status checked separately —
        // a challenged transaction is NOT a completed payment, unlike settlement.
        $newStatus = match (true) {
            $transactionStatus === 'settlement' => 'paid',
            $transactionStatus === 'capture' && $fraudStatus === 'accept' => 'paid',
            $transactionStatus === 'capture' && $fraudStatus === 'challenge' => 'pending',
            $transactionStatus === 'expire' => 'expired',
            $transactionStatus === 'cancel' => 'cancelled',
            $transactionStatus === 'deny' => 'failed',
            $transactionStatus === 'pending' => 'pending',
            default => $payment->status,
        };

        $payment->update([
            'status' => $newStatus,
            'raw_response' => $payload,
        ]);

        $orderStatus = match ($newStatus) {
            'paid' => 'paid',
            'expired', 'cancelled', 'failed' => 'cancelled',
            default => $payment->order->status,
        };

        $payment->order->update(['status' => $orderStatus]);
    }

    private function baseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }
}
