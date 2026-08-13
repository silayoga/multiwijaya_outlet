# Store Multi Wijaya — System Blueprint & Setup Guide

Companion doc to the catalog scaffolding. Covers: how the pieces fit together,
how to get running in VS Code, and how `store-multiwijaya` (Laravel) talks to
`multiwijaya_official` (FastAPI + MongoDB + React).

---

## 1. The big picture

CV. Multi Wijaya currently has **two separate codebases** that need to work as
one brand:

| Repo | Stack | Role |
|---|---|---|
| `multiwijaya_official` | FastAPI + MongoDB, React frontend | Company profile — who you are, product lines overview, marketing |
| `store-multiwijaya` | Laravel + Blade/Vue, (MySQL/Postgres) | The actual store — catalog, cart, checkout, for both hardware and software |
| *(planned)* Flutter app | Flutter | Mobile client, consumes the Laravel API |

They stay **two separate services** — different stacks, different databases,
different deploy targets. They connect over HTTP, not by sharing a database.
That keeps `multiwijaya_official` free to stay a lightweight profile/marketing
site while `store-multiwijaya` carries the transactional weight (stock,
pricing, cart, orders).

```
                         ┌─────────────────────────┐
                         │   multiwijaya_official   │
                         │  React (public site)     │
                         │  FastAPI + MongoDB (api) │
                         └────────────┬─────────────┘
                                      │ fetch()
                                      │ GET /api/public/featured-listings
                                      ▼
                         ┌─────────────────────────┐
        Flutter app ───► │   store-multiwijaya     │
       (consumes same    │  Laravel API + Blade/Vue │
        API routes)      │  MySQL/Postgres          │
                         └─────────────────────────┘
```

`store-multiwijaya` is the source of truth for products, prices, and stock.
`multiwijaya_official` never writes catalog data — it only **reads** a small,
public slice of it (e.g. "featured listings" for the homepage) and otherwise
links out to `store.multiwijaya.com` for anything transactional (browsing,
cart, checkout, account).

---

## 2. Why not merge them into one repo?

Worth stating explicitly since it's tempting:

- `multiwijaya_official` is FastAPI/Mongo — rewriting it in Laravel just to
  merge repos is a lot of throwaway work for no functional gain.
- The profile site and the store have different release cadences — you'll
  touch pricing/catalog code weekly, but marketing copy rarely. Keeping them
  separate means a store deploy never risks the public site and vice versa.
- A public API boundary forces you to think about what's actually meant to be
  public — which directly helps with the CORS/auth issues flagged in
  `multiwijaya_official` previously (see §5).

---

## 3. VS Code setup — `store-multiwijaya`

### 3.1 Prerequisites

- PHP 8.2+
- Composer 2.x
- Node 20+ / npm
- MySQL 8 or PostgreSQL 15 (matches your usual Restoflow/E-HIOS setup)
- Git

### 3.2 Recommended VS Code extensions

| Extension | Why |
|---|---|
| `bmewburn.vscode-intelephense-client` | PHP language server (autocomplete, go-to-definition) |
| `onecentlin.laravel-blade` | Blade syntax highlighting |
| `amiralizadeh9480.laravel-extra-intellisense` | Route/view/config autocomplete |
| `vue.volar` | Vue 3 language support |
| `bradlc.vscode-tailwindcss` | If you switch the mockup CSS to Tailwind utility classes later |
| `mikestead.dotenv` | `.env` syntax highlighting |
| `eamodio.gitlens` | Since you're juggling multiple repos, blame/history context helps |

### 3.3 First-time project init

The scaffolding delivered so far (migrations + models) assumes a full Laravel
app around it. If you haven't run `laravel new` yet:

```bash
composer create-project laravel/laravel store-multiwijaya
cd store-multiwijaya

# Drop in the delivered files
# (copy database/migrations/* and app/Models/* from the scaffold into place,
#  overwriting the defaults Laravel generated)

composer require laravel/sanctum   # for API auth — see §5.3
php artisan install:api            # if using Laravel 11+, sets up Sanctum routes
```

### 3.4 `.env` — key values for this project

```env
APP_NAME="Store Multi Wijaya"
APP_URL=http://outlet.multiwijaya.test

DB_CONNECTION=mysql
DB_DATABASE=outlet_multiwijaya
DB_USERNAME=root
DB_PASSWORD=

# Public API access allowed from the profile site — see §5.2
OFFICIAL_SITE_ORIGIN=https://multiwijaya.com
OFFICIAL_SITE_API_KEY=            # shared secret, generate with: php artisan tinker → Str::random(40)
```

### 3.5 Migrate + run

```bash
php artisan migrate
npm install
npm run dev        # Vite dev server for Blade/Vue assets
php artisan serve  # or use Laravel Valet/Herd for outlet.multiwijaya.test
```

Open `preview/index.html` from the earlier delivery separately in a browser —
it's a static mockup, not wired into the Laravel app yet. Once you're happy
with the design, the next step is translating its sections into
`resources/views/` Blade components fed by the `Listing`/`Category` models.

### 3.6 Suggested folder additions (not yet scaffolded)

```
app/Http/Controllers/
├── Api/
│   ├── PublicCatalogController.php   ← what multiwijaya_official reads (§5)
│   ├── ListingController.php
│   └── CartController.php
├── CatalogController.php             ← Blade-rendered storefront pages
routes/
├── api.php     ← /api/... for Flutter + multiwijaya_official
├── web.php     ← Blade storefront
resources/views/
├── layouts/app.blade.php
├── catalog/
│   ├── index.blade.php   ← translate preview/index.html here
│   └── show.blade.php
```

---

## 4. VS Code workspace tip

If you're actively working across both repos, open them as a multi-root
workspace so Claude Code / IntelliSense doesn't confuse the two stacks:

```json
// store-multiwijaya.code-workspace
{
  "folders": [
    { "name": "store-multiwijaya (Laravel)", "path": "../store-multiwijaya" },
    { "name": "multiwijaya_official (FastAPI/React)", "path": "../multiwijaya_official" }
  ]
}
```

This also makes it easy to keep a single Claude Code session aware of both
sides of the API contract when you're building the integration in §5.

---

## 5. Connecting to `multiwijaya_official`

### 5.1 What "connect" should mean here

Keep it to **one read-only integration point**: `multiwijaya_official`'s React
homepage displays a handful of featured listings (mix of hardware + software)
pulled live from `store-multiwijaya`, each linking out to the real product
page on `store.multiwijaya.com` for anything transactional. No shared
database, no shared session — just a public GET endpoint.

```
React (multiwijaya_official)
   │
   │  GET https://store.multiwijaya.com/api/public/featured-listings
   │  Header: X-Official-Site-Key: <shared secret>
   ▼
Laravel (store-multiwijaya)
   │  PublicCatalogController::featured()
   │  → Listing::active()->featured()->with('pricingPlans')->limit(6)->get()
   ▼
JSON response (name, image, price, slug → link to store.multiwijaya.com/p/{slug})
```

### 5.2 Laravel side — the public endpoint

```php
// routes/api.php
Route::middleware('official.site')->get(
    '/public/featured-listings',
    [PublicCatalogController::class, 'featured']
);
```

```php
// app/Http/Middleware/VerifyOfficialSiteKey.php
public function handle(Request $request, Closure $next)
{
    if ($request->header('X-Official-Site-Key') !== config('services.official_site.key')) {
        abort(403, 'Invalid site key.');
    }
    return $next($request);
}
```

```php
// config/services.php
'official_site' => [
    'key' => env('OFFICIAL_SITE_API_KEY'),
],
```

Only expose the fields the homepage actually needs — never dump the full
`Listing` model:

```php
// PublicCatalogController::featured()
return Listing::active()->featured()
    ->with(['pricingPlans' => fn ($q) => $q->where('is_default', true)])
    ->limit(6)->get()
    ->map(fn ($l) => [
        'name' => $l->name,
        'slug' => $l->slug,
        'type' => $l->listing_type,
        'image' => $l->primaryImage(),
        'price' => $l->pricingPlans->first()?->formattedPrice(),
        'url' => "https://store.multiwijaya.com/p/{$l->slug}",
    ]);
```

### 5.3 FastAPI side — fixes needed before connecting anything

Before wiring this up, the earlier code review of `multiwijaya_official`
flagged issues that matter *specifically* because you're about to make it
talk to another live system with real pricing data:

- **Hardcoded JWT secret fallback** — if the app falls back to a default
  secret when `JWT_SECRET` isn't set, rotate to a required env var with no
  fallback. A guessable JWT secret on the profile site becomes a bigger
  problem once that site is trusted to call another service.
- **Permissive CORS** — tighten it to an explicit allowlist. If
  `store-multiwijaya`'s API will only ever be called server-side from
  FastAPI (not from the browser directly), you may not need browser CORS on
  the outlet side at all — have FastAPI proxy the request instead of exposing
  the outlet API key to the React frontend.
- **Publicly accessible seed endpoint** — close this regardless of the
  integration; it's unrelated to the storefront but should be fixed before
  any of this ships.

**Recommended pattern**: have the React frontend call **your own FastAPI
backend**, and have FastAPI call the Laravel public API server-side. That way
`OFFICIAL_SITE_API_KEY` never reaches the browser.

```
React → FastAPI (multiwijaya_official) → Laravel (store-multiwijaya)
        [server-side, holds the API key]
```

```python
# multiwijaya_official — FastAPI route, illustrative
@router.get("/homepage/featured-listings")
async def featured_listings():
    resp = httpx.get(
        "https://store.multiwijaya.com/api/public/featured-listings",
        headers={"X-Official-Site-Key": settings.OUTLET_API_KEY},
        timeout=5.0,
    )
    resp.raise_for_status()
    return resp.json()
```

React then just calls `/homepage/featured-listings` on its own backend, no
knowledge of the outlet's key or domain required.

### 5.4 Later, if you want unified accounts

Not needed for the read-only integration above, but if you eventually want
"one login" across the profile site and the store:

- Don't share JWT secrets between the two stacks directly — issue Laravel
  Sanctum tokens from `store-multiwijaya` and have FastAPI validate them via
  a introspection call, or move to a dedicated auth service (Keycloak/Auth0-
  style) both stacks trust.
- This is a bigger decision — worth its own planning session once the
  read-only integration is live and stable.

---

## 6. Suggested build order

1. Turn `preview/index.html` into real Blade views wired to `Listing`/`Category`.
2. Build `CartController` (session-based guest cart → converts to `Order` on checkout).
3. Ship `store.multiwijaya.com` standalone, fully working, before touching `multiwijaya_official`.
4. Fix the three `multiwijaya_official` security items in §5.3 (independent of this integration, but do it before exposing any new endpoint).
5. Add the read-only `featured-listings` integration.
6. Point the Flutter app at the same `routes/api.php` used by the integration.

---

*Next open item on this thread: `orders`/`order_items` migrations, and the
Blade translation of the mockup — say the word and we'll pick either up.*
