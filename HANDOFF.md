# energytrm-live — Build Handoff

## What this is

`richardawe/energytrm-live` is a production-oriented fork of `richardawe/energytrm` — a fully-built Laravel 13 / PHP 8.4 Energy Trading & Risk Management (ETRM) platform. The original was a training simulator; this repo is being evolved into a real platform for prospect demos and eventually live trading.

**Tech stack:** PHP 8.4 · Laravel 13 · Blade + Alpine.js + Bootstrap 5 · MySQL · Laravel Breeze auth · cPanel shared hosting (document root → `public/`)

---

## Current state of the repo

All code from the original training platform is present **plus** a live market data scaffold added in the first build session (commit `f9ce442`). The app covers the full commodity trading lifecycle: deal capture → validation → logistics → invoicing → settlement → risk monitoring.

### What's already fully built

- 60+ database tables, 46 Eloquent models, 56 controllers, 165 Blade views
- Physical trades (crude, gas, power, LNG) with full lifecycle and amendment versioning
- Financial instruments — swaps, futures, options — with Black-Scholes analytics
- Operations — shipments, invoices, nominations, settlements, EoB checklist
- Risk — VaR (historical simulation), stress scenarios, counterparty exposure, credit warnings
- Master data — parties (hierarchical Group→LE→BU), indices, products, currencies, brokers, exchanges, pipelines, etc.
- User management — 3 roles (admin/trader/back_office), personnel fields, security groups, trading locations
- Audit log — full morphable trail on all trade mutations
- Training scaffolding — guided scenarios, field description tooltips (from data dictionary CSV)

### Live data scaffold (added first build session)

Five new service classes under `app/Services/MarketData/`:

| Class | Purpose |
|---|---|
| `EiaService` | EIA API v2 adapter — WTI, Brent crude |
| `FredService` | FRED API adapter — Henry Hub gas, SOFR risk-free rate |
| `FxRateService` | Open Exchange Rates, no key required — EUR/GBP/JPY/SGD |
| `MarketDataIngestor` | Orchestrates all sources, writes to `index_grid_points`, updates `currencies.fx_rate_to_usd`, caches SOFR |

Two artisan commands:

```bash
php artisan etrm:fetch-prices   # syncs all configured index feeds
php artisan etrm:fetch-fx       # syncs FX rates
```

Scheduled in `routes/console.php`: prices twice daily (07:00 + 18:00 UTC), FX hourly (06:00–22:00 UTC).

Migration `2026_06_08_100001` adds to `index_definitions`: `live_feed_source`, `live_feed_route`, `live_feed_series`, `live_feed_multiplier`, `last_synced_at`. Also adds `last_synced_at` to `currencies`.

`MarketDataFeedSeeder` (runs automatically in the main seed chain) maps:

| Index | Source | Series | Multiplier |
|---|---|---|---|
| Brent 1M | EIA | `RBRTE` | 1.0 |
| WTI 1M | EIA | `RWTC` | 1.0 |
| TTF Day-Ahead | FRED | `DHHNGSP` | 1.35 |
| NBP Day-Ahead | FRED | `DHHNGSP` | 1.25 |
| UK Power Baseload | FRED | `DHHNGSP` | 10.8 |

`FinancialTrade` Black-Scholes now reads risk-free rate from `Cache::get('risk_free_rate', 0.05)` (populated by SOFR fetch) instead of hardcoded 5%.

Dashboard has a new **Market Data Feeds** widget (`resources/views/partials/market-feed-status.blade.php`) showing feed health, staleness badges, live FX rates, SOFR, and a **Refresh Now** button (admin only) at `POST /admin/market-data/refresh`.

---

## Environment variables needed

```env
EIA_API_KEY=      # free at eia.gov/opendata
FRED_API_KEY=     # free at fred.stlouisfed.org/docs/api/api_key.html
# FX rates use open.er-api.com — no key required
```

---

## Deployment checklist (cPanel)

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# edit .env: DB credentials, API keys, APP_ENV=production, APP_URL

php artisan migrate --force
php artisan db:seed --force           # includes MarketDataFeedSeeder

php artisan etrm:fetch-fx             # first FX sync (no key needed)
php artisan etrm:fetch-prices         # first price sync (needs API keys)
```

cPanel cron (single entry covers all scheduled tasks):

```
* * * * * /usr/local/bin/php /home/youraccount/energytrm-live/artisan schedule:run >> /dev/null 2>&1
```

---

## What still needs to be done

### Immediate — to share with prospects

1. Add `EIA_API_KEY` and `FRED_API_KEY` to `.env` on the server
2. Run first manual sync (`etrm:fetch-prices`, `etrm:fetch-fx`)
3. Confirm cron is active in cPanel
4. Log in as admin, verify dashboard Market Data Feeds widget goes green

### Short-term — polish for demos

5. **Reseed with live prices** — reseed trades using current market prices so MTM/P&L figures look credible rather than static training values
6. **TTF/NBP multiplier tuning** — adjust `live_feed_multiplier` on TTF and NBP rows in `index_definitions` to reflect current TTF-to-Henry Hub spread
7. **Custom branding** — update `APP_NAME`, logo, colour scheme from training defaults
8. **Training UI decision** — keep or remove guided scenario overlays and tooltip icons

### Medium-term — make it a real platform

9. **REST API** — add `/api/v1/` routes with Sanctum auth; the whole app is currently HTML-only
10. **Position management** — build a `positions` materialised view (net long/short per product/book)
11. **Real laytime/demurrage** — `getDemurrageOrDespatchAttribute()` on `Shipment` is a stub; build the SHINC/SHEX calculation engine
12. **European gas direct feed** — replace Henry Hub proxy for TTF/NBP with a real source (ICIS Heren API, or WorldBank monthly series as a stepping stone)
13. **Invoice → payment workflow** — auto-generate SWIFT payment instructions from confirmed settlements
14. **Broker statement reconciliation** — match broker fee records against `broker_commissions` table

### Longer-term — enterprise features

15. EMIR/REMIT regulatory reporting
16. Real-time WebSocket price updates (Laravel Echo + Soketi)
17. CCP clearing connectivity (ICE Clear, CME Clearing) for margin management
18. Data warehouse for heavy analytics (separate read DB)
19. Multi-entity / group consolidation

---

## Key credentials (training defaults — change before real use)

| User | Role | Password |
|---|---|---|
| admin@energytrm.com | admin | Admin@123! |
| j.okafor@energytrm.com | trader | same pattern |
| l.kovacs@energytrm.com | back_office | same pattern |

---

## Architecture notes

- **ID generation** — `nextTransactionNumber()` / `nextInstrumentNumber()` on `FinancialTrade` unions both `trades` and `financial_trades` tables. Any concurrent insert logic must keep these in sync with the same methods on the `Trade` model.
- **Routes** — write routes are registered before wildcard read routes in every prefix block. Admin writes use `middleware('role:admin')`, trader writes use `middleware('role:admin,trader')`.
- **MarketDataIngestor** is registered as a singleton in `AppServiceProvider`; inject via constructor in any new controller that needs feed data.
- **Black-Scholes risk-free rate** — now sourced from `Cache::get('risk_free_rate', 0.05)`, populated on every `etrm:fetch-prices` run via FRED SOFR. Falls back to 5% if cache is cold.
- **Hosting** — currently cPanel shared hosting. Document root must point to `public/`. When ready to move to cloud, the app is otherwise standard Laravel and will run on any PHP 8.4 + MySQL environment.
