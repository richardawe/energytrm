<?php

namespace App\Console\Commands;

use App\Services\MarketData\MarketDataIngestor;
use Illuminate\Console\Command;

class FetchMarketPrices extends Command
{
    protected $signature   = 'etrm:fetch-prices {--index= : Sync only the named index}';
    protected $description = 'Fetch live commodity prices from EIA and FRED and store in index_grid_points';

    public function handle(MarketDataIngestor $ingestor): int
    {
        $this->info('Fetching market prices…');

        if ($name = $this->option('index')) {
            $index = \App\Models\IndexDefinition::where('index_name', $name)->first();
            if (! $index) {
                $this->error("Index '{$name}' not found.");
                return self::FAILURE;
            }
            $results = [$index->index_name => $ingestor->syncIndex($index)];
        } else {
            $results = $ingestor->syncAllPrices();
        }

        if (empty($results)) {
            $this->warn('No indices configured with a live feed source. Run the MarketDataFeedSeeder first.');
            return self::SUCCESS;
        }

        $headers = ['Index', 'Status', 'Points', 'Message'];
        $rows = collect($results)->map(fn($r, $name) => [
            $name, $r['status'], $r['points'], $r['message'],
        ])->values()->toArray();

        $this->table($headers, $rows);

        // Also refresh the risk-free rate on each price sync
        $sofr = $ingestor->syncRiskFreeRate();
        $this->line("Risk-free rate (SOFR): " . number_format($sofr * 100, 3) . '%');

        $this->info('Done.');
        return self::SUCCESS;
    }
}
