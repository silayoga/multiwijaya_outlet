@extends('layouts.app')

@section('title', 'Order '.$order->order_number)

@section('content')

@php
    $statusStyle = match ($order->status) {
        'pending_payment' => 'bg-amber-100 text-amber-700',
        'paid', 'completed' => 'bg-green-100 text-green-700',
        'processing' => 'bg-brand-blue/10 text-brand-blue',
        'cancelled' => 'bg-red-100 text-red-700',
        default => 'bg-surface-border text-slate-600',
    };
    $statusLabel = str_replace('_', ' ', $order->status);
    $latestPayment = $order->latestPayment();
@endphp

<section class="py-16 md:py-24">
  <div class="max-w-3xl mx-auto px-6 md:px-10">

    <div class="rounded-2xl border border-surface-border bg-white p-8 mb-8">
      <div class="flex flex-wrap items-start justify-between gap-4 mb-2">
        <div>
          <span class="block font-mono text-xs uppercase tracking-wide text-brand-blue mb-2">Order confirmed</span>
          <h1 class="font-display text-2xl md:text-3xl font-bold text-brand-ink tracking-tight">{{ $order->order_number }}</h1>
        </div>
        <span class="font-mono text-[10px] uppercase tracking-wide {{ $statusStyle }} px-3 py-1.5 rounded-full whitespace-nowrap">
          {{ $statusLabel }}
        </span>
      </div>
      <p class="text-sm text-slate-500">Placed {{ $order->created_at->format('d M Y, H:i') }}</p>
    </div>

    @if (session('error'))
      <div class="mb-8 rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-600">{{ session('error') }}</div>
    @endif

    @if ($order->status === 'paid')
      <div class="mb-8 rounded-lg border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-700">
        Payment received — thank you! We'll be in touch about delivery/installation next steps.
      </div>
    @elseif ($order->status === 'cancelled')
      <div class="mb-8 rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-600">
        This order was cancelled{{ $latestPayment && in_array($latestPayment->status, ['failed', 'expired', 'cancelled']) ? " — payment {$latestPayment->status}." : ' because payment wasn\'t completed in time.' }}
      </div>
    @elseif ($latestPayment?->status === 'pending')
      <div class="mb-8 rounded-lg border border-brand-cyan/30 bg-brand-cyan/10 px-5 py-4 text-sm text-brand-blue">
        <p class="mb-3">Waiting for payment confirmation. If you haven't paid yet, continue to the payment page below.</p>
        @if ($redirectUrl = $latestPayment->raw_response['redirect_url'] ?? null)
          <a href="{{ $redirectUrl }}" class="inline-block rounded-lg bg-brand-blue text-white px-5 py-2.5 text-sm font-semibold hover:opacity-90 transition-opacity">
            Continue to payment
          </a>
        @endif
      </div>
    @elseif ($order->status === 'pending_payment')
      <div class="mb-8 rounded-lg border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-700">
        <p class="mb-3">
          @if ($latestPayment)
            Your last payment attempt was not completed ({{ $latestPayment->status }}).
          @else
            Payment hasn't been started yet.
          @endif
        </p>
        <form method="POST" action="{{ route('orders.pay', $order) }}">
          @csrf
          <button type="submit" class="rounded-lg bg-brand-blue text-white px-5 py-2.5 text-sm font-semibold hover:opacity-90 transition-opacity cursor-pointer">
            {{ $latestPayment ? 'Retry payment' : 'Start payment' }}
          </button>
        </form>
      </div>
    @endif

    <div class="rounded-2xl border border-surface-border bg-white overflow-hidden mb-6">
      @foreach ($order->items as $item)
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

    <div class="flex items-center justify-between border-t border-surface-border pt-6">
      <span class="font-display font-semibold text-lg text-brand-ink">Total</span>
      <span class="font-mono text-lg font-semibold text-brand-ink">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
    </div>

    <a href="{{ route('catalog.index') }}" class="mt-10 block text-center rounded-lg border-2 border-surface-border text-brand-ink px-6 py-3.5 text-sm font-semibold hover:border-brand-blue transition-colors">
      Continue shopping
    </a>
  </div>
</section>

@endsection
