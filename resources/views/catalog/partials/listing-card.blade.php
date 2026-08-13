@php
    $plan = $listing->defaultPlan->first();
    $isPhysical = $listing->isPhysical();
    $typeLabel = $isPhysical ? 'Physical' : 'Subscription';
    $cycleLabel = match ($plan?->billing_cycle) {
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'one_time' => 'One-time',
        'custom_quote' => 'Custom quote',
        default => null,
    };
    // Skip the badge where every card in the list is already known-featured
    // (e.g. a "Featured this month" rail) — showing it on every single card
    // makes it noise instead of a signal.
    $showBadge = $showBadge ?? true;
@endphp
<a href="{{ route('listings.show', $listing) }}"
   class="w-full rounded-2xl border border-surface-border bg-white overflow-hidden block focus-visible:border-brand-blue">
  <div class="relative h-36 flex items-center justify-center text-center px-4 font-display font-semibold text-sm text-white {{ $isPhysical ? 'bg-gradient-to-br from-brand-blue to-brand-ink' : 'bg-gradient-to-br from-brand-cyan to-brand-blue' }}">
    @if ($showBadge && $listing->isPopular())
      <span class="absolute top-3 left-3 rounded-full bg-brand-blue px-2.5 py-1 font-mono text-[10px] uppercase tracking-wide text-white shadow-sm">Populer</span>
    @elseif ($showBadge && $listing->isNew())
      <span class="absolute top-3 left-3 rounded-full bg-brand-cyan px-2.5 py-1 font-mono text-[10px] uppercase tracking-wide text-brand-ink shadow-sm">New</span>
    @endif
    @if ($listing->primaryImage())
      <img src="{{ $listing->primaryImage() }}" alt="{{ $listing->name }}" class="w-full h-full object-cover">
    @else
      {{ $listing->category->name ?? $typeLabel }}
    @endif
  </div>
  <div class="p-4">
    <span class="block mb-1.5 font-mono text-[10px] uppercase tracking-wide text-slate-500">
      {{ $typeLabel }}{{ $cycleLabel ? ' · '.$cycleLabel : '' }}
    </span>
    <h4 class="font-display font-semibold text-sm text-brand-ink mb-3 min-h-[2.25rem]">{{ $listing->name }}</h4>
    <span class="font-mono text-sm font-medium text-brand-ink">{{ $plan?->formattedPrice() ?? 'Contact for price' }}</span>
  </div>
</a>
