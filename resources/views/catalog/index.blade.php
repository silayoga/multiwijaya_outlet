@extends('layouts.app')

@section('title', 'Catalog')

@section('content')

<section class="py-24 md:py-36">
  <div class="max-w-7xl mx-auto px-6 md:px-10 grid md:grid-cols-2 gap-16 lg:gap-20 items-center">
    <div>
      <span class="inline-flex items-center gap-2 font-mono text-xs uppercase tracking-wide text-brand-blue bg-brand-blue/10 px-3 py-1.5 rounded-full mb-8">
        <span class="w-1.5 h-1.5 rounded-full bg-brand-cyan"></span>
        CV. Multi Wijaya · Gianyar, Bali
      </span>
      <h1 class="font-display text-5xl md:text-6xl lg:text-7xl font-bold text-brand-ink leading-[0.97] tracking-tight mb-7">
        One connected system —<br class="hidden md:block"> from the network to the till.
      </h1>
      <p class="text-slate-500 text-lg md:text-xl max-w-md mb-10 leading-relaxed">
        Routers, CCTV, and POS hardware alongside Restoflow, DriverLoka, and E-HIOS software — priced,
        carted, and checked out in one place instead of five different vendors.
      </p>
      <div class="flex flex-wrap gap-3">
        <a href="#hardware-categories" class="inline-flex items-center gap-2 rounded-lg bg-brand-blue text-white px-7 py-3.5 text-sm font-semibold hover:opacity-90 transition-opacity">
          Browse Hardware →
        </a>
        <a href="#software-categories" class="inline-flex items-center gap-2 rounded-lg border-2 border-brand-cyan text-brand-ink px-7 py-3.5 text-sm font-semibold hover:bg-brand-cyan/10 transition-colors">
          Browse Software
        </a>
      </div>
    </div>

    <div class="hidden md:block md:py-8 lg:py-12">
      @include('catalog.partials.hero-signature')
    </div>
  </div>
</section>

<section class="py-16 md:py-24 bg-white border-y border-surface-border">
  <div class="max-w-7xl mx-auto px-6 md:px-10">
    <div class="max-w-xl mb-12 md:mb-16">
      <span class="block font-mono text-xs uppercase tracking-wide text-brand-blue mb-3">Why Multi Wijaya</span>
      <h2 class="font-display text-3xl md:text-4xl font-bold text-brand-ink tracking-tight">Built around how Bali businesses actually buy IT.</h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-6">
      <div>
        <div class="w-11 h-11 rounded-xl bg-brand-blue/10 flex items-center justify-center mb-5">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1830C4" stroke-width="1.6"><path d="M12 21s-7-5.2-7-11a7 7 0 0 1 14 0c0 5.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
        </div>
        <h3 class="font-display font-semibold text-base text-brand-ink mb-2">On-site install &amp; support</h3>
        <p class="text-sm text-slate-500 leading-relaxed">Our technicians set up and support your hardware in person, anywhere across Bali.</p>
      </div>
      <div>
        <div class="w-11 h-11 rounded-xl bg-brand-blue/10 flex items-center justify-center mb-5">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1830C4" stroke-width="1.6"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
        </div>
        <h3 class="font-display font-semibold text-base text-brand-ink mb-2">One cart, two product lines</h3>
        <p class="text-sm text-slate-500 leading-relaxed">Hardware and software subscriptions check out together — no separate vendors or invoices.</p>
      </div>
      <div>
        <div class="w-11 h-11 rounded-xl bg-brand-blue/10 flex items-center justify-center mb-5">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1830C4" stroke-width="1.6"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        </div>
        <h3 class="font-display font-semibold text-base text-brand-ink mb-2">Direct line to the team</h3>
        <p class="text-sm text-slate-500 leading-relaxed">No call center — you talk to the people who actually build and maintain what you're using.</p>
      </div>
      <div>
        <div class="w-11 h-11 rounded-xl bg-brand-blue/10 flex items-center justify-center mb-5">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1830C4" stroke-width="1.6"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h3 class="font-display font-semibold text-base text-brand-ink mb-2">Sourced &amp; configured locally</h3>
        <p class="text-sm text-slate-500 leading-relaxed">Every device is sourced and configured in Bali before it ever reaches you.</p>
      </div>
    </div>
  </div>
</section>

<section class="py-20 md:py-28">
  <div class="max-w-7xl mx-auto px-6 md:px-10">
    <div class="flex flex-wrap justify-between items-end gap-4 mb-14">
      <div>
        <span class="block font-mono text-xs uppercase tracking-wide text-brand-blue mb-3">Catalog</span>
        <h2 class="font-display text-3xl md:text-4xl font-bold text-brand-ink tracking-tight">Two lines, one ecosystem</h2>
      </div>
      <p class="text-sm text-slate-500 max-w-sm">
        Every listing — physical or software — shares the same pricing engine, so a router and a subscription sit in the same cart.
      </p>
    </div>

    <div class="grid md:grid-cols-2 gap-8 lg:gap-10">
      <div id="hardware-categories" class="scroll-mt-24">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
          <h3 class="font-display font-semibold text-xl text-brand-ink">Hardware</h3>
          <span class="whitespace-nowrap font-mono text-[10px] uppercase tracking-wide text-brand-blue bg-brand-blue/10 px-2.5 py-1 rounded-full">Physical · one-time</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          @forelse ($hardwareCategories as $category)
            <a href="{{ route('catalog.show', $category) }}"
               class="rounded-xl border border-surface-border bg-white p-6 hover:border-brand-blue transition-colors focus-visible:border-brand-blue">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1830C4" stroke-width="1.6" class="mb-4"><rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M2 20h20"/></svg>
              <h4 class="font-display font-semibold text-sm text-brand-ink mb-1.5">{{ $category->name }}</h4>
              <p class="text-xs text-slate-500">{{ $category->listings->count() }} {{ Str::plural('listing', $category->listings->count()) }}</p>
            </a>
          @empty
            <p class="col-span-2 text-sm text-slate-500 py-4">No hardware categories yet.</p>
          @endforelse
        </div>
      </div>

      <div id="software-categories" class="scroll-mt-24">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
          <h3 class="font-display font-semibold text-xl text-brand-ink">Software &amp; Services</h3>
          <span class="whitespace-nowrap font-mono text-[10px] uppercase tracking-wide text-brand-blue bg-brand-cyan/20 px-2.5 py-1 rounded-full">Subscription · tiered</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          @forelse ($softwareCategories as $category)
            <a href="{{ $category->slug === 'software-services' ? route('catalog.software-services') : route('catalog.show', $category) }}"
               class="rounded-xl border border-surface-border bg-white p-6 hover:border-brand-blue transition-colors focus-visible:border-brand-blue">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1830C4" stroke-width="1.6" class="mb-4"><path d="M3 12h4l3 8 4-16 3 8h4"/></svg>
              <h4 class="font-display font-semibold text-sm text-brand-ink mb-1.5">{{ $category->name }}</h4>
              <p class="text-xs text-slate-500">{{ $category->listings->count() }} {{ Str::plural('listing', $category->listings->count()) }}</p>
            </a>
          @empty
            <p class="col-span-2 text-sm text-slate-500 py-4">No software categories yet.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</section>

<section id="featured" class="py-20 md:py-28">
  <div class="max-w-7xl mx-auto">
    <div class="px-6 md:px-10 flex flex-wrap justify-between items-end gap-4 mb-10">
      <div>
        <span class="block font-mono text-xs uppercase tracking-wide text-brand-blue mb-3">One cart, mixed order</span>
        <h2 class="font-display text-3xl md:text-4xl font-bold text-brand-ink tracking-tight">Featured this month</h2>
      </div>
      <p class="text-sm text-slate-500 max-w-sm">
        An example of how hardware and subscriptions appear side by side in the same catalog view.
      </p>
    </div>

    <div class="relative">
      <div class="carousel-scroll flex gap-4 overflow-x-auto px-6 md:px-10 pb-4 scroll-px-6 md:scroll-px-10">
        @forelse ($featured as $listing)
          <div class="flex-shrink-0 w-64">
            @include('catalog.partials.listing-card', ['listing' => $listing, 'showBadge' => false])
          </div>
        @empty
          <p class="text-sm text-slate-500 py-4">No featured listings yet.</p>
        @endforelse
      </div>
      {{-- Fades the trailing edge card so the horizontal scroll reads as "more here", not a hard clip --}}
      <div class="pointer-events-none absolute top-0 right-0 bottom-4 w-12 md:w-20 bg-gradient-to-l from-surface to-transparent"></div>
    </div>
  </div>
</section>

@endsection
