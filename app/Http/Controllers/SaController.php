<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\ServicePricelist;
use App\Models\SubMaterialCatalog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaController extends Controller
{
    public function index(): View
    {
        $orders = ServiceOrder::with(['customer', 'vehicle', 'technician', 'invoice'])
            ->orderByDesc('id')
            ->get();

        $stats = [
            'total' => $orders->count(),
            'draft' => $orders->where('status', 'draft')->count(),
            'waiting' => $orders->where('status', 'waiting_approval')->count(),
            'working' => $orders->where('status', 'working')->count(),
            'unpaid' => $orders->where('status', 'invoiced')
                ->where(fn ($q) => $q->whereHas('invoice', fn ($i) => $i->where('payment_status', 'unpaid')))
                ->count(),
        ];

        return view('sa.dashboard', compact('orders', 'stats'));
    }

    public function create(): View
    {
        $customers = User::where('role', 'customer')->with('vehicles')->orderBy('name')->get();

        return view('sa.create', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:users,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'service_type' => ['required', 'in:regular,free_atpm,free_jomoto,home_service'],
            'location_type' => ['required', 'in:onsite,offsite'],
            'customer_complaint' => ['required', 'string', 'max:2000'],
            'estimated_completion' => ['nullable', 'date'],
        ]);

        $order = ServiceOrder::create([
            ...$data,
            'wo_number' => ServiceOrder::generateWoNumber(),
            'sa_id' => auth()->id(),
            'status' => 'draft',
        ]);

        return redirect()->route('sa.show', $order)
            ->with('success', "Work Order {$order->wo_number} berhasil dibuat.");
    }

    public function show(ServiceOrder $serviceOrder): View
    {
        $serviceOrder->load([
            'customer', 'vehicle', 'technician', 'sa', 'items', 'inspection', 'workLogs', 'invoice',
        ]);

        $pricelists = ServicePricelist::active()->orderBy('category')->orderBy('service_name')->get();
        $subMaterials = SubMaterialCatalog::active()->orderBy('name')->get();
        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();

        return view('sa.show', compact('serviceOrder', 'pricelists', 'subMaterials', 'technicians'));
    }

    public function assign(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $data = $request->validate([
            'technician_id' => ['required', 'exists:users,id'],
            'estimated_completion' => ['nullable', 'date'],
        ]);

        $serviceOrder->update($data + ['sa_id' => auth()->id()]);

        return back()->with('success', 'Teknisi berhasil di-assign.');
    }

    public function estimate(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $data = $request->validate([
            'sa_diagnosis' => ['nullable', 'string', 'max:2000'],
            'items' => ['array', 'min:1'],
            'items.*.item_type' => ['required', 'in:service,sparepart,sub_material'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.is_free' => ['nullable', 'in:1,on,true'],
            'items.*.free_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $serviceOrder->update([
            'sa_id' => auth()->id(),
            'sa_diagnosis' => $data['sa_diagnosis'] ?? $serviceOrder->sa_diagnosis,
        ]);

        $serviceOrder->items()->delete();

        foreach ($data['items'] as $item) {
            $isFree = ! empty($item['is_free']);

            $serviceOrder->items()->create([
                'item_type' => $item['item_type'],
                'item_name' => $item['item_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $isFree ? 0 : $item['quantity'] * $item['unit_price'],
                'is_free' => $isFree,
                'free_reason' => $isFree ? ($item['free_reason'] ?? 'Tanpa biaya') : null,
            ]);
        }

        if (in_array($serviceOrder->status, ['draft', 'waiting_approval'], true)) {
            $serviceOrder->update(['status' => 'waiting_approval']);
        }

        return back()->with('success', 'Lembar estimasi biaya tersimpan & menunggu persetujuan customer.');
    }

    public function approve(ServiceOrder $serviceOrder): RedirectResponse
    {
        if ($serviceOrder->status !== 'waiting_approval') {
            return back()->with('error', 'Hanya WO berstatus menunggu persetujuan yang bisa disetujui.');
        }

        $serviceOrder->update(['status' => 'approved']);

        return back()->with('success', 'Estimasi disetujui. WO siap dikerjakan teknisi.');
    }

    public function issueInvoice(ServiceOrder $serviceOrder): RedirectResponse
    {
        if ($serviceOrder->status !== 'completed' || $serviceOrder->invoice) {
            return back()->with('error', 'Invoice hanya bisa diterbitkan untuk WO yang sudah selesai.');
        }

        $invoice = Invoice::createFromOrder($serviceOrder);
        $serviceOrder->update(['status' => 'invoiced']);

        return redirect()->route('invoices.print', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} berhasil diterbitkan.");
    }
}
