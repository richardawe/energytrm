<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Master\CurrencyController;
use App\Http\Controllers\Master\PaymentTermController;
use App\Http\Controllers\Master\IncotermController;
use App\Http\Controllers\Master\TransportClassController;
use App\Http\Controllers\Master\PartyController;
use App\Http\Controllers\Master\PartyAddressController;
use App\Http\Controllers\Master\PartyNoteController;
use App\Http\Controllers\Master\CreditRatingController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\UomController;
use App\Http\Controllers\Master\IndexDefinitionController;
use App\Http\Controllers\Master\IndexGridPointController;
use App\Http\Controllers\Master\AgreementController;
use App\Http\Controllers\Master\BrokerController;
use App\Http\Controllers\Master\BrokerCommissionController;
use App\Http\Controllers\Master\PortfolioController;
use App\Http\Controllers\Master\PipelineController;
use App\Http\Controllers\Master\ExchangeController;
use App\Http\Controllers\Master\GoverningBodyController;
use App\Http\Controllers\Master\CommodityController;
use App\Http\Controllers\Master\ContractTypeController;
use App\Http\Controllers\Master\SettlementInstructionController;
use App\Http\Controllers\Master\AccountController;
use App\Http\Controllers\Master\SecurityGroupController;
use App\Http\Controllers\Master\TradingLocationController;
use App\Http\Controllers\Trades\TradeController;
use App\Http\Controllers\Operations\ShipmentController;
use App\Http\Controllers\Operations\InvoiceController;
use App\Http\Controllers\Operations\SettlementController;
use App\Http\Controllers\Operations\NominationController;
use App\Http\Controllers\Operations\EobChecklistController;
use App\Http\Controllers\Financials\FinancialTradeController;
use App\Http\Controllers\Financials\FinancialSettlementController;
use App\Http\Controllers\Financials\MarketPricesController;
use App\Http\Controllers\Financials\BrokerFeesController;
use App\Http\Controllers\Financials\PnlController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Training\ScenarioController;
use App\Http\Controllers\Risk\PortfolioAnalysisController;
use App\Http\Controllers\Risk\CounterpartyExposureController;
use App\Http\Controllers\Risk\VarController;
use App\Http\Controllers\Risk\VarConfigController;
use App\Http\Controllers\Risk\StressScenarioController;
use App\Http\Controllers\Risk\CreditWarningController;
use App\Http\Controllers\Risk\ReportsController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Redirect root to login or dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Master Data ──────────────────────────────────────────────────────────
    Route::get('/master', function () {
        return view('master.dashboard');
    })->name('master.dashboard');

    Route::prefix('master')->name('master.')->group(function () {
        // Write routes registered FIRST so /create and /edit are matched before the {id} wildcard
        Route::middleware('role:admin')->group(function () {
            Route::resource('currencies',             CurrencyController::class)->except(['index', 'show']);
            Route::resource('payment-terms',          PaymentTermController::class)->except(['index', 'show']);
            Route::resource('incoterms',              IncotermController::class)->except(['index', 'show']);
            Route::resource('transport-classes',      TransportClassController::class)->except(['index', 'show']);
            Route::resource('parties',                PartyController::class)->except(['index', 'show']);
            Route::resource('products',               ProductController::class)->except(['index', 'show']);
            Route::resource('uoms',                   UomController::class)->except(['index', 'show']);
            Route::resource('indices',                IndexDefinitionController::class)->except(['index', 'show']);
            Route::resource('agreements',             AgreementController::class)->except(['index', 'show']);
            Route::resource('brokers',                BrokerController::class)->except(['index', 'show']);
            Route::resource('portfolios',             PortfolioController::class)->except(['index', 'show']);
            Route::resource('pipelines',              PipelineController::class)->except(['index', 'show', 'destroy']);
            Route::resource('exchanges',              ExchangeController::class)->except(['index', 'show']);
            Route::resource('governing-bodies',       GoverningBodyController::class)->except(['index', 'show']);
            Route::resource('commodities',            CommodityController::class)->except(['index', 'show']);
            Route::resource('contract-types',         ContractTypeController::class)->except(['index', 'show']);
            Route::resource('settlement-instructions',SettlementInstructionController::class)->except(['index', 'show']);
            Route::resource('accounts',               AccountController::class)->except(['index', 'show']);
            Route::resource('security-groups',        SecurityGroupController::class)->except(['index']);
            Route::resource('trading-locations',      TradingLocationController::class)->except(['index']);
            // Party nested resources — write routes before wildcards
            Route::post('parties/{party}/addresses',               [PartyAddressController::class, 'store'])->name('parties.addresses.store');
            Route::get('parties/{party}/addresses/create',         [PartyAddressController::class, 'create'])->name('parties.addresses.create');
            Route::get('parties/{party}/addresses/{address}/edit', [PartyAddressController::class, 'edit'])->name('parties.addresses.edit');
            Route::put('parties/{party}/addresses/{address}',      [PartyAddressController::class, 'update'])->name('parties.addresses.update');
            Route::delete('parties/{party}/addresses/{address}',   [PartyAddressController::class, 'destroy'])->name('parties.addresses.destroy');
            Route::post('parties/{party}/notes',                   [PartyNoteController::class, 'store'])->name('parties.notes.store');
            Route::get('parties/{party}/notes/create',             [PartyNoteController::class, 'create'])->name('parties.notes.create');
            Route::get('parties/{party}/notes/{note}/edit',        [PartyNoteController::class, 'edit'])->name('parties.notes.edit');
            Route::put('parties/{party}/notes/{note}',             [PartyNoteController::class, 'update'])->name('parties.notes.update');
            Route::delete('parties/{party}/notes/{note}',          [PartyNoteController::class, 'destroy'])->name('parties.notes.destroy');
            Route::post('parties/{party}/credit-ratings',               [CreditRatingController::class, 'store'])->name('parties.credit-ratings.store');
            Route::get('parties/{party}/credit-ratings/create',         [CreditRatingController::class, 'create'])->name('parties.credit-ratings.create');
            Route::get('parties/{party}/credit-ratings/{rating}/edit',  [CreditRatingController::class, 'edit'])->name('parties.credit-ratings.edit');
            Route::put('parties/{party}/credit-ratings/{rating}',       [CreditRatingController::class, 'update'])->name('parties.credit-ratings.update');
            Route::delete('parties/{party}/credit-ratings/{rating}',    [CreditRatingController::class, 'destroy'])->name('parties.credit-ratings.destroy');
            // Broker commission nested routes
            Route::get('brokers/{broker}/commissions/create',           [BrokerCommissionController::class, 'create'])->name('brokers.commissions.create');
            Route::post('brokers/{broker}/commissions',                  [BrokerCommissionController::class, 'store'])->name('brokers.commissions.store');
            Route::get('broker-commissions/{commission}/edit',           [BrokerCommissionController::class, 'edit'])->name('brokers.commissions.edit');
            Route::put('broker-commissions/{commission}',                [BrokerCommissionController::class, 'update'])->name('brokers.commissions.update');
            Route::delete('broker-commissions/{commission}',             [BrokerCommissionController::class, 'destroy'])->name('brokers.commissions.destroy');
            // Index grid point nested routes — write before {index} wildcard
            Route::get('indices/{index}/grid-points/create',             [IndexGridPointController::class, 'create'])->name('indices.grid-points.create');
            Route::post('indices/{index}/grid-points',                   [IndexGridPointController::class, 'store'])->name('indices.grid-points.store');
            Route::get('indices/{index}/grid-points/{gridPoint}/edit',   [IndexGridPointController::class, 'edit'])->name('indices.grid-points.edit');
            Route::put('indices/{index}/grid-points/{gridPoint}',        [IndexGridPointController::class, 'update'])->name('indices.grid-points.update');
            Route::delete('indices/{index}/grid-points/{gridPoint}',     [IndexGridPointController::class, 'destroy'])->name('indices.grid-points.destroy');
            Route::post('pipelines/{pipeline}/zones',                    [PipelineController::class, 'storeZone'])->name('pipelines.zones.store');
            Route::post('pipelines/{pipeline}/zones/{zone}/locations',   [PipelineController::class, 'storeLocation'])->name('pipelines.locations.store');
        });

        // Read-only routes — all authenticated users (registered after write routes)
        Route::resource('currencies',             CurrencyController::class)->only(['index', 'show']);
        Route::resource('payment-terms',          PaymentTermController::class)->only(['index', 'show']);
        Route::resource('incoterms',              IncotermController::class)->only(['index', 'show']);
        Route::resource('transport-classes',      TransportClassController::class)->only(['index', 'show']);
        Route::resource('parties',                PartyController::class)->only(['index', 'show']);
        Route::resource('products',               ProductController::class)->only(['index', 'show']);
        Route::resource('uoms',                   UomController::class)->only(['index', 'show']);
        Route::resource('indices',                IndexDefinitionController::class)->only(['index', 'show']);
        Route::resource('agreements',             AgreementController::class)->only(['index', 'show']);
        Route::resource('brokers',                BrokerController::class)->only(['index', 'show']);
        Route::resource('portfolios',             PortfolioController::class)->only(['index', 'show']);
        Route::resource('pipelines',              PipelineController::class)->only(['index', 'show']);
        Route::resource('exchanges',              ExchangeController::class)->only(['index', 'show']);
        Route::resource('governing-bodies',       GoverningBodyController::class)->only(['index', 'show']);
        Route::resource('commodities',            CommodityController::class)->only(['index', 'show']);
        Route::resource('contract-types',         ContractTypeController::class)->only(['index', 'show']);
        Route::resource('settlement-instructions',SettlementInstructionController::class)->only(['index', 'show']);
        Route::resource('accounts',               AccountController::class)->only(['index', 'show']);
        Route::resource('security-groups',        SecurityGroupController::class)->only(['index']);
        Route::resource('trading-locations',      TradingLocationController::class)->only(['index']);
    });

    // ── Physical Trades (Phase 2) ─────────────────────────────────────────────
    // Write routes first so /trades/create is matched before the {trade} wildcard
    Route::middleware('role:admin,trader')->group(function () {
        Route::resource('trades', TradeController::class)->except(['index', 'show', 'destroy']);
        Route::post('/trades/{trade}/validate', [TradeController::class, 'validate'])->name('trades.validate');
        Route::post('/trades/{trade}/revert',   [TradeController::class, 'revert'])->name('trades.revert');
    });
    Route::middleware('role:admin')->group(function () {
        Route::delete('/trades/{trade}', [TradeController::class, 'destroy'])->name('trades.destroy');
    });
    // Read-only after write routes
    Route::resource('trades', TradeController::class)->only(['index', 'show']);

    // ── Operations (Phase 3) ──────────────────────────────────────────────────
    Route::get('/operations', fn() => view('operations.dashboard'))->name('operations.dashboard');
    Route::prefix('operations')->name('operations.')->group(function () {
        Route::resource('shipments',   ShipmentController::class)->except(['destroy']);
        Route::resource('invoices',    InvoiceController::class)->except(['create', 'store', 'destroy']);
        Route::get('/invoices/create/{trade}',  [InvoiceController::class, 'createFromTrade'])->name('invoices.createFromTrade');
        Route::post('/invoices/create/{trade}', [InvoiceController::class, 'store'])->name('invoices.storeFromTrade');
        Route::resource('nominations', NominationController::class)->except(['show', 'destroy']);
        Route::get('/invoices/{invoice}/settlements/create',  [SettlementController::class, 'create'])->name('settlements.create');
        Route::post('/invoices/{invoice}/settlements',        [SettlementController::class, 'store'])->name('settlements.store');
        Route::patch('/settlements/{settlement}',             [SettlementController::class, 'update'])->name('settlements.update');
        Route::get('/eob',                    [EobChecklistController::class, 'index'])->name('eob.index');
        Route::post('/eob/{eobChecklist}/sign-off', [EobChecklistController::class, 'signOff'])->name('eob.signOff');
        Route::post('/eob/{eobChecklist}/reset',    [EobChecklistController::class, 'reset'])->name('eob.reset');
    });

    // ── Financials (Phase 4 + Financial Trades) ───────────────────────────────
    Route::get('/financials', fn() => view('financials.dashboard'))->name('financials.dashboard');
    Route::prefix('financials')->name('financials.')->group(function () {
        Route::get('market-prices',                          [MarketPricesController::class, 'index'])->name('market-prices.index');
        Route::get('market-prices/{index}',                  [MarketPricesController::class, 'show'])->name('market-prices.show');
        Route::post('market-prices/{index}',                 [MarketPricesController::class, 'store'])->name('market-prices.store');
        Route::delete('market-prices/point/{point}',         [MarketPricesController::class, 'destroy'])->name('market-prices.destroy');
        Route::get('broker-fees',                            [BrokerFeesController::class, 'index'])->name('broker-fees.index');
        Route::get('pnl',                                    [PnlController::class, 'index'])->name('pnl.index');

        // Financial Trades (write routes before {financialTrade} wildcard)
        Route::middleware('role:admin,trader')->group(function () {
            Route::resource('financial-trades', FinancialTradeController::class)->except(['index', 'show', 'destroy']);
            Route::post('financial-trades/{financialTrade}/validate', [FinancialTradeController::class, 'validate'])->name('financial-trades.validate');
            Route::post('financial-trades/{financialTrade}/revert',   [FinancialTradeController::class, 'revert'])->name('financial-trades.revert');
            Route::get( 'financial-trades/{financialTrade}/settlements/create', [FinancialSettlementController::class, 'create'])->name('financial-trades.settlements.create');
            Route::post('financial-trades/{financialTrade}/settlements',        [FinancialSettlementController::class, 'store'])->name('financial-trades.settlements.store');
        });
        Route::middleware('role:admin')->group(function () {
            Route::delete('financial-trades/{financialTrade}', [FinancialTradeController::class, 'destroy'])->name('financial-trades.destroy');
        });
        Route::resource('financial-trades', FinancialTradeController::class)->only(['index', 'show']);
    });

    // ── Risk & Analytics (Phase 6) ────────────────────────────────────────────
    Route::get('/risk', fn() => view('risk.dashboard'))->name('risk.dashboard');
    Route::prefix('risk')->name('risk.')->group(function () {
        Route::get('portfolio-analysis',   [PortfolioAnalysisController::class,   'index'])->name('portfolio-analysis');
        Route::get('counterparty-exposure',[CounterpartyExposureController::class, 'index'])->name('counterparty-exposure');
        Route::get('var',                  [VarController::class,                 'index'])->name('var');
        Route::get('reports',              [ReportsController::class,             'index'])->name('reports');
        Route::post('reports/generate',    [ReportsController::class,             'generate'])->name('reports.generate');
        // VaR Configuration, Stress Scenarios, Credit Warnings — write behind admin, reads for all
        Route::middleware('role:admin')->group(function () {
            Route::resource('var-config',       VarConfigController::class)->except(['index', 'show'])->names('var-config');
            Route::resource('stress-scenarios', StressScenarioController::class)->except(['index', 'show'])->names('stress-scenarios');
            Route::resource('credit-warnings',  CreditWarningController::class)->except(['index', 'show'])->names('credit-warnings');
        });
        Route::resource('var-config',       VarConfigController::class)->only(['index'])->names('var-config');
        Route::resource('stress-scenarios', StressScenarioController::class)->only(['index', 'show'])->names('stress-scenarios');
        Route::resource('credit-warnings',  CreditWarningController::class)->only(['index'])->names('credit-warnings');
    });

    // ── Training UX ───────────────────────────────────────────────────────────
    Route::prefix('training')->name('training.')->group(function () {
        Route::get('scenarios',          [ScenarioController::class, 'index'])->name('scenarios.index');
        Route::get('scenarios/{scenario}',[ScenarioController::class, 'show'])->name('scenarios.show');
    });

    // ── User Management & Admin (Phase 5) ─────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
    });
});

require __DIR__.'/auth.php';
