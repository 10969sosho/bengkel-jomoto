<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServicePricelist;
use App\Services\WhatsAppService;
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
            ->with(['vehicle', 'technician', 'sa', 'invoice', 'items', 'inspection', 'workLogs'])
            ->orderByDesc('id')
            ->get();

        $active = $orders->whereIn('status', [
            'draft', 'inspected', 'waiting_approval', 'approved', 'working', 'completed', 'waiting_confirmation', 'confirmed',
        ]);
        $history = $orders->whereIn('status', ['invoiced', 'cancelled']);

        $pricelistNames = ServicePricelist::pluck('service_name', 'id')->all();

        return view('customer.dashboard', compact(
            'user', 'vehicles', 'active', 'history', 'pricelistNames',
        ));
    }

    /** Halaman mandiri: daftar kendaraan customer + form pendaftaran kendaraan baru. */
    public function vehicles(): View
    {
        $vehicles = auth()->user()->vehicles()
            ->with(['serviceOrders' => fn ($query) => $query->whereNotNull('coupon_milestone')->where('status', '!=', 'cancelled')])
            ->orderBy('plate_number')->get();

        return view('customer.vehicles', compact('vehicles'));
    }

    /** Halaman mandiri: booking servis baru oleh customer. */
    public function createBooking(): View
    {
        $vehicles = auth()->user()->vehicles()
            ->with(['serviceOrders' => fn ($query) => $query->whereNotNull('coupon_milestone')->where('status', '!=', 'cancelled')])
            ->orderBy('plate_number')->get();

        $pricelists = ServicePricelist::serviceCategories()->active()->orderBy('category')->orderBy('service_name')->get();

        return view('customer.booking.create', compact('vehicles', 'pricelists'));
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'engine_cc' => ['nullable', 'string', 'max:20'],
            'fuel_type' => ['required', 'in:electric,gasoline'],
            'last_odometer' => ['required', 'integer', 'min:0'],
        ]);

        $request->user()->vehicles()->create($data + ['year' => now()->year]);

        return back()->with('success', 'Kendaraan berhasil didaftarkan.');
    }

    public function storeOrder(Request $request, WhatsAppService $whatsapp): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('user_id', $request->user()->id)],
            'customer_complaint' => ['required', 'string', 'max:2000'],
            'odometer' => ['required', 'integer', 'min:0'],
            'booking_date' => ['nullable', 'date', 'after_or_equal:today'],
            'need_tax_invoice' => ['nullable', 'boolean'],
            'location_type' => ['required', 'in:onsite,offsite'],
            'offsite_address' => ['required_if:location_type,offsite', 'nullable', 'string', 'max:1000'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'requested_services' => ['required', 'array', 'min:1'],
            'requested_services.*' => ['integer', 'distinct', Rule::exists('service_pricelists', 'id')->where('is_active', true)],
            'stnk_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
            'requested_services' => array_values($data['requested_services']),
            'stnk_photo' => $request->file('stnk_photo')->store('stnk', 'public'),
            'odometer' => $data['odometer'],
            'booking_date' => $data['booking_date'] ?? null,
            'need_tax_invoice' => (bool) ($data['need_tax_invoice'] ?? false),
            'service_type' => $coupon['service_type'] ?? ($offsite ? 'home_service' : 'regular'),
            'coupon_milestone' => $coupon['milestone'] ?? null,
            'location_type' => $data['location_type'],
            'offsite_address' => $offsite ? $data['offsite_address'] : null,
            'location_notes' => $offsite ? ($data['location_notes'] ?? null) : null,
            'visit_fee' => 0,
            'status' => 'draft',
        ]);

        $whatsapp->notify($order, "Halo {$order->customer?->name}! Booking servis Anda sudah kami terima dan sedang diproses Sales Adviser.");

        return redirect()->route('customer.index')->with('success', "Booking {$order->wo_number} berhasil dikirim ke Sales Adviser.");
    }

    public function approveEstimate(Request $request, ServiceOrder $serviceOrder, WhatsAppService $whatsapp): RedirectResponse
    {
        $this->guard($request, $serviceOrder);

        if ($serviceOrder->status !== 'waiting_approval') {
            return back()->with('error', 'Hanya estimasi yang masih menunggu persetujuan yang bisa disetujui.');
        }

        $spkNumber = $serviceOrder->spk_number ?? ServiceOrder::generateSpkNumber();
        $serviceOrder->update([
            'status' => 'approved',
            'estimate_approved_at' => now(),
            'spk_number' => $spkNumber,
            'spk_issued_at' => $serviceOrder->spk_issued_at ?? now(),
        ]);

        $whatsapp->notify($serviceOrder, "Terima kasih, estimasi disetujui. Surat Perintah Kerja (SPK) No: {$spkNumber} telah diterbitkan dan teknisi segera mengerjakan kendaraan Anda.");

        return back()->with('success', "Estimasi {$serviceOrder->wo_number} disetujui. Surat Perintah Kerja (SPK) No: {$spkNumber} telah diterbitkan dan teknisi mulai mengerjakan.");
    }

    /** Halaman detail lembar estimasi biaya (preview resmi) untuk customer pemilik WO. */
    public function showEstimate(Request $request, ServiceOrder $serviceOrder): View
    {
        $this->guard($request, $serviceOrder);

        $serviceOrder->load(['customer', 'vehicle', 'technician', 'sa', 'items', 'inspection']);

        return view('sa.print-estimate', [
            'serviceOrder' => $serviceOrder,
            'requestedServices' => $serviceOrder->requestedServiceLabels(ServicePricelist::pluck('service_name', 'id')->all()),
        ]);
    }

    public function confirmResult(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $this->guard($request, $serviceOrder);

        if ($serviceOrder->status !== 'waiting_confirmation') {
            return back()->with('error', 'Hanya hasil pengerjaan yang sudah diperiksa SA yang bisa dikonfirmasi.');
        }

        $data = $request->validate([
            'confirmation_note' => ['required', 'string', 'max:500'],
        ]);

        $serviceOrder->update([
            'status' => 'confirmed',
            'result_confirmed_at' => now(),
            'result_confirmation_notes' => $data['confirmation_note'],
        ]);

        return back()->with('success', "Terima kasih. Konfirmasi hasil pengerjaan {$serviceOrder->wo_number} tercatat, SA akan menerbitkan invoice.");
    }

    private function guard(Request $request, ServiceOrder $serviceOrder): void
    {
        abort_unless($serviceOrder->customer_id === $request->user()?->id, 403);
    }
}
