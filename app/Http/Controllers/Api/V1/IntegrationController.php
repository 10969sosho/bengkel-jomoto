<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IntegrationController extends Controller
{
    public function customers(): JsonResponse
    {
        $customers = User::query()->where('role', 'customer')
            ->select(['id', 'name', 'email', 'phone', 'whatsapp', 'ecommerce_user_id'])
            ->orderBy('id')->paginate(25);

        $customers->getCollection()->transform(fn (User $customer): array => $this->customerData($customer));

        return response()->json($customers);
    }

    public function customer(int $id): JsonResponse
    {
        return response()->json(['data' => $this->customerData(
            User::query()->where('role', 'customer')->findOrFail($id),
        )]);
    }

    public function vehicles(int $id): JsonResponse
    {
        $customer = User::query()->where('role', 'customer')->findOrFail($id);

        return response()->json(['data' => $customer->vehicles()->orderBy('id')->get()]);
    }

    public function serviceHistory(int $id): JsonResponse
    {
        $customer = User::query()->where('role', 'customer')->findOrFail($id);

        return response()->json(['data' => $customer->customerOrders()
            ->with(['vehicle', 'items', 'invoice'])
            ->latest('id')->paginate(25)]);
    }

    public function serviceOrders(Request $request): JsonResponse
    {
        $orders = ServiceOrder::query()->with(['customer', 'vehicle', 'technician', 'items', 'invoice'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->latest('id')->paginate(25);

        return response()->json($orders);
    }

    public function serviceOrder(int $id): JsonResponse
    {
        return response()->json(['data' => ServiceOrder::with([
            'customer', 'vehicle', 'technician', 'sa', 'items', 'inspection', 'invoice',
        ])->findOrFail($id)]);
    }

    public function createServiceOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:users,id'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'customer_complaint' => ['required', 'string', 'max:2000'],
            'odometer' => ['required', 'integer', 'min:0'],
            'location_type' => ['required', Rule::in(['onsite', 'offsite'])],
            'offsite_address' => ['required_if:location_type,offsite', 'nullable', 'string', 'max:1000'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'visit_fee' => ['nullable', 'integer', 'min:0'],
        ]);

        $customer = User::query()->where('role', 'customer')->findOrFail($data['customer_id']);
        $vehicle = $customer->vehicles()->find($data['vehicle_id']);

        if (! $vehicle) {
            throw ValidationException::withMessages(['vehicle_id' => 'Kendaraan bukan milik customer tersebut.']);
        }

        $coupon = ServiceOrder::couponForOdometer($vehicle, $data['odometer']);
        $vehicle->update(['last_odometer' => $data['odometer']]);
        $offsite = $data['location_type'] === 'offsite';
        $order = ServiceOrder::create([
            'wo_number' => ServiceOrder::generateWoNumber(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'customer_complaint' => $data['customer_complaint'],
            'service_type' => $coupon['service_type'] ?? ($offsite ? 'home_service' : 'regular'),
            'coupon_milestone' => $coupon['milestone'] ?? null,
            'location_type' => $data['location_type'],
            'offsite_address' => $offsite ? $data['offsite_address'] : null,
            'location_notes' => $offsite ? ($data['location_notes'] ?? null) : null,
            'visit_fee' => $offsite ? ($data['visit_fee'] ?? 50_000) : 0,
            'odometer' => $data['odometer'],
            'status' => 'draft',
        ]);

        return response()->json(['data' => $order->load(['customer', 'vehicle'])], 201);
    }

    public function invoices(): JsonResponse
    {
        return response()->json(Invoice::with('serviceOrder.customer', 'serviceOrder.vehicle')
            ->latest('id')->paginate(25));
    }

    public function invoice(int $id): JsonResponse
    {
        return response()->json(['data' => Invoice::with([
            'serviceOrder.customer', 'serviceOrder.vehicle', 'serviceOrder.items',
        ])->findOrFail($id)]);
    }

    public function spareparts(): JsonResponse
    {
        return response()->json(Sparepart::query()->where('is_active', true)
            ->orderBy('name')->paginate(50));
    }

    public function sparepartStock(int $id): JsonResponse
    {
        $sparepart = Sparepart::query()->where('is_active', true)->findOrFail($id);

        return response()->json(['data' => [
            'id' => $sparepart->id,
            'sku' => $sparepart->sku,
            'stock' => $sparepart->stock,
            'ecommerce_product_id' => $sparepart->ecommerce_product_id,
            'updated_at' => $sparepart->updated_at,
        ]]);
    }

    public function revenue(Request $request): JsonResponse
    {
        $invoices = $this->paidInvoices($request);

        return response()->json(['data' => [
            'total' => (int) (clone $invoices)->sum('grand_total'),
            'breakdown' => [
                'services' => (int) (clone $invoices)->sum('total_services'),
                'spareparts' => (int) (clone $invoices)->sum('total_spareparts'),
                'sub_materials' => (int) (clone $invoices)->sum('total_submaterials'),
            ],
            'payment_methods' => (clone $invoices)->selectRaw('payment_method, SUM(grand_total) as total, COUNT(*) as count')
                ->groupBy('payment_method')->get(),
        ]]);
    }

    public function serviceSummary(): JsonResponse
    {
        return response()->json(['data' => [
            'total' => ServiceOrder::count(),
            'by_status' => ServiceOrder::query()->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status'),
            'by_service_type' => ServiceOrder::query()->selectRaw('service_type, COUNT(*) as count')->groupBy('service_type')->pluck('count', 'service_type'),
        ]]);
    }

    /** @return array<string, mixed> */
    private function customerData(User $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'whatsapp' => $customer->whatsapp,
            'email' => $customer->email,
            'ecommerce_user_id' => $customer->ecommerce_user_id,
        ];
    }

    private function paidInvoices(Request $request): Builder
    {
        $data = $request->validate(['period' => ['sometimes', 'in:all,month,today']]);
        $period = $data['period'] ?? 'all';

        return Invoice::query()->where('payment_status', 'paid')
            ->when($period === 'month', fn ($query) => $query->whereYear('paid_at', now()->year)->whereMonth('paid_at', now()->month))
            ->when($period === 'today', fn ($query) => $query->whereDate('paid_at', today()));
    }
}
