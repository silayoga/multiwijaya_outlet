<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Services\Payments\MidtransSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $subscriptions = $request->user()->subscriptions()
            ->with(['listing', 'pricingPlan'])
            ->orderByDesc('created_at')
            ->get();

        return view('subscriptions.index', compact('subscriptions'));
    }

    public function checkout(Request $request)
    {
        $cart = CartController::resolveCart($request);

        // Direct checkout links from external sites (?plan=restoflow-basic)
        // pre-select that plan by replacing whatever else is in the cart —
        // a marketing "Subscribe to Basic" link should always land on Basic,
        // not on leftover state from an earlier browsing session.
        if ($planSlug = $request->query('plan')) {
            $plan = PricingPlan::with('listing')->where('slug', $planSlug)->first();

            abort_unless($plan && in_array($plan->billing_cycle, ['monthly', 'yearly'], true), 404);

            $cart->items()->delete();
            $cart->items()->create([
                'listing_id' => $plan->listing_id,
                'pricing_plan_id' => $plan->id,
                'listing_name_snapshot' => $plan->listing->name,
                'plan_name_snapshot' => $plan->plan_name,
                'unit_price_snapshot' => $plan->price,
                'billing_cycle_snapshot' => $plan->billing_cycle,
                'quantity' => 1,
            ]);
        }

        $cart->load('items');

        if (! $this->isPureSubscriptionCart($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart needs to contain only software subscription items to continue here.');
        }

        $tokenizeUrl = config('services.midtrans.is_production')
            ? 'https://api.midtrans.com/v2/token'
            : 'https://api.sandbox.midtrans.com/v2/token';

        return view('subscriptions.checkout', [
            'cart' => $cart,
            'trialEndsAt' => now()->addDays(7),
            'clientKey' => config('services.midtrans.client_key'),
            'tokenizeUrl' => $tokenizeUrl,
        ]);
    }

    public function store(Request $request, MidtransSubscriptionService $midtrans)
    {
        $validated = $request->validate([
            'token_id' => ['required', 'string'],
        ]);

        $user = $request->user();
        $cart = CartController::resolveCart($request);
        $cart->load('items');

        if (! $this->isPureSubscriptionCart($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart needs to contain only software subscription items to continue here.');
        }

        $trialEndsAt = now()->addDays(7);

        // One Subscription row (and one Midtrans /v1/subscriptions
        // registration) per cart item — Midtrans's Subscription API models
        // exactly one recurring schedule per call, so a cart with several
        // different plans becomes several independent subscriptions, all
        // billed against the same saved card token.
        foreach ($cart->items as $item) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'listing_id' => $item->listing_id,
                'pricing_plan_id' => $item->pricing_plan_id,
                'status' => 'trialing',
                'trial_ends_at' => $trialEndsAt,
                'current_period_start' => now(),
                'current_period_end' => $trialEndsAt,
                'saved_token_id' => $validated['token_id'],
            ]);

            try {
                $result = $midtrans->registerSubscription($subscription, $validated['token_id'], $trialEndsAt);
                $subscription->update(['gateway_subscription_id' => $result['id']]);
            } catch (\Throwable $e) {
                Log::error('Subscription checkout: registration failed, rolling back local record', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $subscription->delete();

                return redirect()->route('subscriptions.checkout')->with('error',
                    "Could not start your subscription for {$item->listing_name_snapshot}. ".
                    'Please check your card details and try again.'
                );
            }
        }

        $cart->items()->delete();
        $cart->update(['status' => 'converted']);

        return redirect()->route('subscriptions.index')
            ->with('status', 'Your 7-day free trial has started — no charge until it ends.');
    }

    private function isPureSubscriptionCart(Cart $cart): bool
    {
        if ($cart->items->isEmpty()) {
            return false;
        }

        return $cart->items->every(
            fn (CartItem $item) => in_array($item->billing_cycle_snapshot, ['monthly', 'yearly'], true)
        );
    }
}
