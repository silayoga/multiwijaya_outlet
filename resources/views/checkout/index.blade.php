@extends('layouts.app')

@section('title', 'Checkout')

@section('content')

<section class="py-16 md:py-24">
  <div class="max-w-3xl mx-auto px-6 md:px-10">
    <h1 class="font-display text-3xl md:text-4xl font-bold text-brand-ink tracking-tight mb-3">Checkout</h1>
    <p class="text-sm text-slate-500 mb-8">
      Review your cart, then place your order. Payment integration is coming soon — orders are
      recorded as pending payment for now, and we'll follow up on how to complete payment.
    </p>

    @if (session('error'))
      <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">{{ session('error') }}</div>
    @endif
    @if (session('status'))
      <div class="mb-6 rounded-lg border border-brand-cyan/30 bg-brand-cyan/10 px-4 py-3 text-sm text-brand-blue">{{ session('status') }}</div>
    @endif

    @php
      $cart = \App\Http\Controllers\CartController::resolveCart(request());
    @endphp

    @if ($cart->items->isEmpty())
      <p class="text-sm text-slate-500 py-8 text-center">Your cart is empty. <a href="{{ route('catalog.index') }}" class="text-brand-blue font-semibold hover:underline">Browse the catalog</a>.</p>
    @else
      <div class="rounded-2xl border border-surface-border bg-white overflow-hidden mb-6">
        @foreach ($cart->items as $item)
          <div class="flex items-center justify-between gap-4 p-5 {{ ! $loop->last ? 'border-b border-surface-border' : '' }}">
            <div>
              <span class="block font-mono text-[10px] uppercase tracking-wide text-slate-500 mb-1">
                {{ $item->plan_name_snapshot }} · {{ str_replace('_', ' ', $item->billing_cycle_snapshot) }}
              </span>
              <h3 class="font-display font-semibold text-base text-brand-ink">{{ $item->listing_name_snapshot }}</h3>
              <p class="text-sm text-slate-500 mt-1">Qty {{ $item->quantity }}</p>
            </div>
            <span class="font-mono text-sm font-medium text-brand-ink">
              {{ $item->unit_price_snapshot !== null ? 'Rp '.number_format((float) $item->lineTotal(), 0, ',', '.') : 'Custom quote' }}
            </span>
          </div>
        @endforeach
      </div>

      <div class="flex items-center justify-between border-t border-surface-border pt-6 mb-8">
        <span class="font-display font-semibold text-lg text-brand-ink">Subtotal</span>
        <span class="font-mono text-lg font-semibold text-brand-ink">Rp {{ number_format($cart->subtotal(), 0, ',', '.') }}</span>
      </div>

      <form method="POST" action="{{ route('checkout.place-order') }}">
        @csrf
        <button type="submit" class="w-full rounded-lg bg-brand-blue text-white px-6 py-3.5 text-sm font-semibold hover:opacity-90 transition-opacity cursor-pointer">
          Place order
        </button>
      </form>
    @endif
  </div>
</section>

@endsection
