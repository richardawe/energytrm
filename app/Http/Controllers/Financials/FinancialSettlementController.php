<?php

namespace App\Http\Controllers\Financials;

use App\Http\Controllers\Controller;
use App\Models\FinancialSettlement;
use App\Models\FinancialTrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialSettlementController extends Controller
{
    public function create(FinancialTrade $financialTrade)
    {
        if (!in_array($financialTrade->trade_status, ['Active', 'Open'])) {
            return redirect()->route('financials.financial-trades.show', $financialTrade)
                ->with('error', 'Settlements can only be added to Active or Open trades.');
        }

        return view('financials.financial-trades.settlements.create', [
            'trade' => $financialTrade,
        ]);
    }

    public function store(Request $request, FinancialTrade $financialTrade)
    {
        $data = $request->validate([
            'settlement_type'  => 'required|in:periodic,final,margin,premium',
            'period_start'     => 'nullable|date',
            'period_end'       => 'nullable|date|after_or_equal:period_start',
            'fixed_leg_amount' => 'nullable|numeric',
            'float_leg_amount' => 'nullable|numeric',
            'net_amount'       => 'required|numeric',
            'settlement_date'  => 'required|date',
            'settlement_status'=> 'required|in:Pending,Confirmed',
            'fx_rate'          => 'required|numeric|min:0.000001',
            'bank_ref'         => 'nullable|string|max:100',
            'comments'         => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data, $financialTrade) {
            $data['settlement_number']  = FinancialSettlement::nextSettlementNumber();
            $data['financial_trade_id'] = $financialTrade->id;
            $data['created_by']         = auth()->id();

            FinancialSettlement::create($data);

            // Final confirmed settlement → close/settle the trade
            if ($data['settlement_type'] === 'final' && $data['settlement_status'] === 'Confirmed') {
                $financialTrade->update(['trade_status' => 'Settled']);
            }
        });

        return redirect()->route('financials.financial-trades.show', $financialTrade)
            ->with('success', 'Settlement recorded.');
    }
}
