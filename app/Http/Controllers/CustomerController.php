<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $vehicles = $user->vehicles()
            ->with(['serviceOrders' => fn ($query) => $query->whereNotNull('coupon_milestone')->where('status', '!=', 'cancelled')])
            ->orderBy('plate_number')->get();

        $orders = $user->customerOrders()
            ->with(['vehicle', 'technician', 'sa', 'invoice', 'items'])
            ->orderByDesc('id')
            ->get();

        $active = $orders->whereIn('status', ['draft', 'waiting_approval', 'approved', 'working', 'completed']);
        $history = $orders->whereIn('status', ['invoiced', 'cancelled']);

        return view('customer.dashboard', compact('user', 'vehicles', 'active', 'history'));
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['required', 'in:electric,gasoline'],
            'last_odometer' => ['required', 'integer', 'min:0'],
        ]);

        $request->user()->vehicles()->create($data + ['year' => now()->year]);

        return back()->with('success', 'Kendaraan berhasil didaftarkan.');
    }

    public function storeOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('user_id', $request->user()->id)],
            'customer_complaint' => ['required', 'string', 'max:2000'],
            'odometer' => ['required', 'integer', 'min:0'],
            'location_type' => ['required', 'in:onsite,offsite'],
            'offsite_address' => ['required_if:location_type,offsite', 'nullable', 'string', 'max:1000'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'visit_fee' => ['nullable', 'integer', 'min:0'],
        ]);

        $vehicle = $request->user()->vehicles()->findOrFail($data['vehicle_id']);
        $coupon = ServiceOrder::couponForOdometer($vehicle, $data['odometer']);
        $offsite = $data['location_type'] === 'offsite';
        $vehicle->update(['last_odometer' => $data['odometer']]);

        $order = ServiceOrder::create([
            'wo_number' => ServiceOrder::generateWoNumber(),
            'customer_id' => $request->user()->id,
            'vehicle_id' => $vehicle->id,
            'customer_complaint' => $data['customer_complaint'],
            'odometer' => $data['odometer'],
            'service_type' => $coupon['service_type'] ?? ($offsite ? 'home_service' : 'regular'),
            'coupon_milestone' => $coupon['milestone'] ?? null,
            'location_type' => $data['location_type'],
            'offsite_address' => $offsite ? $data['offsite_address'] : null,
            'location_notes' => $offsite ? ($data['location_notes'] ?? null) : null,
            'visit_fee' => $offsite ? ($data['visit_fee'] ?? 50_000) : 0,
            'status' => 'draft',
        ]);

        return redirect()->route('customer.index')->with('success', "Booking {$order->wo_number} berhasil dikirim ke Service Adviser.");
    }
}
