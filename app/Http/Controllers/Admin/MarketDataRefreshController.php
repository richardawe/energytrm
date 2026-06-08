<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MarketData\MarketDataIngestor;
use Illuminate\Http\RedirectResponse;

class MarketDataRefreshController extends Controller
{
    public function refresh(MarketDataIngestor $ingestor): RedirectResponse
    {
        $results  = $ingestor->syncAllPrices();
        $fxCount  = $ingestor->syncFxRates();
        $sofr     = $ingestor->syncRiskFreeRate();

        $ok      = collect($results)->where('status', 'ok')->count();
        $errored = collect($results)->where('status', 'error')->count();

        $message = "Synced {$ok} index feed(s)";
        if ($errored) $message .= ", {$errored} failed";
        if ($fxCount)  $message .= ", {$fxCount} FX rate(s) updated";
        $message .= '. SOFR: ' . number_format($sofr * 100, 3) . '%.';

        return back()->with('success', $message);
    }
}
