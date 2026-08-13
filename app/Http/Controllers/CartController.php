<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Listing;
use App\Models\PricingPlan;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->resolveCart($request);
        $cart->load(['items.listing', 'items.pricingPlan']);

        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
            'pricing_plan_id' => ['required', 'integer', 'exists:pricing_plans,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $listing = Listing::findOrFail($validated['listing_id']);
        $plan = PricingPlan::findOrFail($validated['pricing_plan_id']);
        $quantity = $validated['quantity'] ?? 1;

        $cart = $this->resolveCart($request);

        $item = $cart->items()
            ->where('listing_id', $listing->id)
            ->where('pricing_plan_id', $plan->id)
            ->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'listing_id' => $listing->id,
                'pricing_plan_id' => $plan->id,
                'listing_name_snapshot' => $listing->name,
                'plan_name_snapshot' => $plan->plan_name,
                'unit_price_snapshot' => $plan->price,
                'billing_cycle_snapshot' => $plan->billing_cycle,
                'quantity' => $quantity,
            ]);
        }

        return redirect()->route('cart.index')->with('status', "{$listing->name} added to cart.");
    }

    public function remove(Request $request, CartItem $cartItem)
    {
        $cart = $this->resolveCart($request);
        abort_unless($cartItem->cart_id === $cart->id, 403);

        $cartItem->delete();

        return redirect()->route('cart.index')->with('status', 'Item removed from cart.');
    }

    public static function resolveCart(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::firstOrCreate(
                ['user_id' => $request->user()->id, 'status' => 'active'],
            );
        }

        $cartId = $request->session()->get('guest_cart_id');

        if ($cartId) {
            $cart = Cart::where('id', $cartId)
                ->whereNull('user_id')
                ->where('status', 'active')
                ->first();

            if ($cart) {
                return $cart;
            }
        }

        $cart = Cart::create([
            'session_token' => $request->session()->getId(),
            'status' => 'active',
        ]);

        $request->session()->put('guest_cart_id', $cart->id);

        return $cart;
    }
}
