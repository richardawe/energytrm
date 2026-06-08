<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MasterDataSeeder::class,
            UserSeeder::class,
            TradeSeeder::class,
            FinancialTradeSeeder::class,
            FieldDescriptionSeeder::class,
            GuidedScenarioSeeder::class,
            MarketDataFeedSeeder::class,  // wire live data sources to index definitions
        ]);
    }
}
