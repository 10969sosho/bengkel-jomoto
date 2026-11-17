<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\WorkLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeknisiController extends Controller
{
    public function index(): View
    {
        $orders = ServiceOrder::with(['customer', 'vehicle', 'items', 'workLogs', 'inspection'])
            ->where('technician_id', auth()->id())
            ->whereIn('status', ['draft', 'inspected', 'waiting_approval', 'approved', 'working', 'completed'])
            ->orderByDesc('id')
            ->get()
            ->sortBy(fn (ServiceOrder $order) => array_search($order->status, ['working', 'approved', 'inspected', 'waiting_approval', 'draft', 'completed']))
            ->values();

        return view('teknisi.dashboard', compact('orders'));
    }

    public function inspect(ServiceOrder $serviceOrder): View
    {
        $this->guard($serviceOrder);

        $serviceOrder->load(['customer', 'vehicle', 'inspection']);

        return view('teknisi.inspect', [
            'order' => $serviceOrder,
            'checklistItems' => $serviceOrder->inspectionChecklist(),
        ]);
    }

    public function saveInspection(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $this->guard($serviceOrder);

        $data = $request->validate([
            'status' => ['nullable', 'array'],
            'status.*' => ['nullable', 'in:ok,p,s,g'],
            'findings' => ['nullable', 'string', 'max:3000'],
            'recommendations' => ['nullable', 'string', 'max:3000'],
            'before_photos' => ['nullable', 'array', 'max:5'],
            'before_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'after_photos' => ['nullable', 'array', 'max:5'],
            'after_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $checklist = [];
        foreach ($serviceOrder->inspectionChecklist() as $key => $label) {
            $checklist[] = [
                'key' => $key,
                'label' => $label,
                'status' => $data['status'][$key] ?? 'ok',
            ];
        }

        $inspection = $serviceOrder->inspection;
        $beforePhotos = $inspection?->before_photos ?? [];
        $afterPhotos = $inspection?->after_photos ?? [];

        foreach ($data['before_photos'] ?? [] as $photo) {
            $beforePhotos[] = $photo->store('inspections', 'public');
        }
        foreach ($data['after_photos'] ?? [] as $photo) {
            $afterPhotos[] = $photo->store('inspections', 'public');
        }

        $serviceOrder->inspection()->updateOrCreate(
            ['service_order_id' => $serviceOrder->id],
            [
                'technician_id' => auth()->id(),
                'checklist' => $checklist,
                'findings' => $data['findings'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'before_photos' => array_slice($beforePhotos, -10),
                'after_photos' => array_slice($afterPhotos, -10),
                'mechanic_confirmed_at' => $serviceOrder->inspection?->mechanic_confirmed_at ?? now(),
            ],
        );

        if ($serviceOrder->status === 'draft') {
            $serviceOrder->update(['status' => 'inspected']);
        }

        return redirect()->route('teknisi.index')
            ->with('success', "Lembar pemeriksaan {$serviceOrder->wo_number} tersimpan. Diteruskan ke Sales Adviser untuk estimasi.");
    }

    public function timer(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $this->guard($serviceOrder);

        $action = $request->input('action');
        $openLog = $serviceOrder->workLogs()->whereNull('stopped_at')->latest('id')->first();

        if ($action === 'start') {
            if ($openLog === null) {
                $resuming = $serviceOrder->status === 'working';

                if (! $resuming && ($serviceOrder->status !== 'approved' || ! $serviceOrder->inspection()->exists())) {
                    return back()->with('error', 'Timer hanya bisa dimulai setelah estimasi di-ACC customer dan pemeriksaan selesai.');
                }

                WorkLog::create([
                    'service_order_id' => $serviceOrder->id,
                    'technician_id' => auth()->id(),
                    'started_at' => now(),
                ]);

                if (! $resuming) {
                    $serviceOrder->update([
                        'status' => 'working',
                        'work_start_time' => $serviceOrder->work_start_time ?? now()->format('H:i:s'),
                    ]);
                }

                return back()->with('success', $resuming ? 'Timer dilanjutkan kembali.' : 'Timer pengerjaan dimulai.');
            }

            return back()->with('success', 'Timer dilanjutkan kembali.');
        }

        if ($action === 'pause') {
            if (! $openLog) {
                return back()->with('error', 'Timer sedang tidak berjalan, tidak ada yang bisa dijeda.');
            }

            $data = $request->validate([
                'pause_reason' => ['required', 'string', 'min:5', 'max:500'],
            ]);

            $openLog->update([
                'stopped_at' => now(),
                'duration_seconds' => $openLog->started_at->diffInSeconds(now()),
                'pause_reason' => $data['pause_reason'],
            ]);

            return back()->with('success', 'Timer dijeda. Alasan jeda dicatat dalam riwayat pengerjaan.');
        }

        return back();
    }

    public function complete(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $this->guard($serviceOrder);

        if ($serviceOrder->status !== 'working') {
            return back()->with('error', 'Hanya WO berstatus sedang dikerjakan yang bisa diselesaikan.');
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['nullable', 'in:qris,cash'],
        ]);

        if (! empty($data['payment_method']) && $serviceOrder->location_type !== 'offsite') {
            return back()->with('error', 'Pembayaran langsung hanya tersedia untuk Home Service.');
        }

        DB::transaction(function () use ($data, $serviceOrder): void {
            $openLog = $serviceOrder->workLogs()->whereNull('stopped_at')->latest('id')->first();
            if ($openLog) {
                $openLog->update([
                    'stopped_at' => now(),
                    'duration_seconds' => $openLog->started_at->diffInSeconds(now()),
                    'notes' => $data['notes'] ?? $openLog->notes,
                ]);
            }

            $serviceOrder->update([
                'status' => 'completed',
                'actual_completion' => now(),
            ]);

            if (! empty($data['odometer'])) {
                $serviceOrder->vehicle->update(['last_odometer' => $data['odometer']]);
            }

            if (! empty($data['payment_method'])) {
                $serviceOrder->loadMissing('items');
                $invoice = $serviceOrder->invoice ?? Invoice::createFromOrder($serviceOrder);
                $invoice->markPaid($data['payment_method']);
                $serviceOrder->update(['status' => 'invoiced']);
            }
        });

        $message = ! empty($data['payment_method'])
            ? "{$serviceOrder->wo_number} selesai dan pembayaran {$data['payment_method']} tercatat."
            : "{$serviceOrder->wo_number} ditandai selesai. Menunggu pemeriksaan akhir SA dan konfirmasi customer.";

        return redirect()->route('teknisi.index')
            ->with('success', $message);
    }

    
    public function confirmInspection(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $this->guard($serviceOrder);

        $data = $request->validate([
            'mechanic_notes' => ['nullable', 'string', 'max:2000'],
            'findings' => ['nullable', 'string', 'max:3000'],
            'recommendations' => ['nullable', 'string', 'max:3000'],
        ]);

        $inspection = $serviceOrder->inspection;
        if (! $inspection) {
            $inspection = $serviceOrder->inspection()->create([
                'technician_id' => auth()->id(),
                'inspection_number' => Inspection::generateInspectionNumber(),
                'inspection_date' => now()->toDateString(),
            ]);
        }

        $inspection->update([
            'technician_id' => auth()->id(),
            'mechanic_confirmed_at' => now(),
            'mechanic_notes' => $data['mechanic_notes'] ?? $inspection->mechanic_notes,
            'findings' => $data['findings'] ?? $inspection->findings,
            'recommendations' => $data['recommendations'] ?? $inspection->recommendations,
        ]);

        return back()->with('success', "Pemeriksaan awal {$serviceOrder->wo_number} telah dikonfirmasi mekanik. Sales Adviser dapat melanjutkan pembuatan lembar estimasi biaya.");
    }

    private function guard(ServiceOrder $order): void
    {
        if ($order->technician_id !== auth()->id()) {
            abort(403, 'WO ini bukan tugas Anda.');
        }
    }
}
