# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A browser-based Energy Trading & Risk Management (ETRM) training portal simulating a system like Endur. Covers the full commodity trading lifecycle — deal capture → validation → logistics → invoicing → settlement → risk monitoring — for training traders and back-office staff.

**Current status:** Fully built Laravel 13 / PHP 8.4 application. All 6 phases plus Phase 7 (financial instruments) are complete — `FinancialTradeController`, `FinancialSettlementController`, all views, and routes are implemented.

## Tech Stack

- **Backend:** PHP 8.4 + Laravel 13 (Blade templates, Eloquent ORM, Artisan CLI)
- **Frontend:** Alpine.js + Bootstrap 5 (no separate build step — no React/Vue)
- **Database:** MySQL
- **Auth:** Laravel Breeze (email/password sessions)
- **Hosting:** cPanel (shared hosting) — document root points to `public/`

## Common Commands

```bash
php artisan serve                        # start dev server
php artisan migrate                      # run pending migrations
php artisan migrate:fresh --seed         # wipe and reseed (uses DatabaseSeeder)
php artisan db:seed --class=SomeSeeder   # run a specific seeder
php artisan test                         # run all tests
php artisan test --filter SomeTest       # run a single test
php artisan route:list                   # inspect registered routes
```

For cPanel deployment: pull via Git Version Control, set document root to `public/`, run migrations via SSH.

## Scheduled Tasks (cPanel Cron)

```
* * * * * php artisan schedule:run   # daily EoB/CoB checklist reset
```

## Architecture

Controllers are namespaced by module under `app/Http/Controllers/`:

| Namespace | Module |
|---|---|
| `Master\*` | Master data (currencies, parties, products, indices, etc.) |
| `Trades\TradeController` | Physical trade capture & lifecycle |
| `Operations\*` | Shipments, invoices, settlements, nominations, EoB checklist |
| `Financials\*` | Market prices, broker fees, P&L, financial trade capture & settlements |
| `Admin\*` | User management, audit log |
| `Risk\*` | Portfolio analysis, counterparty exposure, VaR, reports |
| `Training\ScenarioController` | Guided training scenarios |

Routes are in `routes/web.php` under a single `auth` middleware group. Write routes are registered **before** read routes within each prefix block so `/create` and `/edit` are matched before the `{model}` wildcard. Admin-only routes use `middleware('role:admin')`; trader routes use `middleware('role:admin,trader')`.

### ID Generation Pattern (Physical + Financial Trades)

Three IDs per trade:
- **Deal Number** (`FIN-YYYY-NNNN` / `DEAL-YYYY-NNNN`) — permanent, per instrument
- **Transaction Number** (`TXN-YYYY-NNNN`) — shared sequence across both `trades` and `financial_trades`; changes on amendment
- **Instrument Number** (`INST-YYYY-NNNN`) — shared sequence; shared across linked OTC duplicates

The `nextTransactionNumber()` and `nextInstrumentNumber()` static methods on `FinancialTrade` union both tables for sequence continuity. The same logic must be kept in sync in `Trade` model.

### Trade Status Workflows

**Physical trades:** Pending → Validated → Active → Settled / Closed  
**Financial swaps:** Pending → Validated → Active → Settled / Closed  
**Financial futures/options:** Pending → Validated → Open → Expired / Exercised / Closed

`FinancialTrade::VALIDATED_STATUS` maps instrument type → post-validation status. `FinancialTrade::TERMINAL_STATUSES` lists statuses that block further edits.

### Financial Instruments (FinancialTrade model)

`financial_trades` is a single table covering three instrument types via `instrument_type` enum (`swap`, `futures`, `options`). Instrument-specific columns are nullable — only the relevant subset is populated per type:

- **Swap:** `swap_type`, `fixed_rate`, `float_index_id`, `second_index_id` (basis), `notional_quantity`, `spread`, `payment_frequency`, `start_date`, `end_date`
- **Futures:** `exchange`, `contract_code`, `expiry_date`, `num_contracts`, `contract_size`, `futures_price`, `margin_requirement`, `futures_index_id`
- **Options:** `option_type`, `exercise_style`, `strike_price`, `option_expiry_date`, `premium`, `underlying_index_id`, `volatility`

Analytics methods are on the model: `swapMtm()`, `futuresUnrealisedPnl()`, `blackScholesDelta/Gamma/Vega/Theta()`. Black-Scholes uses a hardcoded 5% risk-free rate (training constant).

### Roles

Three roles on `users.role`: `admin` (full access), `trader` (trade capture + view ops), `back_office` (ops + settlements, no trade capture). Enforced via `role:` middleware and `FinancialTradePolicy`.

### Key Models and Relationships

- `Party` — both internal BUs and external counterparties (`party_type`: `LE`/`BU`, `internal_external`)
- `IndexDefinition` → `IndexGridPoint` — price curves; `latestPrice` accessor used by analytics
- `Trade` and `FinancialTrade` are independent models (separate tables) but share transaction/instrument number sequences
- `FinancialTrade` → `FinancialSettlement` (hasMany); `FinancialSettlementController` auto-closes the trade when final settlement is confirmed
- All auditable models morph to `AuditLog` via `MorphMany`

### Seeder Stack

`DatabaseSeeder` calls: `MasterDataSeeder` → `UserSeeder` → `TradeSeeder` → `FieldDescriptionSeeder` → `GuidedScenarioSeeder`

### Training UX

- `field_descriptions` table seeded from `ETRM_Data_Dictionary_Combined.csv` — powers "?" tooltip icons
- `guided_scenarios` table — pre-loaded walkthroughs overlaid on the real UI

## Data Dictionary

`ETRM_Data_Dictionary_Combined.csv` is authoritative for field names, source types (System/User/Derived/Calculated), and descriptions. Reference it when adding new migrations or form fields.

Field source types:
- **System-generated:** IDs, transaction/instrument numbers, nomination IDs — never user-editable
- **Derived:** Pay/Receive (from Buy/Sell), statuses — server-side only
- **Calculated:** Trade Value, MTM, PnL, VaR — read-only, computed in model methods or SQL views

## Out of Scope

- Live market data feeds (manual price entry only)
- Accounting/GL integration
- Real demurrage/laytime calculation engine
