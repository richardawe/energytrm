<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Master\CurrencyController;
use App\Http\Controllers\Master\PaymentTermController;
use App\Http\Controllers\Master\IncotermController;
use App\Http\Controllers\Master\TransportClassController;
use App\Http\Controllers\Master\PartyController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\UomController;
use App\Http\Controllers\Master\IndexDefinitionController;
use App\Http\Controllers\Master\AgreementController;
use App\Http\Controllers\Master\BrokerController;
use App\Http\Controllers\Master\PortfolioController;
use App\Http\Controllers\Trades\TradeController;
use App\Http\Controllers\Operations\ShipmentController;
use App\Http\Controllers\Operations\InvoiceController;
use App\Http\Controllers\Operations\SettlementController;
use App\Http\Controllers\Operations\NominationController;
use App\Http\Controllers\Operations\EobChecklistController;
use App\Http\Controllers\Financials\MarketPricesController;
use App\Http\Controllers\Financials\BrokerFeesController;
use App\Http\Controllers\Financials\PnlController;
use Illuminate\Support\Facades\Route;

// Redirect root to login or dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
        Route::resource('currencies', CurrencyController::class);
        Route::resource('payment-terms', PaymentTermController::class);
        Route::resource('incoterms', IncotermController::class);
        Route::resource('transport-classes', TransportClassController::class);
        Route::resource('parties', PartyController::class);
        Route::resource('products', ProductController::class);
        Route::resource('uoms', UomController::class);
        Route::resource('indices', IndexDefinitionController::class);
        Route::resource('agreements', AgreementController::class);
        Route::resource('brokers', BrokerController::class);
        Route::resource('portfolios', PortfolioController::class);
    });

    // ── Physical Trades (Phase 2) ─────────────────────────────────────────────
    Route::resource('trades', TradeController::class);
    Route::post('/trades/{trade}/validate', [TradeController::class, 'validate'])->name('trades.validate');
    Route::post('/trades/{trade}/revert',   [TradeController::class, 'revert'])->name('trades.revert');

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

    // ── Financials (Phase 4) ──────────────────────────────────────────────────
    Route::get('/financials', fn() => view('financials.dashboard'))->name('financials.dashboard');
    Route::prefix('financials')->name('financials.')->group(function () {
        Route::get('market-prices',                          [MarketPricesController::class, 'index'])->name('market-prices.index');
        Route::get('market-prices/{index}',                  [MarketPricesController::class, 'show'])->name('market-prices.show');
        Route::post('market-prices/{index}',                 [MarketPricesController::class, 'store'])->name('market-prices.store');
        Route::delete('market-prices/point/{point}',         [MarketPricesController::class, 'destroy'])->name('market-prices.destroy');
        Route::get('broker-fees',                            [BrokerFeesController::class, 'index'])->name('broker-fees.index');
        Route::get('pnl',                                    [PnlController::class, 'index'])->name('pnl.index');
    });

    // ── Risk & Analytics (Phase 6) ────────────────────────────────────────────
    Route::get('/risk', fn() => view('coming-soon', ['module' => 'Risk & Analytics']))->name('risk.dashboard');

    // ── User Management (Phase 5) ─────────────────────────────────────────────
    Route::get('/admin/users', fn() => view('coming-soon', ['module' => 'User Management']))->name('admin.users.index');
});

require __DIR__.'/auth.php';
