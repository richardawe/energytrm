# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A browser-based Energy Trading & Risk Management (ETRM) training portal simulating a system like Endur. Covers the full commodity trading lifecycle — deal capture → validation → logistics → invoicing → settlement → risk monitoring — for training traders and back-office staff.

**Current status:** Fully built Laravel 13 / PHP 8.4 application. All phases are complete, including Phase 7 (financial instruments) and a full data dictionary implementation pass (April 2026) that added ~23 migrations, 17 new models, 16 new controllers, and 15 new view directories.

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
| `Master\*` | Master data (currencies, parties, products, indices, exchanges, governing bodies, commodities, contract types, settlement instructions, accounts, security groups, trading locations, broker commissions, party addresses/notes/credit ratings, index grid points) |
| `Trades\TradeController` | Physical trade capture & lifecycle |
| `Operations\*` | Shipments, invoices, settlements, nominations, EoB checklist |
| `Financials\*` | Market prices, broker fees, P&L, financial trade capture & settlements |
| `Admin\*` | User management (with personnel fields + assignment pivot tables), audit log |
| `Risk\*` | Portfolio analysis, counterparty exposure, VaR config, stress scenarios, credit warnings, reports |
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
- **Clearing & Hedge (all types):** `settlement_method`, `lot_size`, `number_of_lots`, `clearing_venue`, `clearing_broker_id` (FK → parties), `margin_account_ref`, `hedge_designation`, `hedged_item_reference`

Analytics methods are on the model: `swapMtm()`, `futuresUnrealisedPnl()`, `blackScholesDelta/Gamma/Vega/Theta()`. Black-Scholes uses a hardcoded 5% risk-free rate (training constant).

### Roles

Three roles on `users.role`: `admin` (full access), `trader` (trade capture + view ops), `back_office` (ops + settlements, no trade capture). Enforced via `role:` middleware and `FinancialTradePolicy`.

### Key Models and Relationships

**Core trading:**
- `Trade` — physical trades; `transferMethod()` BelongsTo `TransportClass`; pricing fields: `start_time`, `deal_volume_type`, `reset_period`, `payment_period`, `payment_date_offset`, `pricing_formula`
- `FinancialTrade` → `FinancialSettlement` (hasMany); `clearingBroker()` BelongsTo `Party`; `FinancialSettlementController` auto-closes the trade when final settlement is confirmed
- Both `Trade` and `FinancialTrade` morph to `AuditLog` via `MorphMany`

**Master data:**
- `Party` — both internal BUs and external counterparties (`party_type`: `LE`/`BU`, `internal_external`); has `addresses()`, `notes()`, `creditRatings()`, `settlementInstructions()` HasMany
- `IndexDefinition` → `IndexGridPoint` — price curves with full curve config (interpolation, projection_method, holiday_schedule, etc.); `latestPrice` accessor used by analytics; `discountIndex()` self-referential BelongsTo
- `Exchange` — trading exchanges (ICE, CME, etc.)
- `GoverningBody` — regulatory/legal authorities
- `Commodity` — energy/metal/agricultural commodities with `commodity_group` enum
- `ContractType` — spot/term/framework contract types
- `SettlementInstruction` — SI records for payment routing; `nextSiNumber()` static method
- `Account` — Nostro/Vostro/Margin accounts; `holdingParty()` and `currency()` BelongsTo
- `PartyAddress`, `PartyNote`, `CreditRating` — party sub-records (nested under Party)
- `SecurityGroup` — user access permission groups
- `TradingLocation` — office and trading desk locations
- `PaymentTerm` — includes `discount_rate`
- `TransportClass` — includes `transfer_point` enum (Load/Discharge/Both)

**Operations:**
- `Invoice` — includes `invoice_type`, `invoice_reference_external`, `tax_amount`, `tax_code`, `dispute_status`, `dispute_reason`
- `Nomination` — includes `scheduling_window`, `counterpart_nominated_volume`, `imbalance_quantity`, `submission_timestamp`
- `Shipment` — full logistics fields: `vessel_eta_date`, `laycan_start/end`, `nor_date`, `laytime_commencement`, `allowed_laytime_hours`, `time_used_hours`, `demurrage_rate/currency/amount`, `freight_cost/basis`, `bl_quantity`, `draft_survey_quantity`; `getDemurrageOrDespatchAttribute()` accessor

**User management:**
- `User` — extended with personnel fields: `personnel_id`, `user_type`, `license_type`, `short_ref_name`, `employee_id`, `title`, `phone`, `address`, `city`, `state`, `country`, `password_never_expires`, `status`
- `User` BelongsToMany: `businessUnits()` (via `user_business_units`), `portfolios()` (via `user_portfolios`), `securityGroups()` (via `user_security_groups`), `tradingLocations()` (via `user_trading_locations`, with `is_default`)

**Risk:**
- `VarConfiguration` — lookback/holding period, method, confidence level; `createdBy()` BelongsTo User
- `StressScenario` → `StressScenarioShock` (hasMany) — shock definitions per index
- `CreditWarningThreshold` — per-party warning/breach percentage thresholds

### Seeder Stack

`DatabaseSeeder` calls: `MasterDataSeeder` → `UserSeeder` → `TradeSeeder` → `FieldDescriptionSeeder` → `GuidedScenarioSeeder`

### Training UX

- `field_descriptions` table seeded from `ETRM_Data_Dictionary_Combined.csv` — powers "?" tooltip icons
- `guided_scenarios` table — pre-loaded walkthroughs overlaid on the real UI

## Data Dictionary

`ETRM_Data_Dictionary_Combined.csv` is authoritative for field names, source types (System/User/Derived/Calculated), and descriptions. All fields in the CSV are now implemented. Reference it when adding new migrations or form fields.

Field source types:
- **System-generated:** IDs, transaction/instrument numbers, nomination IDs, SI numbers — never user-editable
- **Derived:** Pay/Receive (from Buy/Sell), statuses — server-side only
- **Calculated:** Trade Value, MTM, PnL, VaR, demurrage/despatch — read-only, computed in model methods or SQL views

## Route Conventions

- Nested sub-resources use dot notation: `master.parties.addresses.*`, `master.parties.notes.*`, `master.parties.credit-ratings.*`, `master.brokers.commissions.*`, `master.indices.grid-points.*`
- Admin write routes use `->except(['index', 'show'])` inside the admin middleware group; public read routes use `->only(['index', 'show'])` outside it — never duplicate route names
- Risk module routes: `risk.var-config.*`, `risk.stress-scenarios.*`, `risk.credit-warnings.*`

## Migration Naming Conventions

Migrations added in the April 2026 data dictionary pass use the prefix `2026_04_22_` with hundred-block ranges by phase:

| Range | Phase |
|---|---|
| `100xxx` | New master data standalone tables |
| `200xxx` | Party sub-records and financial instruments (SI, Accounts, Credit Ratings) |
| `300xxx` | Operations additions (shipments, invoices, nominations) |
| `400xxx` | User management (personnel fields, security groups, trading locations, pivot tables) |
| `500xxx` | Physical trade additions |
| `600xxx` | Index definition and grid point enhancements |
| `700xxx` | Financial trade clearing/hedge fields |
| `800xxx` | Risk module tables |

## Out of Scope

- Live market data feeds (manual price entry only)
- Accounting/GL integration
- Real demurrage/laytime calculation engine
