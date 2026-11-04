<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public const BANK_ACCOUNTS = [
        'BCA · 1234567890 a.n. Bengkel Jomoto',
        'Mandiri · 135000998877 a.n. Bengkel Jomoto',
    ];

    public function print(Invoice $invoice): View
    {
        $invoice->load(['serviceOrder.customer', 'serviceOrder.vehicle', 'serviceOrder.items', 'serviceOrder.technician', 'serviceOrder.sa']);

        return view('invoices.print', compact('invoice'));
    }

    public function pay(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->isPaid()) {
            return back()->with('error', 'Invoice sudah lunas.');
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:qris,cash,transfer'],
            'cash_received' => [Rule::when($request->input('payment_method') === 'cash', ['required', 'integer', 'min:'.$invoice->grand_total], ['nullable'])],
            'payment_reference' => [Rule::when(in_array($request->input('payment_method'), ['qris', 'transfer'], true), ['required', 'string', 'max:100'], ['nullable'])],
            'bank_account' => [Rule::when($request->input('payment_method') === 'transfer', ['required', Rule::in(self::BANK_ACCOUNTS)], ['nullable'])],
        ]);

        $invoice->markPaid($data['payment_method'], $data['payment_reference'] ?? null, $data['bank_account'] ?? null);

        return back()->with('success', "Pembayaran {$invoice->invoice_number} via ".strtoupper($data['payment_method']).' tercatat.');
    }
}
