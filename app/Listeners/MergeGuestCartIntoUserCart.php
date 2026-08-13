<?php

namespace App\Listeners;

use App\Models\Cart;
use Illuminate\Auth\Events\Login;

class MergeGuestCartIntoUserCart
{
    /**
     * Laravel regenerates the session ID on login (session-fixation
     * protection), so the guest cart's session_token no longer matches
     * session()->getId() by the time this fires. Session *data* survives
     * that regeneration though, so CartController stashes the guest cart's
     * own id in the session — that's what we look up here instead.
     */
    public function handle(Login $event): void
    {
        $guestCartId = session('guest_cart_id');

        if (! $guestCartId) {
            return;
        }

        $guestCart = Cart::where('id', $guestCartId)
            ->whereNull('user_id')
            ->where('status', 'active')
            ->with('items')
            ->first();

        session()->forget('guest_cart_id');

        if (! $guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = Cart::firstOrCreate(
            ['user_id' => $event->user->id, 'status' => 'active'],
        );

        foreach ($guestCart->items as $guestItem) {
            $existing = $userCart->items()
                ->where('listing_id', $guestItem->listing_id)
                ->where('pricing_plan_id', $guestItem->pricing_plan_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $guestItem->quantity);
                $guestItem->delete();
            } else {
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->delete();
    }
}
