<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Multi Wijaya') — Hardware & Software, One Ecosystem</title>
<meta name="application-name" content="Multi Wijaya">
<meta name="apple-mobile-web-app-title" content="Multi Wijaya">
@php($faviconVersion = file_exists(public_path('images/favicon-32.png')) ? filemtime(public_path('images/favicon-32.png')) : 1)
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicon-180.png') }}?v={{ $faviconVersion }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-body text-brand-ink bg-surface antialiased">

<header class="sticky top-0 z-50 bg-white border-b border-surface-border">
  <div class="max-w-7xl mx-auto px-6 md:px-10">
    <div class="flex items-center justify-between h-20">

      <a href="{{ route('catalog.index') }}" class="flex items-center gap-2.5 py-4 -my-4 pr-4">
        <img src="{{ asset('images/logo-mark.png') }}" alt="Multi Wijaya Technology" class="h-9 w-auto">
        <span class="font-display font-bold text-lg uppercase tracking-wide text-brand-ink">Multi Wijaya<span class="text-brand-cyan">.</span></span>
      </a>

      <nav class="hidden md:flex items-center gap-8">
        <a href="{{ route('catalog.index') }}"
           class="pb-1 text-sm font-medium border-b-2 transition-colors {{ request()->routeIs('catalog.*') ? 'border-brand-cyan text-brand-ink' : 'border-transparent text-brand-ink/70 hover:border-brand-cyan hover:text-brand-ink' }}">
          Catalog
        </a>
        <a href="#contact" class="pb-1 text-sm font-medium border-b-2 border-transparent text-brand-ink/70 hover:border-brand-cyan hover:text-brand-ink transition-colors">
          Contact
        </a>
      </nav>

      <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-4 text-sm mr-1">
          @auth
            <a href="{{ route('settings.set-password') }}" class="text-brand-ink/70 hover:text-brand-ink font-medium">{{ Auth::user()->name }}</a>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="text-brand-ink/70 hover:text-brand-ink font-medium cursor-pointer">Log out</button>
            </form>
          @else
            <a href="{{ route('login') }}" class="text-brand-ink/70 hover:text-brand-ink font-medium">Log in</a>
            <a href="{{ route('register') }}" class="text-brand-ink/70 hover:text-brand-ink font-medium">Sign up</a>
          @endauth
        </div>

        <button type="button" aria-label="Search" class="w-10 h-10 rounded-lg border border-surface-border flex items-center justify-center hover:border-brand-blue transition-colors">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        </button>
        <button type="button" aria-label="Cart" class="relative w-10 h-10 rounded-lg border border-surface-border flex items-center justify-center hover:border-brand-blue transition-colors">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
        </button>

        <input type="checkbox" id="nav-toggle" class="peer hidden">
        <label for="nav-toggle" class="md:hidden w-10 h-10 rounded-lg border border-surface-border flex items-center justify-center cursor-pointer" aria-label="Menu">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </label>

        <div class="hidden peer-checked:block md:hidden absolute left-0 right-0 top-20 bg-white border-b border-surface-border px-6 py-4 shadow-lg">
          <nav class="flex flex-col gap-3 mb-3">
            <a href="{{ route('catalog.index') }}" class="text-sm font-medium py-1.5 {{ request()->routeIs('catalog.*') ? 'text-brand-blue' : 'text-brand-ink' }}">Catalog</a>
            <a href="#contact" class="text-sm font-medium py-1.5 text-brand-ink">Contact</a>
          </nav>
          <div class="flex flex-col gap-3 pt-3 border-t border-surface-border text-sm">
            @auth
              <a href="{{ route('settings.set-password') }}" class="font-medium text-brand-ink">{{ Auth::user()->name }}</a>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="font-medium text-brand-ink cursor-pointer">Log out</button>
              </form>
            @else
              <a href="{{ route('login') }}" class="font-medium text-brand-ink">Log in</a>
              <a href="{{ route('register') }}" class="font-medium text-brand-ink">Sign up</a>
            @endauth
          </div>
        </div>
      </div>

    </div>
  </div>
</header>

<div class="bg-white border-b border-surface-border">
  <div class="max-w-7xl mx-auto px-6 md:px-10 py-4 flex flex-wrap justify-between gap-x-8 gap-y-2 text-xs sm:text-sm text-slate-500">
    <div><span class="font-semibold text-brand-ink">100%</span> sourced &amp; configured in Bali</div>
    <div><span class="font-semibold text-brand-ink">Hospitality · F&amp;B · NGO</span> sectors served</div>
    <div><span class="font-semibold text-brand-ink">1</span> cart for hardware + subscriptions</div>
    <div><span class="font-semibold text-brand-ink">On-site</span> install &amp; support</div>
  </div>
</div>

<main>
@yield('content')
</main>

<footer id="contact" class="bg-brand-ink text-white/70">
  <div class="max-w-7xl mx-auto px-6 md:px-10 py-20 md:py-24">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-10 md:gap-12 mb-16">
      <div class="col-span-2 md:col-span-1">
        <div class="py-2 -my-2 mb-5 inline-block">
          <img src="{{ asset('images/logo-mark.png') }}" alt="Multi Wijaya Technology" class="h-9 w-auto">
        </div>
        <p class="text-sm leading-relaxed text-white/50 max-w-xs">
          CV. Multi Wijaya — IT systems, hardware procurement, and software products for hospitality, F&amp;B, and NGO sectors across Bali.
        </p>
      </div>
      <div>
        <h5 class="text-white text-xs font-semibold uppercase tracking-wide mb-5">Hardware</h5>
        <ul class="space-y-3 text-sm">
          <li><a href="{{ route('catalog.index') }}" class="hover:text-brand-cyan transition-colors">Browse all hardware</a></li>
        </ul>
      </div>
      <div>
        <h5 class="text-white text-xs font-semibold uppercase tracking-wide mb-5">Software &amp; Services</h5>
        <ul class="space-y-3 text-sm">
          <li><a href="{{ route('catalog.software-services') }}" class="hover:text-brand-cyan transition-colors">Browse all software</a></li>
        </ul>
      </div>
      <div>
        <h5 class="text-white text-xs font-semibold uppercase tracking-wide mb-5">Company</h5>
        <ul class="space-y-3 text-sm">
          <li><a href="#" class="hover:text-brand-cyan transition-colors">About CV. Multi Wijaya</a></li>
          <li><a href="#" class="hover:text-brand-cyan transition-colors">Request a Quote</a></li>
          <li><a href="#" class="hover:text-brand-cyan transition-colors">Support</a></li>
          <li><a href="#" class="hover:text-brand-cyan transition-colors">Gianyar, Bali</a></li>
        </ul>
      </div>
    </div>
    <div class="pt-8 border-t border-white/10 flex flex-wrap justify-between gap-2 text-xs text-white/40">
      <span>&copy; {{ date('Y') }} CV. Multi Wijaya. All rights reserved.</span>
      <span>store.multiwijaya.com</span>
    </div>
  </div>
</footer>

</body>
</html>
