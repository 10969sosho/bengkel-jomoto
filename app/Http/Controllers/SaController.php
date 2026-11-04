<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\ServicePricelist;
use App\Models\SubMaterialCatalog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'unpaid' => $orders->filter(fn (ServiceOrder $order): bool => $order->status === 'invoiced'
                && $order->invoice?->payment_status === 'unpaid')->count(),
        ];

        return view('sa.dashboard', compact('orders', 'stats'));
    }

    public function create(): View
    {
        $customers = User::where('role', 'customer')
            ->with(['vehicles.serviceOrders' => fn ($query) => $query->whereNotNull('coupon_milestone')->where('status', '!=', 'cancelled')])
            ->orderBy('name')->get();

        $selectedCustomerId = request()->integer('customer_id');

        return view('sa.create', compact('customers', 'selectedCustomerId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', Rule::exists('users', 'id')->where('role', 'customer')],
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('user_id', $request->integer('customer_id'))],
            'location_type' => ['required', 'in:onsite,offsite'],
            'offsite_address' => ['required_if:location_type,offsite', 'nullable', 'string', 'max:1000'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'visit_fee' => ['nullable', 'integer', 'min:0'],
            'odometer' => ['required', 'integer', 'min:0'],
            'customer_complaint' => ['required', 'string', 'max:2000'],
            'estimated_completion' => ['nullable', 'date'],
        ]);

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        $coupon = ServiceOrder::couponForOdometer($vehicle, $data['odometer']);
        $offsite = $data['location_type'] === 'offsite';
        $vehicle->update(['last_odometer' => $data['odometer']]);

        $order = ServiceOrder::create([
            'customer_id' => $data['customer_id'],
            'vehicle_id' => $data['vehicle_id'],
            'location_type' => $data['location_type'],
            'offsite_address' => $offsite ? $data['offsite_address'] : null,
            'location_notes' => $offsite ? ($data['location_notes'] ?? null) : null,
            'visit_fee' => $offsite ? ($data['visit_fee'] ?? 50_000) : 0,
            'odometer' => $data['odometer'],
            'service_type' => $coupon['service_type'] ?? ($offsite ? 'home_service' : 'regular'),
            'coupon_milestone' => $coupon['milestone'] ?? null,
            'customer_complaint' => $data['customer_complaint'],
            'estimated_completion' => $data['estimated_completion'] ?? null,
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
        $suggestedItems = [];

        if ($serviceOrder->items->isEmpty() && $serviceOrder->coupon_milestone) {
            $milestone = number_format($serviceOrder->coupon_milestone, 0, ',', '.');
            $couponPricelist = $pricelists->first(fn ($row) => $row->category === $serviceOrder->service_type
                && str_contains($row->service_name, $milestone));
            $freeReason = $serviceOrder->service_type === 'free_atpm'
                ? "Kupon servis berkala ATPM {$milestone} KM"
                : "Kupon servis berkala Jomoto {$milestone} KM";

            $suggestedItems[] = [
                'item_type' => 'service',
                'item_name' => $couponPricelist?->service_name ?? "Jasa servis berkala {$milestone} KM",
                'quantity' => 1,
                'unit_price' => (int) ($couponPricelist?->price ?? 0),
                'is_free' => true,
                'free_reason' => $freeReason,
            ];

            if ($serviceOrder->service_type === 'free_atpm') {
                $suggestedItems[] = [
                    'item_type' => 'sparepart', 'item_name' => 'Oli Mesin — Kupon ATPM',
                    'quantity' => 1, 'unit_price' => 0, 'is_free' => true, 'free_reason' => "Oli gratis ATPM {$milestone} KM",
                ];
                $suggestedItems[] = [
                    'item_type' => 'sparepart', 'item_name' => 'Sparepart Dasar — Kupon ATPM',
                    'quantity' => 1, 'unit_price' => 0, 'is_free' => true, 'free_reason' => "Sparepart dasar gratis ATPM {$milestone} KM",
                ];
            }
        }

        return view('sa.show', compact('serviceOrder', 'pricelists', 'subMaterials', 'technicians', 'suggestedItems'));
    }

    public function storeVehicle(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->role === 'customer', 404);

        $data = $request->validate([
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['required', 'in:electric,gasoline'],
            'last_odometer' => ['required', 'integer', 'min:0'],
        ]);

        $customer->vehicles()->create($data + ['year' => now()->year]);

        return back()->with('success', 'Kendaraan baru berhasil ditambahkan ke customer.');
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
