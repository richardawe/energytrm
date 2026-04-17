<?php

namespace Database\Seeders;

use App\Models\Agreement;
use App\Models\Broker;
use App\Models\Currency;
use App\Models\FinancialTrade;
use App\Models\IndexDefinition;
use App\Models\Party;
use App\Models\Portfolio;
use App\Models\Product;
use App\Models\Uom;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinancialTradeSeeder extends Seeder
{
    public function run(): void
    {
        $admin   = User::where('email', 'admin@energytrm.com')->first();
        $trader1 = User::where('email', 'trader1@energytrm.com')->first() ?? $admin;

        $internalBu  = Party::where('internal_external', 'Internal')->where('party_type', 'BU')->first();
        $hedgeBook   = Portfolio::where('name', 'HEDGE_BOOK')->first() ?? Portfolio::first();
        $crudeBook   = Portfolio::where('name', 'CRUDE_BOOK_A')->first() ?? Portfolio::first();
        $usd         = Currency::where('code', 'USD')->first();
        $gbp         = Currency::where('code', 'GBP')->first() ?? $usd;

        $bp          = Party::where('short_name', 'BP')->where('internal_external', 'External')->first();
        $shell       = Party::where('short_name', 'SHELL')->where('internal_external', 'External')->first();
        $total       = Party::where('short_name', 'TOTAL')->where('internal_external', 'External')->first();
        $vitol       = Party::where('short_name', 'VITOL')->where('internal_external', 'External')->first();

        $brentProd   = Product::where('name', 'like', '%Brent%')->first();
        $wtiProd     = Product::where('name', 'like', '%WTI%')->first() ?? $brentProd;
        $gasProd     = Product::where('name', 'like', '%NBP%')->first();

        $brentIdx    = IndexDefinition::where('index_name', 'like', '%Brent%')->first();
        $wtiIdx      = IndexDefinition::where('index_name', 'like', '%WTI%')->first() ?? $brentIdx;
        $nbpIdx      = IndexDefinition::where('index_name', 'like', '%NBP%')->first();

        $mmbtu = Uom::where('code', 'MMBTU')->first();
        $bbl   = Uom::where('code', 'BBL')->first();
        $lot   = Uom::where('code', 'Lot')->first() ?? $bbl;

        $broker    = Broker::where('status', 'Active')->first();
        $agreement = Agreement::first();

        $year = 2026;

        $trades = [
            // Commodity swap — Buy Fixed / Sell Float (Brent, Active)
            [
                'instrument_type'    => 'swap',
                'trade_date'         => '2026-01-20',
                'buy_sell'           => 'Buy',
                'counterparty'       => $bp,
                'product'            => $brentProd,
                'currency'           => $usd,
                'portfolio'          => $hedgeBook,
                'swap_type'          => 'commodity',
                'fixed_rate'         => 82.50,
                'float_index'        => $brentIdx,
                'notional_quantity'  => 100000,
                'uom'                => $bbl,
                'spread'             => 0.00,
                'payment_frequency'  => 'Monthly',
                'start_date'         => '2026-02-01',
                'end_date'           => '2026-07-31',
                'broker'             => $broker,
                'agreement'          => $agreement,
                'trade_status'       => 'Active',
                'validated_by'       => $admin,
                'validated_at'       => '2026-01-21 09:30:00',
                'comments'           => 'Hedge against physical Brent cargo Feb–Jul',
            ],
            // Commodity swap — Sell Fixed (WTI, Pending)
            [
                'instrument_type'    => 'swap',
                'trade_date'         => '2026-02-05',
                'buy_sell'           => 'Sell',
                'counterparty'       => $shell,
                'product'            => $wtiProd ?? $brentProd,
                'currency'           => $usd,
                'portfolio'          => $hedgeBook,
                'swap_type'          => 'commodity',
                'fixed_rate'         => 79.00,
                'float_index'        => $wtiIdx,
                'notional_quantity'  => 50000,
                'uom'                => $bbl,
                'spread'             => -0.25,
                'payment_frequency'  => 'Monthly',
                'start_date'         => '2026-03-01',
                'end_date'           => '2026-06-30',
                'broker'             => null,
                'agreement'          => $agreement,
                'trade_status'       => 'Pending',
                'validated_by'       => null,
                'validated_at'       => null,
                'comments'           => null,
            ],
            // Futures — ICE Brent (Open)
            [
                'instrument_type'    => 'futures',
                'trade_date'         => '2026-02-10',
                'buy_sell'           => 'Sell',
                'counterparty'       => $total ?? $bp,
                'product'            => $brentProd,
                'currency'           => $usd,
                'portfolio'          => $crudeBook,
                'exchange'           => 'ICE',
                'contract_code'      => 'BRN',
                'expiry_date'        => '2026-05-31',
                'num_contracts'      => 10,
                'contract_size'      => 1000,
                'futures_price'      => 84.20,
                'margin_requirement' => 1500.00,
                'futures_index'      => $brentIdx,
                'broker'             => $broker,
                'trade_status'       => 'Open',
                'validated_by'       => $admin,
                'validated_at'       => '2026-02-11 10:00:00',
                'comments'           => 'Short hedge — 10 ICE Brent contracts May expiry',
            ],
            // Options — Call on Brent (Open)
            [
                'instrument_type'    => 'options',
                'trade_date'         => '2026-02-15',
                'buy_sell'           => 'Buy',
                'counterparty'       => $vitol ?? $bp,
                'product'            => $brentProd,
                'currency'           => $usd,
                'portfolio'          => $hedgeBook,
                'option_type'        => 'call',
                'exercise_style'     => 'European',
                'strike_price'       => 90.00,
                'option_expiry_date' => '2026-06-20',
                'premium'            => 2.75,
                'volatility'         => 0.2850,
                'underlying_index'   => $brentIdx,
                'broker'             => $broker,
                'trade_status'       => 'Open',
                'validated_by'       => $admin,
                'validated_at'       => '2026-02-16 08:45:00',
                'comments'           => 'Upside protection cap at $90',
            ],
            // Gas swap — NBP (Pending)
            [
                'instrument_type'    => 'swap',
                'trade_date'         => '2026-03-01',
                'buy_sell'           => 'Buy',
                'counterparty'       => $shell,
                'product'            => $gasProd ?? $brentProd,
                'currency'           => $gbp,
                'portfolio'          => $hedgeBook,
                'swap_type'          => 'commodity',
                'fixed_rate'         => 8.10,
                'float_index'        => $nbpIdx ?? $brentIdx,
                'notional_quantity'  => 50000,
                'uom'                => $mmbtu,
                'spread'             => 0.05,
                'payment_frequency'  => 'Monthly',
                'start_date'         => '2026-04-01',
                'end_date'           => '2026-09-30',
                'broker'             => $broker,
                'agreement'          => null,
                'trade_status'       => 'Pending',
                'validated_by'       => null,
                'validated_at'       => null,
                'comments'           => 'NBP summer gas hedge',
            ],
        ];

        $dealSeq = 1;
        $txnSeq  = 100; // offset from physical trades
        $instSeq = 100;

        foreach ($trades as $t) {
            $cp = $t['counterparty'];
            if (! $internalBu || ! $cp) continue;

            $portfolio = $t['portfolio'] ?? $hedgeBook;

            $base = [
                'deal_number'        => sprintf('FIN-%d-%04d', $year, $dealSeq++),
                'transaction_number' => sprintf('TXN-%d-%04d', $year, $txnSeq++),
                'instrument_number'  => sprintf('INST-%d-%04d', $year, $instSeq++),
                'version'            => 1,
                'instrument_type'    => $t['instrument_type'],
                'trade_status'       => $t['trade_status'],
                'trade_date'         => $t['trade_date'],
                'buy_sell'           => $t['buy_sell'],
                'pay_rec'            => FinancialTrade::derivePayRec($t['buy_sell']),
                'internal_bu_id'     => $internalBu->id,
                'portfolio_id'       => $portfolio->id,
                'counterparty_id'    => $cp->id,
                'product_id'         => ($t['product'] ?? $brentProd)?->id,
                'currency_id'        => ($t['currency'] ?? $usd)->id,
                'broker_id'          => ($t['broker'] ?? null)?->id,
                'agreement_id'       => ($t['agreement'] ?? null)?->id,
                'comments'           => $t['comments'] ?? null,
                'created_by'         => $trader1->id ?? $admin->id,
                'validated_by'       => ($t['validated_by'] ?? null)?->id,
                'validated_at'       => $t['validated_at'] ?? null,
            ];

            $specific = match ($t['instrument_type']) {
                'swap' => [
                    'swap_type'         => $t['swap_type'],
                    'fixed_rate'        => $t['fixed_rate'],
                    'float_index_id'    => ($t['float_index'] ?? null)?->id,
                    'notional_quantity' => $t['notional_quantity'],
                    'uom_id'            => ($t['uom'] ?? $bbl)?->id,
                    'spread'            => $t['spread'] ?? 0,
                    'payment_frequency' => $t['payment_frequency'],
                    'start_date'        => $t['start_date'],
                    'end_date'          => $t['end_date'],
                ],
                'futures' => [
                    'exchange'           => $t['exchange'],
                    'contract_code'      => $t['contract_code'],
                    'expiry_date'        => $t['expiry_date'],
                    'num_contracts'      => $t['num_contracts'],
                    'contract_size'      => $t['contract_size'],
                    'futures_price'      => $t['futures_price'],
                    'margin_requirement' => $t['margin_requirement'] ?? null,
                    'futures_index_id'   => ($t['futures_index'] ?? null)?->id,
                ],
                'options' => [
                    'option_type'         => $t['option_type'],
                    'exercise_style'      => $t['exercise_style'],
                    'strike_price'        => $t['strike_price'],
                    'option_expiry_date'  => $t['option_expiry_date'],
                    'premium'             => $t['premium'],
                    'volatility'          => $t['volatility'] ?? null,
                    'underlying_index_id' => ($t['underlying_index'] ?? null)?->id,
                ],
                default => [],
            };

            FinancialTrade::firstOrCreate(
                ['deal_number' => $base['deal_number']],
                array_merge($base, $specific)
            );
        }
    }
}
