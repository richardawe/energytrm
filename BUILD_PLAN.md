# EnergyTRM — Detailed Build Plan

**Repo:** https://github.com/richardawe/energytrm  
**Production URL:** https://energytrm.com  
**Stack:** PHP 8.x · Laravel · MySQL · Alpine.js · Bootstrap 5  
**Hosting:** cPanel (shared hosting)  
**Deployment:** GitHub Actions → SSH → cPanel

---

## Table of Contents

1. [Repository & Environment Setup](#1-repository--environment-setup)
2. [CI/CD Pipeline — GitHub Actions to cPanel](#2-cicd-pipeline--github-actions-to-cpanel)
3. [Database Architecture](#3-database-architecture)
4. [Phase 1 — Foundation & Master Data (Weeks 1–2)](#4-phase-1--foundation--master-data-weeks-12)
5. [Phase 2 — Physical Trades (Weeks 3–4)](#5-phase-2--physical-trades-weeks-34)
6. [Phase 3 — Operations (Weeks 5–6)](#6-phase-3--operations-weeks-56)
7. [Phase 4 — Financials (Weeks 7–8)](#7-phase-4--financials-weeks-78)
8. [Phase 5 — User Management & Security (Week 9)](#8-phase-5--user-management--security-week-9)
9. [Phase 6 — Risk & Analytics (Weeks 10–11)](#9-phase-6--risk--analytics-weeks-1011)
10. [Phase 7 — Training UX Layer (Week 12)](#10-phase-7--training-ux-layer-week-12)

---

## 1. Repository & Environment Setup

### 1.1 Laravel Scaffolding

```bash
laravel new energytrm
cd energytrm
composer require laravel/breeze
php artisan breeze:install blade   # Blade + Alpine.js variant
npm install && npm run build
php artisan migrate
```

### 1.2 Initial File & Directory Conventions

| Path | Purpose |
|---|---|
| `app/Models/` | One Eloquent model per entity |
| `app/Http/Controllers/` | Resource controllers (one per module subtab) |
| `app/Http/Requests/` | Form request validation classes |
| `database/migrations/` | One migration file per table |
| `database/seeders/` | Realistic training data per phase |
| `resources/views/` | Blade templates per module (e.g. `views/trades/`, `views/operations/`) |
| `resources/views/components/` | Reusable Blade components (status badges, field tooltips) |
| `.github/workflows/` | CI/CD workflow files |

### 1.3 `.env` for Production

The `.env` file is **never committed**. On the cPanel server it is uploaded directly via cPanel File Manager or SSH. Minimum production variables:

```dotenv
APP_NAME="EnergyTRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://energytrm.com
APP_KEY=         # generated via: php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=     # from cPanel MySQL Wizard
DB_USERNAME=     # from cPanel MySQL Wizard
DB_PASSWORD=     # from cPanel MySQL Wizard

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 1.4 `.gitignore` Additions

Add to the Laravel default `.gitignore`:

```
.env
storage/app/public/reports/
```

---

## 2. CI/CD Pipeline — GitHub Actions to cPanel

### 2.1 How It Works

On every push to the `main` branch:

1. GitHub Actions runner installs PHP dependencies (no frontend build needed — no Vite/npm in production for this stack)
2. Connects to the cPanel server via SSH
3. Pulls latest code from the `main` branch into the deployment directory
4. Runs `composer install`, `php artisan migrate --force`, and `php artisan optimize`

```
git push → main
    └─> GitHub Actions triggered
            ├─> Install Composer dependencies (CI check)
            ├─> SSH into energytrm.com
            │       ├─> git pull origin main
            │       ├─> composer install --no-dev --optimize-autoloader
            │       ├─> php artisan migrate --force
            │       ├─> php artisan config:cache
            │       ├─> php artisan route:cache
            │       └─> php artisan view:cache
            └─> Done
```

### 2.2 GitHub Secrets Required

Add these in **GitHub → Settings → Secrets and variables → Actions**:

| Secret Name | Value |
|---|---|
| `SSH_HOST` | `energytrm.com` (or server IP) |
| `SSH_USERNAME` | Your cPanel SSH username |
| `SSH_PRIVATE_KEY` | Contents of your SSH private key (`~/.ssh/id_rsa`) |
| `SSH_PORT` | `22` (or your host's SSH port) |
| `DEPLOY_PATH` | Absolute path to app on server (e.g. `/home/yourusername/energytrm.com`) |

**How to generate the SSH key pair (run on your local machine):**

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/energytrm_deploy
# Add the PUBLIC key to cPanel → SSH/Shell Access → Manage SSH Keys → Import Key
# Paste the PRIVATE key into the SSH_PRIVATE_KEY GitHub secret
```

### 2.3 Workflow File

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    name: Deploy to energytrm.com
    runs-on: ubuntu-latest
    timeout-minutes: 10

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Validate composer.json
        run: composer validate --strict

      - name: Install dependencies (CI check only)
        run: composer install --no-dev --optimize-autoloader --no-interaction

      - name: Deploy via SSH
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USERNAME }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          port: ${{ secrets.SSH_PORT }}
          script: |
            set -e
            cd ${{ secrets.DEPLOY_PATH }}

            echo "→ Pulling latest code..."
            git pull origin main

            echo "→ Installing PHP dependencies..."
            composer install --no-dev --optimize-autoloader --no-interaction

            echo "→ Running migrations..."
            php artisan migrate --force

            echo "→ Caching config, routes, views..."
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache

            echo "→ Clearing old caches..."
            php artisan event:cache

            echo "✓ Deployment complete."
```

### 2.4 First-Time Server Setup (run once via cPanel Terminal or SSH)

```bash
# Navigate to your domain's public_html parent
cd /home/yourusername/

# Clone the repo (use a deploy token or SSH key already added to GitHub)
git clone git@github.com:richardawe/energytrm.git energytrm.com

# Set document root in cPanel to: /home/yourusername/energytrm.com/public

# Upload .env file via cPanel File Manager (do NOT commit it)
# Then run initial setup:
cd energytrm.com
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --seed
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2.5 cPanel Cron Job Setup

Add via **cPanel → Cron Jobs**:

```
* * * * * /usr/local/bin/php /home/yourusername/energytrm.com/artisan schedule:run >> /dev/null 2>&1
```

This drives the daily EoB/CoB checklist reset and any other scheduled tasks.

### 2.6 Deployment Branches Strategy

| Branch | Purpose | Auto-deploys |
|---|---|---|
| `main` | Production code | Yes — triggers deploy.yml |
| `develop` | Integration branch | No |
| `feature/*` | Feature work | No |

**Workflow:** `feature/xxx` → PR → `develop` (test locally) → PR → `main` (auto-deploys to production)

---

## 3. Database Architecture

### 3.1 Migration Creation Order (respects foreign key dependencies)

```
currencies
payment_terms
incoterms
transport_classes
party_groups
parties          (party_type: LE|BU, references party_groups)
portfolios       (references parties)
products
commodities
uoms
index_definitions
index_grid_points (references index_definitions, currencies)
agreements       (references parties)
settlement_instructions
brokers
broker_commissions (references brokers)
users            (Laravel default)
personnel        (references users, parties)
security_groups
functional_groups
trading_locations
trades           (references parties, portfolios, products, uoms, index_definitions, currencies, brokers)
shipments        (references trades)
nominations      (references trades)
invoices         (references trades, parties, payment_terms, currencies)
settlements      (references invoices)
eob_checklists   (references parties)
market_prices    (references index_definitions, currencies)
broker_fees      (references brokers, currencies)
stress_scenarios
reports
field_descriptions  (tooltip seed data from data dictionary)
```

### 3.2 Key Schema Details

**`trades` table** (core table — everything flows from here):
```sql
deal_number           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
transaction_number    BIGINT UNSIGNED  -- new value on each amendment
instrument_number     BIGINT UNSIGNED  -- shared for OTC duplicates
trade_status          ENUM('Pending','Validated','Amended','Settled','Cancelled')
buy_sell              ENUM('Buy','Sell')
pay_rec               ENUM('Pay','Receive')  -- derived from buy_sell
trade_date            DATE
start_date            DATE
end_date              DATE
start_time            TIME NULL
internal_bu_id        BIGINT UNSIGNED FK → parties
internal_le_id        BIGINT UNSIGNED FK → parties
portfolio_id          BIGINT UNSIGNED FK → portfolios
trader_id             BIGINT UNSIGNED FK → users
counterparty_id       BIGINT UNSIGNED FK → parties
agreement_id          BIGINT UNSIGNED FK → agreements
product_id            BIGINT UNSIGNED FK → products
quantity              DECIMAL(18,6)
volume_type           ENUM('Contract','Daily','Hourly','Period','Yearly')
uom_id                BIGINT UNSIGNED FK → uoms
fixed_float           ENUM('Fixed','Float')
index_id              BIGINT UNSIGNED NULL FK → index_definitions
fixed_price           DECIMAL(18,6) NULL
spread                DECIMAL(18,6) NULL
currency_id           BIGINT UNSIGNED FK → currencies
pricing_formula       TEXT NULL
reset_period          VARCHAR(50) NULL
payment_period        VARCHAR(50) NULL
payment_date_offset   INT NULL
incoterm_id           BIGINT UNSIGNED FK → incoterms
load_port             VARCHAR(100) NULL
discharge_port        VARCHAR(100) NULL
transport_class_id    BIGINT UNSIGNED NULL FK → transport_classes
broker_id             BIGINT UNSIGNED NULL FK → brokers
created_at / updated_at
```

**`parties` table** (covers party groups, legal entities, business units):
```sql
id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
party_type            ENUM('Group','LE','BU')
internal_external     ENUM('Internal','External')
parent_id             BIGINT UNSIGNED NULL FK → parties  -- hierarchy
short_name            VARCHAR(32) UNIQUE
long_name             VARCHAR(255)
status                ENUM('Auth Pending','Authorized','Do Not Use')
version               INT UNSIGNED DEFAULT 0
lei                   CHAR(20) NULL
bic_swift             VARCHAR(11) NULL
credit_limit          DECIMAL(18,2) NULL
credit_limit_currency_id BIGINT UNSIGNED NULL FK → currencies
kyc_status            ENUM('Pending','Approved','Expired','Suspended') NULL
kyc_review_date       DATE NULL
regulatory_class      ENUM('FC','NFC','NFC+','Third-Country') NULL
```

**`parties` versioning pattern** (applies to all master data tables):
```sql
-- On update: don't overwrite. Insert new row, increment version,
-- mark old row read-only. Implemented via Laravel Observer.
```

---

## 4. Phase 1 — Foundation & Master Data (Weeks 1–2)

### Goal
cPanel-deployable skeleton with auth, all reference entity CRUD, and realistic seed data.

### Tasks

**Week 1 — Scaffolding**
- [ ] `laravel new energytrm` and configure for cPanel (remove Vite's `npm run dev` requirement for prod)
- [ ] Install Laravel Breeze (Blade variant), run migrations
- [ ] Set up `.github/workflows/deploy.yml` and verify first deploy to energytrm.com
- [ ] Configure `app/Providers/AppServiceProvider.php` to force HTTPS in production
- [ ] Create base layout: `resources/views/layouts/app.blade.php` — Bootstrap 5 navbar with module tabs (Master Data, Trades, Operations, Financials, Risk, User Management)

**Week 2 — Master Data CRUD**

Build each entity as a full resource controller + Blade views with the versioning pattern:

| Entity | Key Fields | Notes |
|---|---|---|
| Currencies | code, name, symbol, fx_rate | USD default; static rates for training |
| Payment Terms | name, days_net, description | |
| Incoterms | code (FOB/CIF/DES...), description | |
| Transport Classes | name (Barge/Pipeline/Rail/Truck) | |
| Party Groups → Legal Entities → Business Units | hierarchy via parent_id | status + version tracking on all |
| Products & Commodities | name, commodity_type, default_uom | |
| UOMs | code, description, conversion_factor | MT, BBL, MMBTU, MWh |
| Index Definitions + Grid Points | name, market, class, base_currency | Grid points: date, price |
| Agreements | name, internal_party_id, counterparty_id, payment_terms_id | |
| Brokers | name, type, status, lei, regulated_entity | + commission schedules sub-table |
| Portfolios | name, bu_id, restricted_flag | |

**Shared CRUD pattern** (use for every master data entity):

```
GET  /master/currencies          → index (paginated table)
GET  /master/currencies/create   → create form
POST /master/currencies          → store
GET  /master/currencies/{id}     → show (with version history panel)
GET  /master/currencies/{id}/edit → edit form
PUT  /master/currencies/{id}     → update (creates new version via Observer)
```

**Seeder — Training Data** (run via `php artisan db:seed`):
- 3 internal BUs: `CRUDE_TRADING`, `GAS_TRADING`, `RISK_MGMT`
- 10 counterparties: major oil/gas companies (BP, Shell, TotalEnergies, Vitol, Trafigura, Glencore, Gunvor, Mercuria, Freepoint, Castleton)
- Products: Brent Crude, WTI Crude, LNG, TTF Gas, NBP Gas, Power (UK)
- Indices: Brent 1M, WTI 1M, TTF Day-Ahead (with 12 months of grid points at realistic prices)
- Currencies: USD, EUR, GBP, JPY
- Brokers: ICAP, Marex, Tradition
- 2 admin users + 5 trader users + 3 back-office users

### Acceptance Criteria
- All master data entities have Create / Read / Update / version history views
- Status transitions work (Auth Pending → Authorized → Do Not Use)
- Seeder populates all reference data needed for Phase 2
- App deploys successfully to energytrm.com via GitHub Actions push to `main`

---

## 5. Phase 2 — Physical Trades (Weeks 3–4)

### Goal
Full deal capture screen with trade blotter grid, matching Endur field behaviour.

### Key Behavioural Rules
- `deal_number` never changes once assigned
- `transaction_number` gets a new value on every amendment (old transaction becomes read-only)
- `instrument_number` is shared across OTC duplicates (unique for all others)
- `pay_rec` auto-derives on save: `Buy → Receive`, `Sell → Pay`
- `trade_status` starts at `Pending`; only moves to `Validated` via an explicit Validate action
- `internal_le_id` auto-populates from selected BU's parent Legal Entity
- `trader` defaults to authenticated user but is editable

### Tasks

**Week 3 — Trade Capture Form**
- [ ] `TradeController` as a resource controller
- [ ] Blade form (`views/trades/create.blade.php`) — Bootstrap 5 card sections:
  - Section 1: Identity (read-only: Deal Number, Transaction Number, Input Date)
  - Section 2: Dates & Direction (Trade Date, Start/End Date, Start Time, Buy/Sell, Trade Status)
  - Section 3: Parties (Internal BU → auto-loads LE + Portfolios, Trader, Counterparty, Agreement)
  - Section 4: Product & Volume (Product, Quantity, Volume Type, UOM, Transport Class)
  - Section 5: Pricing (Fixed/Float toggle; shows Fixed Price field OR Projection Index + Spread; Currency, Pricing Formula, Reset Period, Payment Period, Payment Date Offset)
  - Section 6: Logistics (Incoterm, Load Port, Discharge Port, Broker)
- [ ] Alpine.js: toggle Fixed/Float section visibility; dynamic pick-list reload for Portfolio when BU changes; pay/rec display update when Buy/Sell changes
- [ ] `StoreTradeRequest` form validation (required fields, date logic: end > start)

**Week 4 — Trade Blotter & Amendment Flow**
- [ ] Trade blotter (`views/trades/index.blade.php`):
  - Server-side paginated table (25 rows/page)
  - Columns: Deal No | Transaction No | Trade Date | Buy/Sell | Counterparty | Product | Qty | UOM | Price | Currency | Status
  - Filters: status (multi-select), date range, portfolio, counterparty, product
- [ ] Trade detail view (`views/trades/show.blade.php`):
  - Full field display + version history timeline at bottom
  - Action buttons: Validate | Amend | Cancel (shown based on current status + user role)
- [ ] Amendment flow: `POST /trades/{id}/amend` → creates new `transaction_number`, copies fields to new row, redirects to edit form pre-populated
- [ ] `POST /trades/{id}/validate` → sets `trade_status = Validated` (Trader role only)
- [ ] Credit limit warning (integrate with Phase 6 later — stub an Alpine.js banner placeholder for now)

### Acceptance Criteria
- A trade can be captured, saved as Pending, validated, and amended (each step creates the correct ID behaviour)
- Blotter filters work across all filter combinations
- Pay/Rec derives correctly; LE auto-loads from BU

---

## 6. Phase 3 — Operations (Weeks 5–6)

### Goal
Post-trade lifecycle: logistics (with demurrage), nominations, invoicing, settlement, and EoB/CoB checklist.

### Tasks

**Week 5 — Logistics & Nominations**

**Logistics subtab** (`shipments` table):
- [ ] Auto-create a shipment record when a trade is Validated (link Trade ID)
- [ ] Shipment form: pre-populate Load Port, Discharge Port, Incoterm from trade; user enters Vessel/Carrier, Delivery Start/End, Quantity Delivered, Delivery Status
- [ ] Add demurrage fields to the same form (collapsible panel, shown when Incoterm is voyage-based):
  - Laycan Start / Laycan End, Vessel ETA
  - BL Date, BL Quantity, Draft Survey Quantity
  - NOR Date/Time, Laytime Commencement (derived = NOR + contract notice period)
  - Allowed Laytime (hours), Time Used (calculated = completion time − commencement)
  - Demurrage Rate (USD/day), Demurrage/Despatch Amount (calculated)
  - Freight Cost, Freight Basis

**Nominations subtab** (`nominations` table):
- [ ] Nomination form linked to a Trade ID: Gas Day, Scheduling Window, Pipeline/Grid Operator, Nominated Volume, Counterpart Nominated Volume, Nomination Status, Imbalance Quantity (calculated)
- [ ] Status flow: `Submitted → Confirmed / Rejected / Matched`
- [ ] Submission Timestamp auto-populated on save

**Week 6 — Invoices, Settlements, EoB Checklist**

**Invoices subtab** (`invoices` table):
- [ ] Auto-generate commodity invoice when Shipment reaches `Delivered` status
- [ ] Invoice Amount = BL Quantity (or Draft Survey Qty if specified) × Trade Price (converted to invoice currency via FX rate)
- [ ] Additional fields: Invoice Type (Commodity / Demurrage / Freight / Commission), Tax Code, Tax/VAT Amount (calculated), Invoice Reference (External), Dispute Flag, Dispute Reason
- [ ] Demurrage invoice: auto-generate when Demurrage/Despatch Amount is non-zero
- [ ] Invoice status flow: `Pending → Paid → Overdue` (Overdue: cron job via `php artisan schedule:run` checks payment date vs today)

**Settlements subtab** (`settlements` table):
- [ ] Linked to Invoice; user enters Payment Amount, Payment Date, FX Conversion Rate
- [ ] `Settlement Status → Completed` when payment amount ≥ invoice amount
- [ ] Trigger: update linked Trade's `trade_status → Settled` when all invoices settled

**EoB/CoB Checklist**:
- [ ] One checklist record per Business Unit per business date (auto-created by scheduler at 00:00)
- [ ] Derived checks (queried live, not stored): All trades approved, All invoices matched, All settlements complete, Market data loaded
- [ ] User sign-off: `POST /checklist/{id}/sign-off` → stores `signoff_user_id` + timestamp
- [ ] `Checklist Status` = Pass only when all 4 checks pass AND signed off

### Acceptance Criteria
- Full lifecycle: Trade → Shipment → Invoice (commodity) → Settlement → Trade status = Settled
- Demurrage amount calculates correctly from NOR date, allowed laytime, and rate
- EoB checklist shows Pass/Fail per BU and allows sign-off

---

## 7. Phase 4 — Financials (Weeks 7–8)

### Goal
Market data management, broker fees, and P&L views.

### Tasks

**Week 7 — Market Prices & Broker Fees**

**Market Prices / Indices** (`market_prices` table):
- [ ] Price entry form per index grid point: Date, Price, Currency — confirms "Market Data Loaded" for EoB checklist
- [ ] Bulk entry: paste CSV of date/price pairs for an index
- [ ] Index status: `Custom / Official / Template`
- [ ] Display forward curve chart (Alpine.js + simple SVG or Chart.js)

**Broker Fees** (`broker_fees` table):
- [ ] Linked to Broker in Master Data
- [ ] Fields: Commission Rate, Rate Unit (per MT / per BBL / % of Trade Value), Currency, Payment Frequency, Min Fee, Max Fee, Effective Date, Index Group
- [ ] Commission invoice auto-generated when a brokered trade is settled

**Week 8 — P&L Views**

These are read-only calculated views — no user input:
- [ ] **Trade Value**: `Quantity × Fixed Price` (fixed) or `Quantity × current Index Grid Point price` (float). Displayed on trade detail page.
- [ ] **Unrealized PnL**: `(Current Market Price − Trade Price) × Quantity`. Requires market price to exist for the trade's index and date range.
- [ ] **Realized PnL**: `Settlement Amount − Trade Value` (shown only for Settled trades)
- [ ] P&L summary view: table grouped by Portfolio, showing Trade Value, Unrealized PnL, Realized PnL columns. Filter by date range, portfolio, product.
- [ ] FX Rates: static table (USD, EUR, GBP etc.) with manually updated rates. No live feed.

### Acceptance Criteria
- Manual price entry updates the forward curve display
- Unrealized PnL on the blotter updates when market prices change
- Broker commission invoice generated for brokered settled trades

---

## 8. Phase 5 — User Management & Security (Week 9)

### Goal
Three-role RBAC, personnel records, and portfolio-level access restrictions.

### Tasks

- [ ] **Personnel records** (`personnel` table): link to Laravel `users` table; fields: Personnel ID, Version, Status, Type (Internal/External/Licensed), License Type, Short Ref Name, Employee ID, Title, Name, Phone, Email, Address, Business Units (many-to-many)
- [ ] **Three roles** via Laravel `Gate` / `Policy`:

| Role | Permissions |
|---|---|
| `admin` | Full access to all modules + master data management |
| `trader` | Trade capture, amend, validate; view operations; read-only financials |
| `back_office` | Operations (logistics, invoices, settlements); read-only trades; EoB checklist sign-off |

- [ ] **Security Groups** (`security_groups` table): assign view-only vs execute-trades access per counterparty/portfolio combination. Checked at trade capture — if trader's security group doesn't allow `execute` on selected portfolio, block submission.
- [ ] **Functional Groups & Trading Locations**: `functional_groups` and `trading_locations` tables; informational, displayed on personnel profile.
- [ ] Route middleware: `auth` on all routes; role gates on sensitive actions (`validate.trade`, `manage.master-data`, `sign-off.checklist`)
- [ ] Admin panel (`/admin/users`): invite new users (email), assign role, assign BUs, set personnel record

### Acceptance Criteria
- Trader cannot access `/admin/users`
- Back office cannot reach trade capture form
- Security group restriction blocks trade capture on restricted portfolios
- Admin can invite and role-assign new users

---

## 9. Phase 6 — Risk & Analytics (Weeks 10–11)

### Goal
Portfolio Analysis dashboard, VaR, stress testing, credit breach warnings, and reports log.

### Tasks

**Week 10 — Portfolio Analysis & Credit Breach**

**Portfolio Analysis** (computed SQL view, no new user input tables):
```sql
CREATE VIEW portfolio_analysis AS
SELECT
  t.portfolio_id,
  t.product_id,
  SUM(CASE WHEN t.buy_sell = 'Buy' THEN t.quantity ELSE -t.quantity END) AS net_position,
  SUM(
    (CASE WHEN t.buy_sell = 'Buy' THEN t.quantity ELSE -t.quantity END)
    * (mp.price - COALESCE(t.fixed_price, t.spread))
  ) AS mtm_value,
  -- unrealized_pnl = mtm_value (same formula for physical trades)
  -- realized_pnl calculated from settlements
FROM trades t
LEFT JOIN market_prices mp ON mp.index_id = t.index_id AND mp.price_date = CURDATE()
WHERE t.trade_status NOT IN ('Cancelled')
GROUP BY t.portfolio_id, t.product_id;
```

- [ ] Portfolio Analysis dashboard page: Net Position, MTM Value, Exposure by Currency, Unrealized PnL, Realized PnL. Grouped by Portfolio → Product.
- [ ] Exposure by Counterparty: `SUM(trade_value) per counterparty_id` (running total of open trades)
- [ ] **Credit Breach Warning** at trade capture: Alpine.js `x-init` hook on counterparty select — fetches `GET /api/credit-check/{counterparty_id}` which returns `{limit, current_exposure, utilisation_pct, warning_threshold}`. Display warning banner if `utilisation_pct >= warning_threshold`. Hard-block (prevent form submit) if `utilisation_pct >= 100`.

**Week 11 — VaR, Stress Testing, Reports**

**Risk Management** (`var_config` and `stress_scenarios` tables):
- [ ] VaR configuration form: Confidence Level (95%/99%), Lookback Period (days, default 250), Holding Period (1/10 days), Method (Historical Simulation / Parametric / Monte Carlo)
- [ ] VaR calculation (simplified Historical Simulation for training):
  ```
  1. Pull last N days of market_prices for each index in portfolio
  2. Calculate daily returns: (price_t / price_t-1) - 1
  3. For each historical day: recalculate portfolio PnL using that day's return
  4. Sort simulated PnL values; VaR = loss at (1 - confidence) percentile
  ```
- [ ] Stress Test form: Scenario Name, per-index Price Shock (%), calculate Stressed P&L = `Net Position × (Index Price × (1 + Shock%) - Trade Price)`
- [ ] Reports log (`reports` table): log every time a report is generated (Report Type, Reporting Date, Generated By). Export to CSV via `League\Csv` or Laravel's built-in CSV response.

### Acceptance Criteria
- Portfolio dashboard shows live MTM updating as market prices change
- Credit breach warning fires at Warning Threshold %; blocks at 100%
- VaR result changes when lookback period or method is changed
- Stress test correctly recalculates P&L after price shock

---

## 10. Phase 7 — Training UX Layer (Week 12)

### Goal
Make this a genuine training tool, not just a data entry system.

### Tasks

**Field Tooltips**:
- [ ] Create `field_descriptions` table: `module, subtab, field_name, short_description, example_value`
- [ ] Seed from `ETRM_Data_Dictionary_Combined.csv` (443 rows already written)
- [ ] Create Blade component `<x-field-tooltip field="trade_date" />` — renders a `?` icon; on hover shows description + example via Alpine.js `x-show`
- [ ] Apply component to every field label across all module forms

**Guided Scenarios**:
- [ ] Create `scenarios` table: `name, description, steps (JSON)`
- [ ] Seed 3 core scenarios:
  1. "Book a CIF Brent crude physical trade end-to-end" (Trade capture → Validate → Shipment → Invoice → Settle)
  2. "Check end-of-day business checklist" (Price entry → EoB checklist → Sign-off)
  3. "Run a credit utilisation check" (Risk dashboard → Exposure check → Stress test)
- [ ] Scenario player: `GET /scenarios/{id}/play` renders current module UI with a step-by-step overlay panel; `Next Step` button advances via `POST /scenarios/{id}/progress`

**Audit Trail View**:
- [ ] On every entity detail page: collapsible "History" panel showing all versions (created_at, changed_by, old/new values via JSON diff)
- [ ] Implemented via a polymorphic `audit_logs` table (or Laravel Auditing package: `owen-it/laravel-auditing`)

### Acceptance Criteria
- Every field in every form has a working tooltip with description from the data dictionary
- All 3 scenarios can be completed start-to-finish by a new user with no prior knowledge
- Version history is visible on every trade and master data record

---

## Summary Timeline

| Phase | Focus | Weeks |
|---|---|---|
| 1 | Foundation + CI/CD + Master Data | 1–2 |
| 2 | Physical Trades | 3–4 |
| 3 | Operations (Logistics + Invoices + Settlements + Checklist) | 5–6 |
| 4 | Financials (Market Data + P&L) | 7–8 |
| 5 | User Management & RBAC | 9 |
| 6 | Risk & Analytics (VaR + Credit Breach) | 10–11 |
| 7 | Training UX Layer (Tooltips + Scenarios + Audit) | 12 |
| **Total** | | **~12 weeks** |

Every phase ends with a push to `main` that auto-deploys to energytrm.com, so the application is always live and testable at the end of each phase.
