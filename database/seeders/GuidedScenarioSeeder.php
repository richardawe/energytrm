<?php

namespace Database\Seeders;

use App\Models\GuidedScenario;
use Illuminate\Database\Seeder;

class GuidedScenarioSeeder extends Seeder
{
    public function run(): void
    {
        GuidedScenario::truncate();

        $scenarios = [
            [
                'title'       => 'Capture a Fixed-Price Physical Trade',
                'description' => 'Walk through the full deal capture process for a fixed-price crude oil trade, from setting up counterparty details to submission.',
                'module'      => 'trades',
                'sort_order'  => 1,
                'steps'       => [
                    [
                        'title'       => 'Navigate to the Trade Blotter',
                        'instruction' => 'Click "Physical Trades" in the top navigation. This is the Trade Blotter — the central grid showing all deals in the system. In a live ETRM like Endur, traders spend most of their day here.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'Open the New Trade form',
                        'instruction' => 'Click "+ New Trade" in the top-right. You will land on the Deal Capture screen. Notice that Deal Number, Transaction Number and Instrument Number are not shown — the system generates these on save.',
                        'route_name'  => 'trades.create',
                        'fields'      => ['trade_date', 'start_date', 'end_date'],
                    ],
                    [
                        'title'       => 'Set the deal identity',
                        'instruction' => 'Enter today\'s date as the Trade Date. Set a Delivery Start and End date (e.g., first and last day of next month). These define the physical delivery window, not the payment date.',
                        'route_name'  => 'trades.create',
                        'fields'      => ['trade_date', 'start_date', 'end_date'],
                    ],
                    [
                        'title'       => 'Select counterparties',
                        'instruction' => 'Choose your Internal BU (the trading desk executing the deal) and a Portfolio. Then select an external Counterparty. The system will warn you at save if this counterparty\'s credit limit would be exceeded.',
                        'route_name'  => 'trades.create',
                        'fields'      => ['internal_bu_id', 'portfolio_id', 'counterparty_id'],
                    ],
                    [
                        'title'       => 'Set Buy/Sell direction',
                        'instruction' => 'Select "Buy" (you are purchasing the commodity) or "Sell". Notice that Pay/Receive is automatically derived: Buy → Pay, Sell → Receive. This mirrors how Endur handles the payment leg of a physical trade.',
                        'route_name'  => 'trades.create',
                        'fields'      => ['buy_sell'],
                    ],
                    [
                        'title'       => 'Enter product and quantity',
                        'instruction' => 'Choose a Product (e.g., Crude Oil), enter the Quantity and select the Unit of Measure (e.g., BBL for barrels). Volume Type "Fixed" means the quantity is contractually firm.',
                        'route_name'  => 'trades.create',
                        'fields'      => ['product_id', 'quantity', 'uom_id', 'volume_type'],
                    ],
                    [
                        'title'       => 'Set pricing to Fixed',
                        'instruction' => 'Select "Fixed" for the Pricing Type, then enter a Fixed Price (e.g., 75.00 per barrel). For a Float trade you would instead select an Index, and the price would reference the latest market grid point.',
                        'route_name'  => 'trades.create',
                        'fields'      => ['fixed_float', 'fixed_price', 'currency_id'],
                    ],
                    [
                        'title'       => 'Save and review the assigned IDs',
                        'instruction' => 'Click "Capture Trade". The system assigns a permanent Deal Number (DL-YYYY-####), a Transaction Number (TXN-YYYY-####), and an Instrument Number. Trade Status is set to "Pending" — it must be validated before it affects downstream modules.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                ],
            ],

            [
                'title'       => 'Validate, Invoice and Settle a Trade',
                'description' => 'Follow the post-trade lifecycle: validate a pending trade, generate an invoice, record a settlement payment, and watch the trade status reach "Settled".',
                'module'      => 'operations',
                'sort_order'  => 2,
                'steps'       => [
                    [
                        'title'       => 'Find a Pending trade',
                        'instruction' => 'Go to Physical Trades and filter by Status = Pending. Open any trade and click "Validate Trade". Only Admin and Trader roles can validate. On validation the status moves to Validated and the trade becomes available to Operations.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'Create a Shipment',
                        'instruction' => 'Go to Operations → Shipments → New Shipment. Link it to the validated trade. The Load Port, Discharge Port and Incoterm pre-populate from the trade. Enter the actual delivered quantity and set status to "Discharged".',
                        'route_name'  => 'operations.shipments.create',
                        'fields'      => ['trade_id', 'qty_delivered', 'delivery_status'],
                    ],
                    [
                        'title'       => 'Generate an Invoice',
                        'instruction' => 'Go to Operations → Invoices. Click "New Invoice from Trade" next to your validated trade. Invoice Amount is calculated automatically: Quantity × Price. Review and save.',
                        'route_name'  => 'operations.invoices.index',
                        'fields'      => ['invoice_amount', 'invoice_date'],
                    ],
                    [
                        'title'       => 'Record the Settlement',
                        'instruction' => 'Open the invoice and click "Record Settlement". Enter the payment amount and date. Set status to "Confirmed" when the payment is received. Invoice Amount and Settlement Amount should match.',
                        'route_name'  => 'operations.invoices.index',
                        'fields'      => ['payment_amount', 'payment_date', 'settlement_status'],
                    ],
                    [
                        'title'       => 'Trade reaches Settled',
                        'instruction' => 'When the full invoice amount is covered by confirmed settlements, return to the trade and observe the status has moved to "Settled". Settled trades are locked — no further amendments are permitted.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                ],
            ],

            [
                'title'       => 'Monitor Counterparty Credit Risk',
                'description' => 'Set a credit limit on a counterparty, capture a trade that breaches it, and review the Counterparty Exposure screen.',
                'module'      => 'risk',
                'sort_order'  => 3,
                'steps'       => [
                    [
                        'title'       => 'Set a credit limit on a counterparty',
                        'instruction' => 'Go to Master Data → Parties. Open an external Legal Entity and edit it. Enter a Credit Limit (e.g., 500,000) and select the Credit Limit Currency. This represents the maximum exposure you are willing to hold with this counterparty.',
                        'route_name'  => 'master.parties.index',
                        'fields'      => ['credit_limit', 'credit_limit_currency_id'],
                    ],
                    [
                        'title'       => 'Capture a large trade with that counterparty',
                        'instruction' => 'Go to Physical Trades → New Trade. Select the counterparty you just configured. Enter a quantity and price such that Qty × Price exceeds the credit limit. Save the trade.',
                        'route_name'  => 'trades.create',
                        'fields'      => ['counterparty_id', 'quantity', 'fixed_price'],
                    ],
                    [
                        'title'       => 'Observe the breach warning',
                        'instruction' => 'After saving, a yellow warning banner appears: "Credit limit breach — exposure exceeds limit." This is a soft warning — the trade is captured but the risk team must review. In a production ETRM a hard block or approval workflow would be triggered.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'Review the Counterparty Exposure screen',
                        'instruction' => 'Go to Risk & Analytics → Counterparty Exposure. The breached counterparty appears with a red "BREACH" badge and a filled utilisation bar. Rows above 80% utilisation show "Near Limit" in amber. Credit limits are set in Master Data.',
                        'route_name'  => 'risk.counterparty-exposure',
                        'fields'      => [],
                    ],
                ],
            ],

            [
                'title'       => 'Understand the Three-ID Trade Structure',
                'description' => 'Learn how Deal Number, Transaction Number and Instrument Number work together and what happens when a trade is amended.',
                'module'      => 'trades',
                'sort_order'  => 4,
                'steps'       => [
                    [
                        'title'       => 'The three identifiers explained',
                        'instruction' => 'Every trade has three IDs. The Deal Number (DL-YYYY-####) is permanent — it never changes. The Transaction Number (TXN-YYYY-####) is reissued on every amendment. The Instrument Number (INST-YYYY-####) is shared across OTC duplicate legs of the same economic transaction.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'Amend a Pending trade',
                        'instruction' => 'Open a Pending trade and click "Edit". Change the quantity or price and save. Notice the Transaction Number has changed (new TXN-YYYY-####), but the Deal Number is identical to before. The version counter has also incremented.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'Amend a Validated trade',
                        'instruction' => 'Open a Validated trade and click "Edit". Make a change and save. The trade is automatically reverted to Pending (it must be re-validated) and a new Transaction Number is issued. This models the amendment workflow in real-world ETRMs.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'View the audit trail',
                        'instruction' => 'Open a trade that has been amended and scroll to the Audit Trail section. Every action — captured, amended, validated, reverted — is logged with the user, timestamp and changed values. This is the primary evidence trail for trade operations.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                ],
            ],

            [
                'title'       => 'Capture a Float Gas Trade with Pipeline Path',
                'description' => 'Capture a floating-price natural gas trade referencing the TTF index, linked to a specific pipeline zone and delivery location. Observe how Reference Source, Price Unit, and the Pipeline cascade fields work.',
                'module'      => 'trades',
                'sort_order'  => 5,
                'steps'       => [
                    [
                        'title'       => 'Open a new trade for Natural Gas',
                        'instruction' => 'Go to Physical Trades → New Trade. Select product "TTF Natural Gas". Notice that once a product is selected, the Index dropdown in the Pricing section filters to show only indices linked to that commodity. This prevents accidental cross-commodity mis-pricing.',
                        'route_name'  => 'trades.create',
                        'fields'      => ['product_id'],
                    ],
                    [
                        'title'       => 'Set pricing to Float and choose the TTF index',
                        'instruction' => 'Set Fixed/Float to "Float". Select "TTF Day-Ahead" as the index and enter a spread (e.g. +0.05). Set Reference Source to "Heren" — this identifies which publisher\'s price applies on the fixing date, a critical audit field in real ETRM systems.',
                        'route_name'  => 'trades.create',
                        'fields'      => ['fixed_float', 'index_id', 'spread', 'reference_source'],
                    ],
                    [
                        'title'       => 'Set Price Unit separately from Volume UOM',
                        'instruction' => 'Set UOM to MMBTU (the contracted volume unit). Set Price Unit to GJ — when the published price is in $/GJ but volume is in MMBTU, you need both fields to ensure accurate invoicing. If Price Unit is left blank it defaults to the same UOM.',
                        'route_name'  => 'trades.create',
                        'fields'      => ['uom_id', 'price_unit_id'],
                    ],
                    [
                        'title'       => 'Select the pipeline delivery path',
                        'instruction' => 'In the Logistics section, select pipeline "TTF". The Zone dropdown populates automatically with TTF zones. Select zone "NL — Netherlands Virtual Hub", then select location "TTF-VH" (the virtual hub). Enter a Fuel % of 0 (gas hubs have no physical shrinkage).',
                        'route_name'  => 'trades.create',
                        'fields'      => ['pipeline_id', 'zone_id', 'location_id', 'fuel_percent'],
                    ],
                    [
                        'title'       => 'Save and verify the full trade record',
                        'instruction' => 'Capture the trade. On the show page, verify: Pricing section shows Index + Spread + Reference Source; Logistics section shows the full Pipeline → Zone → Location path. These fields will pre-populate the nomination and shipment when an operational event is created.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                ],
            ],

            [
                'title'       => 'Hedge a Physical Float Trade with a Swap',
                'description' => 'Link a floating-price physical gas trade to a commodity swap, then observe the combined hedge position on each trade\'s detail page and in the P&L view.',
                'module'      => 'financials',
                'sort_order'  => 6,
                'steps'       => [
                    [
                        'title'       => 'Identify the physical float trade to hedge',
                        'instruction' => 'Go to Physical Trades and find a float-priced trade (Fixed/Float = Float) that is Validated or Active. Note its product, currency, notional quantity and index. These will define the terms of the offsetting swap.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'Capture the offsetting swap',
                        'instruction' => 'Go to Financials → Financial Trades → New Financial Trade. Select instrument type "Swap". Set the same product and currency as the physical trade. If the physical is a Buy (you pay floating), the swap should be Buy too — you receive floating from the swap to offset. Set Fixed Rate = the price you want to lock in. Set Float Index = the same index as the physical trade.',
                        'route_name'  => 'financials.financial-trades.create',
                        'fields'      => ['instrument_type', 'buy_sell', 'fixed_rate', 'float_index_id', 'notional_quantity'],
                    ],
                    [
                        'title'       => 'Link the swap back to the physical trade',
                        'instruction' => 'On the physical trade Edit page, scroll to the "Hedge Link" panel. Select the swap you just created from the "Hedged By Financial Trade" dropdown. Save the amendment. Both trades now reference each other.',
                        'route_name'  => 'trades.index',
                        'fields'      => ['hedged_by_financial_trade_id'],
                    ],
                    [
                        'title'       => 'View the hedge panel on the physical trade',
                        'instruction' => 'Open the physical trade. The right column now shows a "Hedge" panel with the swap\'s deal number, status, and live Swap MTM. The MTM should move opposite to the physical unrealised P&L — this is the hedge working. Click "View Hedge →" to jump to the financial trade.',
                        'route_name'  => 'trades.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'View the hedged position on the financial trade',
                        'instruction' => 'Open the financial trade. The right column shows a "Hedged Physical Trade" panel with the physical deal number, product, quantity, delivery period, and direction. A real trader uses this cross-reference to confirm the hedge is sized correctly against the physical exposure.',
                        'route_name'  => 'financials.financial-trades.index',
                        'fields'      => [],
                    ],
                ],
            ],

            [
                'title'       => 'Analyse Combined Physical and Financial P&L',
                'description' => 'Use the P&L view to see physical MTM, swap MTM and net hedge effectiveness side by side. Understand how the hedge offsets price risk on a float physical position.',
                'module'      => 'financials',
                'sort_order'  => 7,
                'steps'       => [
                    [
                        'title'       => 'Navigate to the P&L View',
                        'instruction' => 'Go to Financials → P&L View. The table shows both physical trades and financial instruments together. Physical rows show unrealised P&L based on (Market Price − Trade Price) × Quantity × direction. Financial rows show MTM (swap) or unrealised PnL (futures/options).',
                        'route_name'  => 'financials.pnl.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'Filter to a single product to focus the view',
                        'instruction' => 'Use the Product filter to select e.g. "TTF Natural Gas". You should now see only the physical gas trade and its linked swap. Observe the Unrealised P&L column — if the physical trade is a Buy-Float and price has risen, it shows a positive unrealised gain. The swap MTM should be negative (you receive float but pay fixed at a lower rate). The net should be near zero: the hedge is working.',
                        'route_name'  => 'financials.pnl.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'Update the market price to shift MTM',
                        'instruction' => 'Go to Financials → Market Prices and enter a new price for TTF Day-Ahead — move it significantly (e.g. raise it by $2). Return to P&L View and observe: the physical Buy-Float unrealised P&L increases (market rose), while the swap MTM becomes more negative (you\'re locked into a fixed rate below market). The total row shows that gains and losses roughly cancel — net exposure is hedged.',
                        'route_name'  => 'financials.market-prices.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'Observe realised P&L on settlement',
                        'instruction' => 'When a swap settlement is confirmed (Financials → Financial Trades → open the swap → + Settlement → Confirmed), the settled amount appears in the Realised P&L column. At physical settlement, the invoice and payment flow through Operations. In a production ETRM these would be reconciled in the General Ledger.',
                        'route_name'  => 'financials.pnl.index',
                        'fields'      => [],
                    ],
                ],
            ],

            [
                'title'       => 'Enter Market Prices and View P&L',
                'description' => 'Enter index price data and observe the effect on float-priced trade MTM and unrealised P&L.',
                'module'      => 'financials',
                'sort_order'  => 8,
                'steps'       => [
                    [
                        'title'       => 'Navigate to Market Prices',
                        'instruction' => 'Go to Financials → Market Prices. This screen lists all Authorized indices defined in Master Data. Click "Enter Price" next to an index to open its price history.',
                        'route_name'  => 'financials.market-prices.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'Enter a new price point',
                        'instruction' => 'Enter today\'s date and a price. Click "Save Price". If a price already exists for that date it will be overwritten (updateOrCreate). In a production system these prices would arrive via a market data feed (e.g., Bloomberg, Platts).',
                        'route_name'  => 'financials.market-prices.index',
                        'fields'      => ['price_date', 'price'],
                    ],
                    [
                        'title'       => 'Observe MTM impact on P&L',
                        'instruction' => 'Go to Financials → P&L View. For any float-priced trade linked to this index, the Market Price column now shows the price you just entered. Unrealised P&L = (Market − Trade Price) × Qty × direction. A Buy trade gains value when market price rises.',
                        'route_name'  => 'financials.pnl.index',
                        'fields'      => [],
                    ],
                    [
                        'title'       => 'Check VaR impact',
                        'instruction' => 'Go to Risk & Analytics → VaR & Stress Tests. The Historical VaR calculation uses the price history you have entered. At least 30 data points are needed for a meaningful VaR figure. Stress tests work immediately regardless of history length.',
                        'route_name'  => 'risk.var',
                        'fields'      => [],
                    ],
                ],
            ],
        ];

        foreach ($scenarios as $s) {
            GuidedScenario::create($s);
        }

        $this->command->info('Guided scenarios seeded: ' . count($scenarios));
    }
}
