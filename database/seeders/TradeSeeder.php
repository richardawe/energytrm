<?php

namespace Database\Seeders;

use App\Models\Agreement;
use App\Models\Broker;
use App\Models\Currency;
use App\Models\IndexDefinition;
use App\Models\Incoterm;
use App\Models\Party;
use App\Models\PaymentTerm;
use App\Models\Portfolio;
use App\Models\Product;
use App\Models\Trade;
use App\Models\Uom;
use App\Models\User;
use Illuminate\Database\Seeder;

class TradeSeeder extends Seeder
{
    public function run(): void
    {
        $admin     = User::where('email', 'admin@energytrm.com')->first();
        $trader1   = User::where('email', 'trader1@energytrm.com')->first() ?? $admin;
        $trader2   = User::where('email', 'trader2@energytrm.com')->first() ?? $admin;

        $internalBu  = Party::where('internal_external', 'Internal')->where('party_type', 'BU')->first();
        $portfolio   = Portfolio::first();
        $usdCcy      = Currency::where('code', 'USD')->first();
        $eurCcy      = Currency::where('code', 'EUR')->first() ?? $usdCcy;
        $mtuom       = Uom::where('code', 'MT')->first();
        $bbluom      = Uom::where('code', 'BBL')->first() ?? $mtuom;
        $payTerms    = PaymentTerm::first();
        $broker      = Broker::first();
        $agreement   = Agreement::first();
        $index       = IndexDefinition::first();
        $incoterm    = Incoterm::first();

        // Get counterparties by short name
        $bpParty       = Party::where('short_name', 'like', '%BP%')->where('internal_external', 'External')->first();
        $shellParty    = Party::where('short_name', 'like', '%Shell%')->where('internal_external', 'External')->first();
        $totalParty    = Party::where('short_name', 'like', '%Total%')->where('internal_external', 'External')->first();
        $vitolParty    = Party::where('short_name', 'like', '%Vitol%')->where('internal_external', 'External')->first();
        $trafiParty    = Party::where('short_name', 'like', '%Trafigura%')->where('internal_external', 'External')->first();

        $crudeProd  = Product::where('name', 'like', '%Crude%')->first();
        $fuelProd   = Product::where('name', 'like', '%Fuel%')->first() ?? $crudeProd;
        $gasProd    = Product::where('name', 'like', '%Gas%')->first() ?? $crudeProd;

        $trades = [
            [
                'trade_date'       => '2026-01-15',
                'buy_sell'         => 'Buy',
                'start_date'       => '2026-02-01',
                'end_date'         => '2026-02-28',
                'counterparty'     => $bpParty,
                'product'          => $crudeProd,
                'quantity'         => 100000,
                'uom'              => $bbluom,
                'fixed_float'      => 'Fixed',
                'fixed_price'      => 82.50,
                'currency'         => $usdCcy,
                'incoterm_code'    => $incoterm?->code,
                'load_port'        => 'Rotterdam',
                'discharge_port'   => 'Singapore',
                'trade_status'     => 'Validated',
                'validated_by'     => $admin,
                'validated_at'     => '2026-01-16 09:00:00',
                'created_by'       => $trader1,
            ],
            [
                'trade_date'       => '2026-01-20',
                'buy_sell'         => 'Sell',
                'start_date'       => '2026-02-15',
                'end_date'         => '2026-03-15',
                'counterparty'     => $shellParty,
                'product'          => $crudeProd,
                'quantity'         => 75000,
                'uom'              => $bbluom,
                'fixed_float'      => 'Float',
                'index_id'         => $index?->id,
                'spread'           => 1.25,
                'currency'         => $usdCcy,
                'trade_status'     => 'Validated',
                'validated_by'     => $admin,
                'validated_at'     => '2026-01-21 10:30:00',
                'broker_id'        => $broker?->id,
                'created_by'       => $trader1,
            ],
            [
                'trade_date'       => '2026-02-03',
                'buy_sell'         => 'Buy',
                'start_date'       => '2026-03-01',
                'end_date'         => '2026-03-31',
                'counterparty'     => $totalParty,
                'product'          => $fuelProd,
                'quantity'         => 5000,
                'uom'              => $mtuom,
                'fixed_float'      => 'Fixed',
                'fixed_price'      => 620.00,
                'currency'         => $usdCcy,
                'payment_terms_id' => $payTerms?->id,
                'trade_status'     => 'Pending',
                'created_by'       => $trader2,
            ],
            [
                'trade_date'       => '2026-02-10',
                'buy_sell'         => 'Sell',
                'start_date'       => '2026-03-15',
                'end_date'         => '2026-04-14',
                'counterparty'     => $vitolParty,
                'product'          => $gasProd,
                'quantity'         => 10000,
                'uom'              => $mtuom,
                'fixed_float'      => 'Float',
                'index_id'         => $index?->id,
                'spread'           => -0.50,
                'currency'         => $eurCcy,
                'trade_status'     => 'Pending',
                'broker_id'        => $broker?->id,
                'agreement_id'     => $agreement?->id,
                'created_by'       => $trader2,
            ],
            [
                'trade_date'       => '2026-02-18',
                'buy_sell'         => 'Buy',
                'start_date'       => '2026-04-01',
                'end_date'         => '2026-04-30',
                'counterparty'     => $trafiParty,
                'product'          => $crudeProd,
                'quantity'         => 200000,
                'uom'              => $bbluom,
                'fixed_float'      => 'Fixed',
                'fixed_price'      => 79.75,
                'currency'         => $usdCcy,
                'incoterm_code'    => $incoterm?->code,
                'load_port'        => 'Basra',
                'discharge_port'   => 'Rotterdam',
                'payment_terms_id' => $payTerms?->id,
                'trade_status'     => 'Settled',
                'validated_by'     => $admin,
                'validated_at'     => '2026-02-19 08:00:00',
                'created_by'       => $trader1,
            ],
        ];

        $year = 2026;
        $dealSeq = 1;
        $txnSeq  = 1;
        $instSeq = 1;

        foreach ($trades as $t) {
            $cp = $t['counterparty'];
            $pr = $t['product'];

            if (! $internalBu || ! $portfolio || ! $cp || ! $pr) {
                continue;
            }

            Trade::create([
                'deal_number'       => sprintf('DL-%d-%04d',   $year, $dealSeq++),
                'transaction_number'=> sprintf('TXN-%d-%04d',  $year, $txnSeq++),
                'instrument_number' => sprintf('INST-%d-%04d', $year, $instSeq++),
                'version'           => 1,
                'trade_status'      => $t['trade_status'],
                'trade_date'        => $t['trade_date'],
                'buy_sell'          => $t['buy_sell'],
                'pay_rec'           => Trade::derivePayRec($t['buy_sell']),
                'start_date'        => $t['start_date'],
                'end_date'          => $t['end_date'],
                'internal_bu_id'    => $internalBu->id,
                'portfolio_id'      => $portfolio->id,
                'counterparty_id'   => $cp->id,
                'product_id'        => $pr->id,
                'quantity'          => $t['quantity'],
                'volume_type'       => 'Fixed',
                'uom_id'            => ($t['uom'] ?? $mtuom)?->id,
                'fixed_float'       => $t['fixed_float'],
                'index_id'          => $t['index_id'] ?? null,
                'fixed_price'       => $t['fixed_price'] ?? null,
                'spread'            => $t['spread'] ?? 0,
                'currency_id'       => ($t['currency'] ?? $usdCcy)->id,
                'payment_terms_id'  => $t['payment_terms_id'] ?? null,
                'incoterm_code'     => $t['incoterm_code'] ?? null,
                'load_port'         => $t['load_port'] ?? null,
                'discharge_port'    => $t['discharge_port'] ?? null,
                'broker_id'         => $t['broker_id'] ?? null,
                'agreement_id'      => $t['agreement_id'] ?? null,
                'comments'          => null,
                'created_by'        => ($t['created_by'] ?? $admin)->id,
                'validated_by'      => ($t['validated_by'] ?? null)?->id,
                'validated_at'      => $t['validated_at'] ?? null,
            ]);
        }
    }
}
