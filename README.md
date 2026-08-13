# Store Multi Wijaya — Catalog Scaffolding

## What's here

**`preview/index.html`** — Standalone landing page mockup. Open directly in a browser,
no build step needed. Uses your logo (`preview/logo.png`) and a palette pulled from it.

**`database/migrations/`** — Flexible catalog schema:
- `categories` — nested tree, tagged `hardware` / `software_service` / `mixed`
- `listings` — the universal entity. One table for both a Mikrotik router and a
  Restoflow subscription, distinguished by `listing_type`
- `pricing_plans` — one-time price for hardware, multiple tiers for software.
  `billing_cycle` = `one_time | monthly | yearly | custom_quote`
- `listing_specs` — flexible key/value specs (CPU/RAM for hardware, features for software)
- `carts` / `cart_items` — unified cart, snapshots price/name at add-time so
  historical orders don't break if you edit a listing later

**`app/Models/`** — matching Eloquent models with the relationships wired up.

## Why one `listings` table instead of separate `products` / `services` tables

New product lines (Spa/Massage, Retail System, Tour & Travel) only need a new
category + listings — no schema migration. The catalog, cart, and checkout code
stays identical whether the item is a CCTV bundle or an E-HIOS tier.

## Not built yet (next steps)
- `orders` / `order_items` tables (checkout finalization — carts convert into these)
- Admin CRUD for categories/listings/pricing (Filament or custom Blade)
- Public catalog controllers + Blade views styled from the mockup
- `custom_quote` plans need a "Request Quote" form instead of Add to Cart —
  the `Cart::hasCustomQuoteItems()` helper flags this at checkout time
- Flutter: once the Laravel API routes exist, the app consumes the same
  `listings` / `pricing_plans` endpoints — no separate backend logic needed
