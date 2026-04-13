# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A browser-based Energy Trading & Risk Management (ETRM) training portal simulating a system like Endur. It covers the full physical commodity trading lifecycle — deal capture → validation → logistics → invoicing → settlement → risk monitoring — designed for training traders and back-office staff.

This project is currently in the **planning/pre-code phase**. The data dictionary (`ETRM_Data_Dictionary_Combined.csv`) and build plan (`plan.md`) are complete; no Laravel application has been scaffolded yet.

## Tech Stack

- **Backend:** PHP 8.x + Laravel (Blade templates, Eloquent ORM, Artisan CLI)
- **Frontend:** Alpine.js + Bootstrap 5 (no separate build step — no React/Vue)
- **Database:** MySQL (via cPanel MySQL Wizard)
- **Auth:** Laravel Breeze (email/password sessions)
- **Hosting:** cPanel (shared hosting) — document root must point to `public/`

## Initial Setup Commands

```bash
laravel new energytrm
cd energytrm
composer require laravel/breeze
php artisan breeze:install
php artisan migrate --seed
php artisan serve
```

For cPanel deployment: pull via Git Version Control, set document root to `public/`, run migrations via cPanel Terminal or SSH.

## Scheduled Tasks (cPanel Cron)

```
* * * * * php artisan schedule:run   # daily EoB/CoB checklist reset
```

## Architecture: 6 Modules

Build order matters — each module depends on the previous ones.

### Phase 1 — Master Data (reference entities everything else depends on)
- Party groups → Legal entities → Business units → Portfolios
- Personnel (linked to auth `users` table)
- Products, commodities, UOMs, pricing units
- Indices/curves with grid points (static for training)
- Currencies, payment terms, incoterms, agreements, settlement instructions
- Brokers (commission schedules, LEI, regulated entity flag)
- All master data records have: status workflow (Auth Pending → Authorized → Do Not Use), auto-incremented version tracking

### Phase 2 — Physical Trades (core deal capture)
- Three distinct IDs: **Deal Number** (permanent), **Transaction Number** (changes on amendment), **Instrument Number** (shared across OTC duplicates)
- Pay/Receive auto-derives from Buy/Sell direction
- Trade Status starts at Pending, progresses through workflow to Settled
- Fixed/Float pricing toggle; float pricing references an Index from Master Data
- Trade blotter grid as landing page (sortable/filterable)

### Phase 3 — Operations (post-trade lifecycle)
- **Logistics:** Shipments linked to trades; auto-populate load/discharge port and incoterm from trade
- **Invoices:** Auto-generated from validated trades; Invoice Amount = Qty × Price; status: Pending → Paid → Overdue
- **Settlements:** Link to invoice; when full payment received, Trade Status → Settled
- **Nominations:** Gas/power scheduling (nomination IDs, gas days, pipeline operators, volume matching)
- **EoB/CoB Checklist:** Daily per-business-unit checklist; items derived from module states (all trades approved, invoices matched, settlements complete); resets via cron

### Phase 4 — Financials
- Market prices/indices with manual grid point entry (simulates market data feed)
- Broker fees (commission structures linked to trades via broker field)
- P&L view: Trade Value (Qty × Price) and Unrealized PnL (Market Price − Trade Price × Qty) — read-only calculated views

### Phase 5 — User Management & Security
- Three roles: **Admin** (full), **Trader** (capture/amend trades, view operations), **Back Office** (operations + settlements, no trade capture)
- Security Groups: view-only vs execute-trades per counterparty/portfolio
- Implemented via Laravel Gate/Policy

### Phase 6 — Risk & Analytics
- **Portfolio Analysis:** Net Position, MTM Value, Exposure by Currency, Unrealized/Realized PnL — all aggregated from trades + market prices + settlements (SQL views, no user input)
- **Risk Management:** VaR (historical/parametric), stress test scenarios, Exposure by Counterparty, Credit Limit (stored on counterparty in Master Data), Breach Flag (calculated: exposure > credit_limit → warning at trade capture)
- **Reports:** Audit log of report runs (PDF/CSV exports stored in `/storage`)

## Key Database Tables

```
trades            (deal_number, transaction_number, instrument_number, trade_status, buy_sell, pay_rec,
                   start_date, end_date, internal_bu_id, counterparty_id, portfolio_id, product_id,
                   quantity, volume_type, uom_id, fixed_float, index_id, fixed_price, spread,
                   currency_id, incoterm, load_port, discharge_port, broker_id)

shipments         (shipment_id, trade_id, carrier_id, delivery_start, delivery_end, qty_delivered, delivery_status)
invoices          (invoice_id, trade_id, counterparty_id, invoice_date, invoice_amount, payment_terms_id, currency_id, invoice_status)
settlements       (settlement_id, invoice_id, payment_amount, payment_date, fx_rate, settlement_status)
indices           (index_id, version_id, index_name, market, index_group, format, class, base_currency_id, status)
index_grid_points (linked to indices)
parties           (party_id, party_type [LE/BU], internal_external, short_name, long_name, status, version,
                   credit_limit, credit_limit_currency, lei, bic_swift, kyc_status, kyc_review_date)
reports           (report_id, report_type, reporting_date, data_source, generated_by)
```

## Data Dictionary

`ETRM_Data_Dictionary_Combined.csv` is authoritative for all field names, source types (System/User/Derived/Calculated), and short descriptions. It has 352+ fields across 6 modules. When generating migrations, models, or form templates, reference this file directly — it is clean enough to paste field lists into prompts for scaffolding.

Field classification from the dictionary:
- **System-generated:** Trade ID, Transaction Number, Instrument Number, Nomination ID — never user-editable
- **User Input:** Trade date, quantity, pricing details
- **Derived:** Pay/Receive (from Buy/Sell), Trade Status, Nomination Status — auto-calculated server-side
- **Calculated:** Trade Value, MTM, PnL, Demurrage, VaR — read-only views only

## Training UX Layer (Phase 6 addition)

- Field tooltips: seed "Short Description" from the data dictionary CSV into a `field_descriptions` table; render as "?" hover icons
- Guided scenarios: pre-loaded trade walkthroughs overlaid on the real UI
- Audit trail view: version history for all records (especially trades where amendment creates new Transaction Number)

## Known Intentional Scope Gaps

Per `plan.md`, these are explicitly out of scope for this training tool:
- Financial trades (swaps, futures, options) — physical trades only
- Live market data feeds — manual price entry only
- Accounting/GL integration
- Real demurrage/laytime calculation engine (partial coverage in Logistics subtab)
