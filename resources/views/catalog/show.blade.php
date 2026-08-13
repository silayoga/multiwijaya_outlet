@extends('layouts.app')

@section('title', $category->name)

@section('content')

<section class="pt-16 md:pt-20 pb-10">
  <div class="max-w-7xl mx-auto px-6 md:px-10">
    <div class="flex flex-wrap justify-between items-end gap-4">
      <div>
        <span class="block font-mono text-xs uppercase tracking-wide text-brand-blue mb-3">
          <a href="{{ route('catalog.index') }}" class="hover:underline">Catalog</a> / {{ $category->name }}
        </span>
        <h1 class="font-display text-3xl md:text-4xl font-bold text-brand-ink tracking-tight">{{ $category->name }}</h1>
      </div>
      <span class="font-mono text-[10px] uppercase tracking-wide text-brand-blue {{ $category->type_hint === 'hardware' ? 'bg-brand-blue/10' : 'bg-brand-cyan/20' }} px-2.5 py-1 rounded-full">
        @if ($category->type_hint === 'hardware')
          Physical · one-time
        @elseif ($category->type_hint === 'software_service')
          Subscription · tiered
        @else
          Mixed
        @endif
      </span>
    </div>
  </div>
</section>

<section class="pb-16 md:pb-24">
  <div class="max-w-7xl mx-auto px-6 md:px-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      @forelse ($category->listings as $listing)
        @include('catalog.partials.listing-card', ['listing' => $listing])
      @empty
        <p class="col-span-full text-sm text-slate-500 py-4">No listings in this category yet.</p>
      @endforelse
    </div>
  </div>
</section>

@endsection
