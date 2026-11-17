<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\ServicePricelist;
use App\Models\SubMaterialCatalog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'confirmation' => $orders->filter(fn (ServiceOrder $order): bool => in_array($order->status, ['completed', 'waiting_confirmation'], true))->count(),
            'unpaid' => $orders->filter(fn (ServiceOrder $order): bool => $order->status === 'invoiced'
                && $order->invoice?->payment_status === 'unpaid')->count(),
        ];
        $bookings = ServiceOrder::with(['customer', 'vehicle'])
            ->where('status', 'draft')
            ->whereNull('sa_id')
            ->orderByDesc('id')
            ->get();
        $pricelistNames = ServicePricelist::pluck('service_name', 'id')->all();

        return view('sa.dashboard', compact('orders', 'stats', 'bookings', 'pricelistNames'));
    }

    public function create(): View
    {
        $customers = User::where('role', 'customer')
            ->with(['vehicles.serviceOrders' => fn ($query) => $query->whereNotNull('coupon_milestone')->where('status', '!=', 'cancelled')])
            ->orderBy('name')->get();

        $selectedBooking = request()->filled('booking_id')
            ? ServiceOrder::where('status', 'draft')->whereNull('sa_id')->with(['customer', 'vehicle'])->findOrFail(request()->integer('booking_id'))
            : null;
        $selectedCustomerId = $selectedBooking?->customer_id ?? request()->integer('customer_id');
        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();

        return view('sa.create', compact('customers', 'selectedCustomerId', 'selectedBooking', 'technicians'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'booking_id' => ['nullable', 'integer', Rule::exists('service_orders', 'id')->where('status', 'draft')->whereNull('sa_id')],
            'customer_id' => ['required', Rule::exists('users', 'id')->where('role', 'customer')],
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('user_id', $request->integer('customer_id'))],
            'technician_id' => ['required', Rule::exists('users', 'id')->where('role', 'teknisi')],
            'location_type' => ['required', 'in:onsite,offsite'],
            'offsite_address' => ['required_if:location_type,offsite', 'nullable', 'string', 'max:1000'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'visit_fee' => ['nullable', 'integer', 'min:0'],
            'odometer' => ['required', 'integer', 'min:0'],
            'customer_complaint' => ['required', 'string', 'max:2000'],
            'estimated_completion' => ['nullable', 'date'],
            'inspection_checklist' => ['nullable', 'array'],
            'inspection_checklist.*' => ['string', Rule::in(array_keys(ServiceOrder::INSPECTION_CHECKLIST))],
        ]);

        $booking = isset($data['booking_id'])
            ? ServiceOrder::where('status', 'draft')->whereNull('sa_id')->findOrFail($data['booking_id'])
            : null;
        if ($booking) {
            $data['customer_id'] = $booking->customer_id;
            $data['vehicle_id'] = $booking->vehicle_id;
        }

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        $coupon = ServiceOrder::couponForOdometer($vehicle, $data['odometer'], $booking?->id);
        $offsite = $data['location_type'] === 'offsite';

        $orderData = [
            'customer_id' => $data['customer_id'],
            'vehicle_id' => $data['vehicle_id'],
            'technician_id' => $data['technician_id'],
            'location_type' => $data['location_type'],
            'offsite_address' => $offsite ? $data['offsite_address'] : null,
            'location_notes' => $offsite ? ($data['location_notes'] ?? null) : null,
            'visit_fee' => $offsite ? ($data['visit_fee'] ?? 50_000) : 0,
            'odometer' => $data['odometer'],
            'service_type' => $coupon['service_type'] ?? ($offsite ? 'home_service' : 'regular'),
            'coupon_milestone' => $coupon['milestone'] ?? null,
            'customer_complaint' => $data['customer_complaint'],
            'estimated_completion' => $data['estimated_completion'] ?? null,
            'inspection_checklist' => array_values($data['inspection_checklist'] ?? array_keys(ServiceOrder::INSPECTION_CHECKLIST)),
            'sa_id' => auth()->id(),
            'status' => 'draft',
        ];

        $order = DB::transaction(function () use ($vehicle, $data, $booking, $orderData): ServiceOrder {
            $vehicle->update(['last_odometer' => $data['odometer']]);

            if ($booking) {
                $booking->update($orderData);

                return $booking;
            }

            return ServiceOrder::create($orderData + ['wo_number' => ServiceOrder::generateWoNumber()]);
        });

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

        return view('sa.show', compact('serviceOrder', 'pricelists', 'subMaterials', 'technicians', 'suggestedItems'))
            ->with('pricelistNames', ServicePricelist::pluck('service_name', 'id')->all());
    }

    public function storeVehicle(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->role === 'customer', 404);

        $data = $request->validate([
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')],
            'year' => ['required', 'integer', 'between:1950,'.(now()->year + 1)],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['required', 'in:electric,gasoline'],
            'last_odometer' => ['required', 'integer', 'min:0'],
        ]);

        $customer->vehicles()->create($data);

        return back()->with('success', 'Kendaraan baru berhasil ditambahkan ke customer.');
    }

    public function customers(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $customers = User::query()
            ->where('role', 'customer')
            ->withCount('vehicles')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('whatsapp', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
        ]);

        $customer = User::create($data + [
            'password' => Str::random(40),
            'role' => 'customer',
        ]);

        return redirect()->route('sa.customers.show', $customer)->with('success', 'Customer baru berhasil ditambahkan.');
    }

    public function showCustomer(User $customer): View
    {
        abort_unless($customer->role === 'customer', 404);

        $customer->load(['vehicles' => fn ($query) => $query->orderBy('plate_number')]);
        $orders = $customer->customerOrders()
            ->with(['vehicle', 'technician', 'invoice'])
            ->orderByDesc('id')
            ->get();

        return view('customers.show', compact('customer', 'orders'));
    }

    public function updateVehicle(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')->ignore($vehicle->id)],
            'year' => ['required', 'integer', 'between:1950,'.(now()->year + 1)],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['required', 'in:electric,gasoline'],
            'last_odometer' => ['required', 'integer', 'min:0'],
        ]);

        $vehicle->update($data);

        return back()->with('success', 'Data kendaraan berhasil diperbarui.');
    }

    public function payments(Request $request): View
    {
        $filter = $request->string('status', 'all')->toString();
        abort_unless(in_array($filter, ['all', 'unpaid', 'paid'], true), 404);
        $search = trim($request->string('q')->toString());

        $invoices = Invoice::query()
            ->with(['serviceOrder.customer', 'serviceOrder.vehicle'])
            ->when($filter !== 'all', fn ($query) => $query->where('payment_status', $filter))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('serviceOrder.customer', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('serviceOrder.vehicle', fn ($query) => $query->where('plate_number', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'unpaid' => Invoice::where('payment_status', 'unpaid')->sum('grand_total'),
            'paid_today' => Invoice::where('payment_status', 'paid')->whereDate('paid_at', today())->sum('grand_total'),
            'transactions' => Invoice::count(),
        ];
        $bankAccounts = InvoiceController::BANK_ACCOUNTS;

        return view('sa.payments', compact('invoices', 'stats', 'filter', 'search', 'bankAccounts'));
    }

    public function assign(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $data = $request->validate([
            'technician_id' => ['required', Rule::exists('users', 'id')->where('role', 'teknisi')],
            'estimated_completion' => ['nullable', 'date'],
        ]);

        $serviceOrder->update($data + ['sa_id' => auth()->id()]);

        return back()->with('success', 'Teknisi berhasil di-assign.');
    }

    public function estimate(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        if (! $serviceOrder->inspection()->exists()) {
            return back()->with('error', 'Estimasi baru bisa dibuat setelah teknisi mengisi lembar pemeriksaan.');
        }

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
            'estimate_number' => $serviceOrder->estimate_number ?? ServiceOrder::generateEstimateNumber(),
            'estimate_discount' => $data['estimate_discount'] ?? ($serviceOrder->estimate_discount ?? 0),
            'estimate_tax' => $data['estimate_tax'] ?? ($serviceOrder->estimate_tax ?? 0),
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

        if (in_array($serviceOrder->status, ['draft', 'inspected', 'waiting_approval'], true)) {
            $serviceOrder->update(['status' => 'waiting_approval']);
        }

        return back()->with('success', 'Lembar estimasi biaya tersimpan & menunggu persetujuan customer.');
    }

    public function approve(ServiceOrder $serviceOrder): RedirectResponse
    {
        if ($serviceOrder->status !== 'waiting_approval') {
            return back()->with('error', 'Hanya WO berstatus menunggu persetujuan yang bisa disetujui.');
        }

        $serviceOrder->update([
            'status' => 'approved',
            'estimate_approved_at' => now(),
        ]);

        return back()->with('success', 'Estimasi disetujui. WO siap dikerjakan teknisi.');
    }

    public function finalCheck(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        if ($serviceOrder->status !== 'completed') {
            return back()->with('error', 'Pemeriksaan akhir hanya bisa dilakukan setelah pengerjaan teknisi selesai.');
        }

        $data = $request->validate([
            'final_check_notes' => ['required', 'string', 'max:2000'],
        ]);

        $serviceOrder->update([
            'final_check_at' => now(),
            'final_check_notes' => $data['final_check_notes'],
            'status' => 'waiting_confirmation',
        ]);

        return back()->with('success', "Pemeriksaan akhir {$serviceOrder->wo_number} selesai. Menunggu konfirmasi customer atas hasil pengerjaan.");
    }

    public function confirmResult(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        if ($serviceOrder->status !== 'waiting_confirmation') {
            return back()->with('error', 'Hanya hasil pengerjaan yang sudah diperiksa SA yang bisa dikonfirmasi.');
        }

        $data = $request->validate([
            'result_confirmation_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $serviceOrder->update([
            'status' => 'confirmed',
            'result_confirmed_at' => now(),
            'result_confirmation_notes' => $data['result_confirmation_notes'] ?? 'Dikonfirmasi langsung oleh customer kepada SA.',
        ]);

        return back()->with('success', "Customer menyetujui hasil pengerjaan {$serviceOrder->wo_number}. Invoice siap diterbitkan.");
    }

    public function issueInvoice(ServiceOrder $serviceOrder): RedirectResponse
    {
        if ($serviceOrder->status !== 'confirmed' || $serviceOrder->invoice) {
            return back()->with('error', 'Invoice hanya bisa diterbitkan setelah customer mengonfirmasi hasil pengerjaan.');
        }

        $invoice = Invoice::createFromOrder($serviceOrder);
        $serviceOrder->update(['status' => 'invoiced']);

        return redirect()->route('invoices.print', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} berhasil diterbitkan.");
    }

    public function printEstimate(ServiceOrder $serviceOrder): View
    {
        $serviceOrder->load(['customer', 'vehicle', 'technician', 'sa', 'items', 'inspection']);

        return view('sa.print-estimate', [
            'serviceOrder' => $serviceOrder,
            'requestedServices' => $serviceOrder->requestedServiceLabels(ServicePricelist::pluck('service_name', 'id')->all()),
        ]);
    }

    public function showBooking(ServiceOrder $booking): View
    {
        $booking->load(['customer', 'vehicle']);
        $pricelistNames = ServicePricelist::pluck('service_name', 'id')->all();

        return view('sa.booking-detail', compact('booking', 'pricelistNames'));
    }

    public function createCheckin(Request $request): View
    {
        $booking = null;
        if ($request->filled('booking_id')) {
            $booking = ServiceOrder::with(['customer', 'vehicle'])->findOrFail($request->integer('booking_id'));
        }

        $customers = User::where('role', 'customer')->with('vehicles')->orderBy('name')->get();
        $technicians = User::where('role', 'teknisi')->orderBy('name')->get();
        $nextInspectionNumber = Inspection::generateInspectionNumber();

        return view('sa.checkin', [
            'booking' => $booking,
            'customers' => $customers,
            'technicians' => $technicians,
            'nextInspectionNumber' => $nextInspectionNumber,
            'documentsChecklist' => Inspection::DOCUMENTS_CHECKLIST,
            'exteriorSections' => Inspection::EXTERIOR_SECTIONS,
        ]);
    }

    public function storeCheckin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'booking_id' => ['nullable', 'integer', Rule::exists('service_orders', 'id')],
            'customer_id' => ['required', Rule::exists('users', 'id')->where('role', 'customer')],
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('user_id', $request->integer('customer_id'))],
            'technician_id' => ['required', Rule::exists('users', 'id')->where('role', 'teknisi')],
            'location_type' => ['required', 'in:onsite,offsite'],
            'offsite_address' => ['required_if:location_type,offsite', 'nullable', 'string', 'max:1000'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'odometer' => ['required', 'integer', 'min:0'],
            'fuel_level' => ['required', 'string', 'in:E,1/4,1/2,3/4,F'],
            'manager_name' => ['nullable', 'string', 'max:100'],
            'customer_complaint' => ['required', 'string', 'max:2000'],
            'sa_diagnosis' => ['nullable', 'string', 'max:2000'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['nullable', 'in:ada,tidak'],
            'exterior' => ['nullable', 'array'],
            'odometer_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'fuel_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        $vehicle->update(['last_odometer' => $data['odometer']]);

        $booking = isset($data['booking_id'])
            ? ServiceOrder::findOrFail($data['booking_id'])
            : null;

        $order = DB::transaction(function () use ($data, $booking, $vehicle, $request): ServiceOrder {
            $coupon = ServiceOrder::couponForOdometer($vehicle, $data['odometer'], $booking?->id);
            $offsite = $data['location_type'] === 'offsite';

            $orderAttributes = [
                'customer_id' => $data['customer_id'],
                'vehicle_id' => $data['vehicle_id'],
                'technician_id' => $data['technician_id'],
                'sa_id' => auth()->id(),
                'location_type' => $data['location_type'],
                'offsite_address' => $offsite ? $data['offsite_address'] : null,
                'location_notes' => $offsite ? ($data['location_notes'] ?? null) : null,
                'visit_fee' => $offsite ? 50_000 : 0,
                'odometer' => $data['odometer'],
                'service_type' => $coupon['service_type'] ?? ($offsite ? 'home_service' : 'regular'),
                'coupon_milestone' => $coupon['milestone'] ?? null,
                'customer_complaint' => $data['customer_complaint'],
                'sa_diagnosis' => $data['sa_diagnosis'] ?? null,
                'status' => 'inspected',
            ];

            if ($booking) {
                $booking->update($orderAttributes);
                $serviceOrder = $booking;
            } else {
                $serviceOrder = ServiceOrder::create($orderAttributes + [
                    'wo_number' => ServiceOrder::generateWoNumber(),
                ]);
            }

            $odometerPhotoPath = $request->hasFile('odometer_photo')
                ? $request->file('odometer_photo')->store('inspections', 'public')
                : ($serviceOrder->inspection?->odometer_photo ?? null);

            $fuelPhotoPath = $request->hasFile('fuel_photo')
                ? $request->file('fuel_photo')->store('inspections', 'public')
                : ($serviceOrder->inspection?->fuel_photo ?? null);

            $documentsChecklist = [];
            foreach (Inspection::DOCUMENTS_CHECKLIST as $docKey => $docLabel) {
                $documentsChecklist[$docKey] = [
                    'label' => $docLabel,
                    'status' => ($data['documents'][$docKey] ?? 'tidak') === 'ada' ? 'ada' : 'tidak',
                ];
            }

            $exteriorChecklist = [];
            foreach (Inspection::EXTERIOR_SECTIONS as $secName => $secItems) {
                foreach ($secItems as $itemKey => $itemLabel) {
                    $exteriorChecklist[$itemKey] = [
                        'section' => $secName,
                        'label' => $itemLabel,
                        'condition' => ($data['exterior'][$itemKey] ?? 'G') === 'R' ? 'R' : 'G',
                    ];
                }
            }

            $serviceOrder->inspection()->updateOrCreate(
                ['service_order_id' => $serviceOrder->id],
                [
                    'inspection_number' => $serviceOrder->inspection?->inspection_number ?? Inspection::generateInspectionNumber(),
                    'inspection_date' => now()->toDateString(),
                    'technician_id' => $data['technician_id'],
                    'manager_name' => $data['manager_name'] ?: 'ARTI DEWI',
                    'fuel_level' => $data['fuel_level'],
                    'odometer_photo' => $odometerPhotoPath,
                    'fuel_photo' => $fuelPhotoPath,
                    'documents_checklist' => $documentsChecklist,
                    'exterior_checklist' => $exteriorChecklist,
                ]
            );

            return $serviceOrder;
        });

        return redirect()->route('sa.show', $order)
            ->with('success', "Pemeriksaan awal (Check-in) berhasil disimpan dengan No: {$order->inspection?->inspection_number}. WO masuk ke antrian konfirmasi mekanik.");
    }

    public function printCheckin(ServiceOrder $serviceOrder): View
    {
        $serviceOrder->load(['customer', 'vehicle', 'technician', 'sa', 'inspection']);

        $extChecklist = $serviceOrder->inspection?->exterior_checklist ?? [];
        $flatItems = [];
        foreach (Inspection::EXTERIOR_SECTIONS as $secName => $secItems) {
            foreach ($secItems as $itemKey => $itemLabel) {
                $cond = $extChecklist[$itemKey]['condition'] ?? 'G';
                $flatItems[] = ['label' => $itemLabel, 'cond' => $cond];
            }
        }
        $chunks = array_chunk($flatItems, 4);

        return view('sa.print-checkin', [
            'order' => $serviceOrder,
            'inspection' => $serviceOrder->inspection,
            'documentsChecklist' => Inspection::DOCUMENTS_CHECKLIST,
            'exteriorSections' => Inspection::EXTERIOR_SECTIONS,
            'chunks' => $chunks,
        ]);
    }

    public function issueSpk(ServiceOrder $serviceOrder): RedirectResponse
    {
        if ($serviceOrder->status !== 'approved') {
            return back()->with('error', 'SPK hanya bisa diterbitkan setelah estimasi disetujui customer.');
        }

        $spkNumber = $serviceOrder->spk_number ?? ServiceOrder::generateSpkNumber();
        $serviceOrder->update([
            'spk_number' => $spkNumber,
            'spk_issued_at' => now(),
        ]);

        return back()->with('success', "Surat Perintah Kerja (SPK) No: {$spkNumber} berhasil diterbitkan.");
    }

    public function printSpk(ServiceOrder $serviceOrder): View
    {
        $serviceOrder->load(['customer', 'vehicle', 'technician', 'sa', 'items', 'inspection']);

        return view('sa.print-spk', compact('serviceOrder'));
    }

}
