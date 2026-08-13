<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Payments\Contracts\PaymentGatewayContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function show(Request $request, Order $order)
    {
        // order_number isn't guessable in practice, but that's not a
        // substitute for a real ownership check.
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['items', 'payments']);

        return view('orders.show', compact('order'));
    }

    /**
     * Start a fresh payment attempt for an order still awaiting payment —
     * covers both "the initial gateway call failed" and "a prior attempt
     * expired, buyer wants to try again."
     */
    public function pay(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status !== 'pending_payment') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'This order is no longer awaiting payment.');
        }

        try {
            $result = app(PaymentGatewayContract::class)->createTransaction($order);

            return redirect()->away($result->redirectUrl);
        } catch (\Throwable $e) {
            Log::error('Payment gateway createTransaction failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'Could not start payment. Please try again shortly.');
        }
    }
}
