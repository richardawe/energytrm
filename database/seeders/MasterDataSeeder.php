<?php
namespace Database\Seeders;

use App\Models\Agreement;
use App\Models\Broker;
use App\Models\BrokerCommission;
use App\Models\Currency;
use App\Models\Incoterm;
use App\Models\IndexDefinition;
use App\Models\IndexGridPoint;
use App\Models\Party;
use App\Models\PaymentTerm;
use App\Models\Portfolio;
use App\Models\Product;
use App\Models\TransportClass;
use App\Models\Uom;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Currencies ────────────────────────────────────────────────────────
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar',      'symbol' => '$',  'fx_rate_to_usd' => 1.000000],
            ['code' => 'EUR', 'name' => 'Euro',            'symbol' => '€',  'fx_rate_to_usd' => 0.920000],
            ['code' => 'GBP', 'name' => 'British Pound',   'symbol' => '£',  'fx_rate_to_usd' => 0.790000],
            ['code' => 'JPY', 'name' => 'Japanese Yen',    'symbol' => '¥',  'fx_rate_to_usd' => 149.500000],
            ['code' => 'SGD', 'name' => 'Singapore Dollar','symbol' => 'S$', 'fx_rate_to_usd' => 1.340000],
        ];
        foreach ($currencies as $c) Currency::firstOrCreate(['code' => $c['code']], $c + ['is_active' => true]);
        $usd = Currency::where('code', 'USD')->first();

        // ── Payment Terms ─────────────────────────────────────────────────────
        $terms = [
            ['name' => 'Net 30', 'days_net' => 30, 'description' => 'Payment due 30 days from invoice date'],
            ['name' => 'Net 60', 'days_net' => 60, 'description' => 'Payment due 60 days from invoice date'],
            ['name' => 'Net 5',  'days_net' => 5,  'description' => 'Standard crude oil payment terms'],
            ['name' => 'Upon Delivery', 'days_net' => 0, 'description' => 'Cash on delivery'],
            ['name' => 'Net 14', 'days_net' => 14, 'description' => 'LNG standard terms'],
        ];
        foreach ($terms as $t) PaymentTerm::firstOrCreate(['name' => $t['name']], $t + ['is_active' => true]);
        $net30 = PaymentTerm::where('name', 'Net 30')->first();

        // ── Incoterms ─────────────────────────────────────────────────────────
        $incoterms = [
            ['code' => 'FOB',  'name' => 'Free On Board',              'description' => 'Seller bears all costs/risks up to loading on vessel'],
            ['code' => 'CIF',  'name' => 'Cost, Insurance & Freight',  'description' => 'Seller pays freight and insurance to destination port'],
            ['code' => 'CFR',  'name' => 'Cost and Freight',           'description' => 'Seller pays freight to destination port, buyer bears insurance'],
            ['code' => 'DES',  'name' => 'Delivered Ex Ship',         'description' => 'Seller bears all costs to destination port, unloading excluded'],
            ['code' => 'DAP',  'name' => 'Delivered At Place',        'description' => 'Seller bears all costs to named destination'],
            ['code' => 'EXW',  'name' => 'Ex Works',                  'description' => 'Buyer bears all transport costs and risks from seller premises'],
            ['code' => 'FAS',  'name' => 'Free Alongside Ship',       'description' => 'Seller delivers goods to quay alongside vessel'],
        ];
        foreach ($incoterms as $i) Incoterm::firstOrCreate(['code' => $i['code']], $i + ['is_active' => true]);

        // ── Transport Classes ─────────────────────────────────────────────────
        foreach (['Vessel', 'Pipeline', 'Barge', 'Rail', 'Truck', 'TBD'] as $name) {
            TransportClass::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        // ── UOMs ─────────────────────────────────────────────────────────────
        $uoms = [
            ['code' => 'BBL',   'description' => 'Barrels',              'conversion_factor' => 1.0,        'base_unit' => 'BBL'],
            ['code' => 'MT',    'description' => 'Metric Tonnes',        'conversion_factor' => 7.33,       'base_unit' => 'BBL'],
            ['code' => 'MMBTU', 'description' => 'Million BTU',          'conversion_factor' => 1.0,        'base_unit' => 'MMBTU'],
            ['code' => 'MWh',   'description' => 'Megawatt Hour',        'conversion_factor' => 3.412142,   'base_unit' => 'MMBTU'],
            ['code' => 'GJ',    'description' => 'Gigajoule',            'conversion_factor' => 0.947817,   'base_unit' => 'MMBTU'],
            ['code' => 'MMcf',  'description' => 'Million Cubic Feet',   'conversion_factor' => 1.02,       'base_unit' => 'MMBTU'],
            ['code' => 'Lot',   'description' => 'Lot / Contract',       'conversion_factor' => 1.0,        'base_unit' => null],
        ];
        foreach ($uoms as $u) Uom::firstOrCreate(['code' => $u['code']], $u + ['is_active' => true]);
        $bbl   = Uom::where('code', 'BBL')->first();
        $mmbtu = Uom::where('code', 'MMBTU')->first();
        $mwh   = Uom::where('code', 'MWh')->first();

        // ── Products ─────────────────────────────────────────────────────────
        $products = [
            ['name' => 'Brent Crude Oil',    'commodity_type' => 'Oil',   'default_uom_id' => $bbl->id],
            ['name' => 'WTI Crude Oil',      'commodity_type' => 'Oil',   'default_uom_id' => $bbl->id],
            ['name' => 'Gasoil',             'commodity_type' => 'Oil',   'default_uom_id' => $bbl->id],
            ['name' => 'LNG',                'commodity_type' => 'LNG',   'default_uom_id' => $mmbtu->id],
            ['name' => 'TTF Natural Gas',    'commodity_type' => 'Gas',   'default_uom_id' => $mmbtu->id],
            ['name' => 'NBP Natural Gas',    'commodity_type' => 'Gas',   'default_uom_id' => $mmbtu->id],
            ['name' => 'UK Power (Baseload)','commodity_type' => 'Power', 'default_uom_id' => $mwh->id],
            ['name' => 'EU Power (Baseload)','commodity_type' => 'Power', 'default_uom_id' => $mwh->id],
        ];
        foreach ($products as $p) Product::firstOrCreate(['name' => $p['name']], $p + ['status' => 'Authorized']);

        // ── Parties: Internal hierarchy ───────────────────────────────────────
        $group = Party::firstOrCreate(['short_name' => 'ETRM_GRP'], [
            'party_type' => 'Group', 'internal_external' => 'Internal', 'long_name' => 'EnergyTRM Holdings Group',
            'status' => 'Authorized', 'version' => 0,
        ]);
        $le = Party::firstOrCreate(['short_name' => 'ETRM_LE'], [
            'party_type' => 'LE', 'internal_external' => 'Internal', 'parent_id' => $group->id,
            'long_name' => 'EnergyTRM Trading Ltd', 'status' => 'Authorized', 'version' => 0,
        ]);
        $buCrude = Party::firstOrCreate(['short_name' => 'CRUDE_TRD'], [
            'party_type' => 'BU', 'internal_external' => 'Internal', 'parent_id' => $le->id,
            'long_name' => 'Crude Oil Trading', 'status' => 'Authorized', 'version' => 0,
        ]);
        $buGas = Party::firstOrCreate(['short_name' => 'GAS_TRD'], [
            'party_type' => 'BU', 'internal_external' => 'Internal', 'parent_id' => $le->id,
            'long_name' => 'Gas & Power Trading', 'status' => 'Authorized', 'version' => 0,
        ]);
        $buRisk = Party::firstOrCreate(['short_name' => 'RISK_MGMT'], [
            'party_type' => 'BU', 'internal_external' => 'Internal', 'parent_id' => $le->id,
            'long_name' => 'Risk Management', 'status' => 'Authorized', 'version' => 0,
        ]);

        // ── Parties: External counterparties ──────────────────────────────────
        $counterparties = [
            ['short_name' => 'BP',         'long_name' => 'BP p.l.c.'],
            ['short_name' => 'SHELL',      'long_name' => 'Shell plc'],
            ['short_name' => 'TOTAL',      'long_name' => 'TotalEnergies SE'],
            ['short_name' => 'VITOL',      'long_name' => 'Vitol Group'],
            ['short_name' => 'TRAFIGURA',  'long_name' => 'Trafigura Group Pte Ltd'],
            ['short_name' => 'GLENCORE',   'long_name' => 'Glencore plc'],
            ['short_name' => 'GUNVOR',     'long_name' => 'Gunvor Group Ltd'],
            ['short_name' => 'MERCURIA',   'long_name' => 'Mercuria Energy Group'],
            ['short_name' => 'FREEPOINT',  'long_name' => 'Freepoint Commodities LLC'],
            ['short_name' => 'CASTLETON',  'long_name' => 'Castleton Commodities International'],
        ];
        foreach ($counterparties as $cp) {
            Party::firstOrCreate(['short_name' => $cp['short_name']], array_merge($cp, [
                'party_type' => 'BU', 'internal_external' => 'External',
                'status' => 'Authorized', 'version' => 0,
                'credit_limit' => rand(50, 500) * 1000000,
                'credit_limit_currency_id' => $usd->id,
                'kyc_status' => 'Approved',
                'kyc_review_date' => now()->addYear()->toDateString(),
            ]));
        }

        // ── Portfolios ────────────────────────────────────────────────────────
        Portfolio::firstOrCreate(['name' => 'CRUDE_BOOK_A'], ['business_unit_id' => $buCrude->id, 'is_restricted' => false, 'status' => 'Authorized']);
        Portfolio::firstOrCreate(['name' => 'CRUDE_BOOK_B'], ['business_unit_id' => $buCrude->id, 'is_restricted' => false, 'status' => 'Authorized']);
        Portfolio::firstOrCreate(['name' => 'GAS_BOOK'],     ['business_unit_id' => $buGas->id,   'is_restricted' => false, 'status' => 'Authorized']);
        Portfolio::firstOrCreate(['name' => 'POWER_BOOK'],   ['business_unit_id' => $buGas->id,   'is_restricted' => false, 'status' => 'Authorized']);
        Portfolio::firstOrCreate(['name' => 'HEDGE_BOOK'],   ['business_unit_id' => $buRisk->id,  'is_restricted' => true,  'status' => 'Authorized']);

        // ── Agreements ────────────────────────────────────────────────────────
        $bpParty = Party::where('short_name', 'BP')->first();
        $shellParty = Party::where('short_name', 'SHELL')->first();
        Agreement::firstOrCreate(['name' => 'ISDA MA — BP'], ['internal_party_id' => $le->id, 'counterparty_id' => $bpParty->id, 'payment_terms_id' => $net30->id, 'effective_date' => '2020-01-01', 'status' => 'Authorized']);
        Agreement::firstOrCreate(['name' => 'ISDA MA — Shell'], ['internal_party_id' => $le->id, 'counterparty_id' => $shellParty->id, 'payment_terms_id' => $net30->id, 'effective_date' => '2019-06-01', 'status' => 'Authorized']);

        // ── Brokers ───────────────────────────────────────────────────────────
        $brokerData = [
            ['name' => 'ICAP',      'short_name' => 'ICAP',  'broker_type' => 'Voice', 'is_regulated' => true],
            ['name' => 'Marex',     'short_name' => 'MRX',   'broker_type' => 'Voice', 'is_regulated' => true],
            ['name' => 'Tradition', 'short_name' => 'TRAD',  'broker_type' => 'Voice', 'is_regulated' => true],
        ];
        foreach ($brokerData as $bd) {
            $broker = Broker::firstOrCreate(['name' => $bd['name']], $bd + ['status' => 'Active', 'version' => 0]);
            BrokerCommission::firstOrCreate(['broker_id' => $broker->id, 'name' => 'Standard - Crude'], [
                'commission_rate' => 0.05, 'rate_unit' => 'per MT', 'currency_id' => $usd->id,
                'payment_frequency' => 'Monthly', 'min_fee' => 500, 'max_fee' => 50000, 'is_default' => true,
            ]);
        }

        // ── Indices / Curves ──────────────────────────────────────────────────
        $indices = [
            ['index_name' => 'Brent 1M',         'market' => 'Crude Oil',    'format' => 'Monthly', 'base_currency_id' => $usd->id, 'uom_id' => $bbl->id,   'base_prices' => 85.00],
            ['index_name' => 'WTI 1M',           'market' => 'Crude Oil',    'format' => 'Monthly', 'base_currency_id' => $usd->id, 'uom_id' => $bbl->id,   'base_prices' => 82.00],
            ['index_name' => 'TTF Day-Ahead',     'market' => 'Natural Gas',  'format' => 'Daily',   'base_currency_id' => $usd->id, 'uom_id' => $mmbtu->id, 'base_prices' => 8.50],
            ['index_name' => 'NBP Day-Ahead',     'market' => 'Natural Gas',  'format' => 'Daily',   'base_currency_id' => $usd->id, 'uom_id' => $mmbtu->id, 'base_prices' => 8.20],
            ['index_name' => 'UK Power Baseload', 'market' => 'Power',        'format' => 'Monthly', 'base_currency_id' => $usd->id, 'uom_id' => $mwh->id,   'base_prices' => 95.00],
        ];

        foreach ($indices as $idx) {
            $basePrice = $idx['base_prices'];
            unset($idx['base_prices']);
            $index = IndexDefinition::firstOrCreate(
                ['index_name' => $idx['index_name']],
                $idx + ['status' => 'Official', 'rec_status' => 'Authorized', 'version' => 0]
            );

            // Seed 18 months of price data with realistic variation
            for ($m = -6; $m <= 12; $m++) {
                $priceDate = now()->startOfMonth()->addMonths($m)->format('Y-m-d');
                $price = $basePrice * (1 + (rand(-800, 800) / 10000));
                IndexGridPoint::firstOrCreate(
                    ['index_id' => $index->id, 'price_date' => $priceDate],
                    ['price' => round($price, 4)]
                );
            }
        }
    }
}
