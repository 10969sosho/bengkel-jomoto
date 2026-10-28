<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
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
        ]);

        $invoice->markPaid($data['payment_method']);

        return back()->with('success', "Pembayaran {$invoice->invoice_number} via ".strtoupper($data['payment_method']).' tercatat.');
    }
}
