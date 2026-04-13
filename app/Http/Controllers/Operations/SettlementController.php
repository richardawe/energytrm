<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Settlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    public function create(Invoice $invoice)
    {
        if ($invoice->invoice_status === 'Paid') {
            return redirect()->route('operations.invoices.show', $invoice)
                ->with('error', 'Invoice is already fully paid.');
        }
        return view('operations.settlements.create', compact('invoice'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'payment_amount'    => 'required|numeric|min:0.01',
            'payment_date'      => 'required|date',
            'fx_rate'           => 'required|numeric|min:0.000001',
            'bank_ref'          => 'nullable|string|max:100',
            'settlement_status' => 'required|in:Pending,Confirmed',
            'comments'          => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data, $invoice) {
            $data['settlement_number'] = Settlement::nextSettlementNumber();
            $data['invoice_id']        = $invoice->id;
            $data['created_by']        = auth()->id();

            Settlement::create($data);

            // If confirmed and fully paid, update invoice and trade
            if ($data['settlement_status'] === 'Confirmed') {
                $this->checkAndSettle($invoice);
            }
        });

        return redirect()->route('operations.invoices.show', $invoice)
            ->with('success', 'Settlement recorded.');
    }

    public function update(Request $request, Settlement $settlement)
    {
        $data = $request->validate([
            'payment_amount'    => 'required|numeric|min:0.01',
            'payment_date'      => 'required|date',
            'fx_rate'           => 'required|numeric|min:0.000001',
            'bank_ref'          => 'nullable|string|max:100',
            'settlement_status' => 'required|in:Pending,Confirmed',
            'comments'          => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data, $settlement) {
            $settlement->update($data);

            if ($data['settlement_status'] === 'Confirmed') {
                $this->checkAndSettle($settlement->invoice);
            }
        });

        return redirect()->route('operations.invoices.show', $settlement->invoice)
            ->with('success', 'Settlement updated.');
    }

    private function checkAndSettle(Invoice $invoice): void
    {
        $invoice->refresh();
        if ($invoice->outstandingAmount() <= 0) {
            $invoice->update(['invoice_status' => 'Paid']);
            $invoice->trade->update(['trade_status' => 'Settled']);
        }
    }
}
