<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\PaymentTerm;
use App\Models\Settlement;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['trade', 'counterparty', 'currency'])->latest('invoice_date')->latest('id');

        if ($request->filled('status')) {
            $query->where('invoice_status', $request->status);
        }
        if ($request->filled('counterparty_id')) {
            $query->where('counterparty_id', $request->counterparty_id);
        }

        $invoices = $query->paginate(25)->withQueryString();

        return view('operations.invoices.index', compact('invoices'));
    }

    public function createFromTrade(Trade $trade)
    {
        if ($trade->trade_status !== 'Validated') {
            return redirect()->route('trades.show', $trade)
                ->with('error', 'Only Validated trades can be invoiced.');
        }

        $amount       = Invoice::calculateAmount($trade);
        $paymentTerms = PaymentTerm::orderBy('name')->get();
        $currencies   = Currency::where('is_active', true)->orderBy('code')->get();

        return view('operations.invoices.create', compact('trade', 'amount', 'paymentTerms', 'currencies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'trade_id'                   => 'required|exists:trades,id',
            'invoice_date'               => 'required|date',
            'due_date'                   => 'nullable|date|after_or_equal:invoice_date',
            'invoice_amount'             => 'required|numeric|min:0',
            'currency_id'                => 'required|exists:currencies,id',
            'payment_terms_id'           => 'nullable|exists:payment_terms,id',
            'invoice_status'             => 'required|in:Draft,Issued,Paid,Overdue,Cancelled',
            'comments'                   => 'nullable|string|max:1000',
            'invoice_type'               => 'nullable|in:Commodity,Demurrage,Freight,Commission,Tax,Other',
            'invoice_reference_external' => 'nullable|string|max:100',
            'tax_amount'                 => 'nullable|numeric|min:0',
            'tax_code'                   => 'nullable|string|max:50',
            'dispute_status'             => 'nullable|in:Undisputed,In Dispute,Resolved',
            'dispute_reason'             => 'nullable|string|max:2000',
        ]);

        $trade = Trade::findOrFail($data['trade_id']);

        $data['invoice_number'] = Invoice::nextInvoiceNumber();
        $data['counterparty_id'] = $trade->counterparty_id;
        $data['created_by']     = auth()->id();

        Invoice::create($data);

        return redirect()->route('operations.invoices.index')
            ->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['trade.product', 'trade.uom', 'counterparty', 'currency', 'paymentTerms', 'createdBy', 'settlements.createdBy']);
        return view('operations.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->invoice_status === 'Paid') {
            return redirect()->route('operations.invoices.show', $invoice)
                ->with('error', 'Paid invoices cannot be edited.');
        }
        $paymentTerms = PaymentTerm::orderBy('name')->get();
        $currencies   = Currency::where('is_active', true)->orderBy('code')->get();
        return view('operations.invoices.edit', compact('invoice', 'paymentTerms', 'currencies'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'invoice_date'               => 'required|date',
            'due_date'                   => 'nullable|date',
            'invoice_amount'             => 'required|numeric|min:0',
            'currency_id'               => 'required|exists:currencies,id',
            'payment_terms_id'           => 'nullable|exists:payment_terms,id',
            'invoice_status'             => 'required|in:Draft,Issued,Paid,Overdue,Cancelled',
            'comments'                   => 'nullable|string|max:1000',
            'invoice_type'               => 'nullable|in:Commodity,Demurrage,Freight,Commission,Tax,Other',
            'invoice_reference_external' => 'nullable|string|max:100',
            'tax_amount'                 => 'nullable|numeric|min:0',
            'tax_code'                   => 'nullable|string|max:50',
            'dispute_status'             => 'nullable|in:Undisputed,In Dispute,Resolved',
            'dispute_reason'             => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($data, $invoice) {
            $invoice->update($data);

            // If fully paid, settle the trade
            if ($data['invoice_status'] === 'Paid') {
                $invoice->trade->update(['trade_status' => 'Settled']);
            }
        });

        return redirect()->route('operations.invoices.show', $invoice)
            ->with('success', 'Invoice updated.');
    }
}
