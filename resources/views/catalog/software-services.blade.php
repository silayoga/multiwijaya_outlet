@extends('layouts.app')

@section('title', 'Software & Services')

@section('content')

<section class="bg-gradient-to-br from-brand-ink via-brand-blue to-brand-cyan">
  <div class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-32">
    <span class="inline-flex items-center gap-2 font-mono text-xs uppercase tracking-wide text-white bg-white/10 px-3 py-1.5 rounded-full mb-8">
      <span class="w-1.5 h-1.5 rounded-full bg-brand-cyan"></span>
      Software &amp; Services
    </span>
    <h1 class="font-display text-4xl md:text-6xl font-bold text-white leading-[1.02] tracking-tight mb-6 max-w-3xl">
      Subscription software for hospitality &amp; F&amp;B.
    </h1>
    <p class="text-white/70 text-lg md:text-xl max-w-xl leading-relaxed">
      Restoflow, DriverLoka, and E-HIOS run on a monthly, yearly, or custom-quoted plan — not a one-time
      purchase. Support and updates continue for as long as you subscribe, and it all sits in the same
      cart as your hardware.
    </p>
  </div>
</section>

<section class="py-20 md:py-28">
  <div class="max-w-5xl mx-auto px-6 md:px-10">
    <div class="flex flex-wrap justify-between items-end gap-4 mb-12">
      <div>
        <span class="block font-mono text-xs uppercase tracking-wide text-brand-blue mb-3">
          <a href="{{ route('catalog.index') }}" class="hover:underline">Catalog</a> / Software &amp; Services
        </span>
        <h2 class="font-display text-2xl md:text-3xl font-bold text-brand-ink tracking-tight">Available plans</h2>
      </div>
    </div>

    @php
      $allListings = $categories->flatMap->listings;
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      @forelse ($allListings as $listing)
        @php
          $plan = $listing->defaultPlan->first();
          $cycleLabel = match ($plan?->billing_cycle) {
              'monthly' => 'Monthly',
              'yearly' => 'Yearly',
              'one_time' => 'One-time',
              'custom_quote' => 'Custom quote',
              default => null,
          };
        @endphp
        <a href="{{ route('listings.show', $listing) }}"
           class="rounded-2xl border border-surface-border bg-white p-7 hover:border-brand-blue transition-colors focus-visible:border-brand-blue">
          <div class="flex items-start justify-between gap-4 mb-4">
            <span class="font-mono text-[10px] uppercase tracking-wide text-brand-blue bg-brand-cyan/20 px-2.5 py-1 rounded-full">
              Subscription{{ $cycleLabel ? ' · '.$cycleLabel : '' }}
            </span>
            @if ($listing->isPopular())
              <span class="font-mono text-[10px] uppercase tracking-wide text-white bg-brand-blue px-2.5 py-1 rounded-full">Populer</span>
            @elseif ($listing->isNew())
              <span class="font-mono text-[10px] uppercase tracking-wide text-brand-ink bg-brand-cyan px-2.5 py-1 rounded-full">New</span>
            @endif
          </div>
          <h3 class="font-display font-semibold text-xl text-brand-ink mb-2">{{ $listing->name }}</h3>
          <p class="text-sm text-slate-500 leading-relaxed mb-6">{{ $listing->short_description }}</p>
          <span class="font-mono text-base font-medium text-brand-ink">{{ $plan?->formattedPrice() ?? 'Contact for price' }}</span>
        </a>
      @empty
        <p class="col-span-full text-sm text-slate-500 py-4">No software listings yet.</p>
      @endforelse
    </div>
  </div>
</section>

@endsection
