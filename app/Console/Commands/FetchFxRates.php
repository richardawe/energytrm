<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Services\MarketData\MarketDataIngestor;
use Illuminate\Console\Command;

class FetchFxRates extends Command
{
    protected $signature   = 'etrm:fetch-fx';
    protected $description = 'Fetch live FX rates from Open Exchange Rates and update the currencies table';

    public function handle(MarketDataIngestor $ingestor): int
    {
        $this->info('Fetching FX rates…');

        $updated = $ingestor->syncFxRates();

        if ($updated === 0) {
            $this->warn('No currencies updated — check network or rate-limit.');
            return self::FAILURE;
        }

        $currencies = Currency::whereNotIn('code', ['USD'])
            ->whereNotNull('last_synced_at')
            ->orderBy('code')
            ->get(['code', 'name', 'fx_rate_to_usd', 'last_synced_at']);

        $this->table(
            ['Code', 'Name', 'Rate (per USD)', 'Synced At'],
            $currencies->map(fn($c) => [
                $c->code, $c->name,
                number_format((float) $c->fx_rate_to_usd, 6),
                $c->last_synced_at->format('Y-m-d H:i'),
            ])->toArray()
        );

        $this->info("Updated {$updated} currencies.");
        return self::SUCCESS;
    }
}
