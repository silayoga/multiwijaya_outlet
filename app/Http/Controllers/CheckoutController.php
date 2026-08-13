<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Services\OrderNumberGenerator;
use App\Services\Payments\Contracts\PaymentGatewayContract;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout.index');
    }

    public function placeOrder(Request $request)
    {
        $user = $request->user();
        $cart = CartController::resolveCart($request);
        $cart->load('items');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty — add something before checking out.');
        }

        // Subscription checkout isn't built yet — see task scope. Block the
        // whole checkout rather than silently splitting the order, and name
        // the offending item so the buyer knows what to remove.
        $subscriptionItem = $cart->items->first(fn (CartItem $item) => $item->billing_cycle_snapshot !== 'one_time');

        if ($subscriptionItem) {
            return redirect()->route('cart.index')->with('error',
                "Software subscriptions require a different checkout process, coming soon. ".
                "Please complete your hardware purchase separately for now, or remove ".
                "{$subscriptionItem->listing_name_snapshot} from your cart to continue."
            );
        }

        $order = DB::transaction(function () use ($cart, $user) {
            $subtotal = $cart->items->sum(
                fn (CartItem $item) => (float) $item->unit_price_snapshot * $item->quantity
            );

            $order = $this->createOrderWithUniqueNumber($user->id, $subtotal);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'listing_id' => $item->listing_id,
                    'pricing_plan_id' => $item->pricing_plan_id,
                    'listing_name_snapshot' => $item->listing_name_snapshot,
                    'plan_name_snapshot' => $item->plan_name_snapshot,
                    'unit_price_snapshot' => $item->unit_price_snapshot,
                    'billing_cycle_snapshot' => $item->billing_cycle_snapshot,
                    'quantity' => $item->quantity,
                ]);
            }

            $cart->items()->delete();
            $cart->update(['status' => 'converted']);

            return $order;
        });

        // Deliberately outside the transaction above — an external HTTP call
        // has no business holding DB locks, and the order must exist either
        // way even if the gateway call below fails.
        try {
            $result = app(PaymentGatewayContract::class)->createTransaction($order);

            return redirect()->away($result->redirectUrl);
        } catch (\Throwable $e) {
            Log::error('Payment gateway createTransaction failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'Your order was placed, but starting payment failed. Please try again below.');
        }
    }

    /**
     * OrderNumberGenerator::generate() is safe under normal sequential use
     * (lockForUpdate serializes within the surrounding transaction), but the
     * very first order of a day has no existing row to lock — so a true
     * concurrent race there is still possible. Retry once on a unique
     * violation rather than letting a checkout fail outright for it.
     */
    private function createOrderWithUniqueNumber(int $userId, float $subtotal): Order
    {
        $attempts = 0;

        while (true) {
            $attempts++;

            try {
                return Order::create([
                    'order_number' => app(OrderNumberGenerator::class)->generate(),
                    'user_id' => $userId,
                    'status' => 'pending_payment',
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                ]);
            } catch (QueryException $e) {
                $isDuplicateOrderNumber = str_contains($e->getMessage(), 'orders_order_number_unique');

                if (! $isDuplicateOrderNumber || $attempts >= 3) {
                    throw $e;
                }
            }
        }
    }
}
