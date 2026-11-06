<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\ServicePricelist;
use App\Models\SubMaterialCatalog;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $paidRevenue = Invoice::where('payment_status', 'paid')->sum('grand_total');

        $stats = [
            'orders' => ServiceOrder::count(),
            'active' => ServiceOrder::whereIn('status', ['draft', 'waiting_approval', 'approved', 'working'])->count(),
            'revenue' => (int) $paidRevenue,
            'unpaid' => Invoice::where('payment_status', 'unpaid')->sum('grand_total'),
            'technicians' => User::where('role', 'teknisi')->count(),
            'customers' => User::where('role', 'customer')->count(),
        ];

        $recentOrders = ServiceOrder::with(['customer', 'vehicle', 'technician', 'invoice'])
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $pricelists = ServicePricelist::orderBy('category')->orderBy('service_name')->get();
        $subMaterials = SubMaterialCatalog::orderBy('name')->get();
        $technicians = User::where('role', 'teknisi')->withCount('technicianOrders')->orderBy('name')->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'pricelists', 'subMaterials', 'technicians'));
    }

    public function reports(Request $request): View
    {
        $period = $request->query('period', 'all');
        abort_unless(in_array($period, ['all', 'month', 'today'], true), 404);

        $invoices = Invoice::query()->where('payment_status', 'paid');
        $this->applyPeriod($invoices, $period, 'paid_at');
        $revenue = [
            'total' => (int) (clone $invoices)->sum('grand_total'),
            'services' => (int) (clone $invoices)->sum('total_services'),
            'spareparts' => (int) (clone $invoices)->sum('total_spareparts'),
            'submaterials' => (int) (clone $invoices)->sum('total_submaterials'),
        ];
        $paymentTotals = (clone $invoices)->selectRaw('payment_method, SUM(grand_total) as total')
            ->groupBy('payment_method')->pluck('total', 'payment_method');
        $payments = collect(['qris', 'cash', 'transfer'])->map(fn (string $method): array => [
            'method' => strtoupper($method),
            'total' => (int) ($paymentTotals[$method] ?? 0),
        ]);

        $completedOrders = ServiceOrder::query()->whereNotNull('actual_completion');
        $this->applyPeriod($completedOrders, $period, 'actual_completion');
        $completedByTechnician = (clone $completedOrders)->selectRaw('technician_id, COUNT(*) as total')
            ->groupBy('technician_id')->pluck('total', 'technician_id');
        $workLogs = WorkLog::query()->whereNotNull('stopped_at');
        $this->applyPeriod($workLogs, $period, 'started_at');
        $durationsByOrder = $workLogs->selectRaw('service_order_id, SUM(duration_seconds) as duration_seconds')
            ->groupBy('service_order_id');
        $averageDurations = ServiceOrder::query()
            ->joinSub($durationsByOrder, 'work_durations', fn ($join) => $join->on('service_orders.id', '=', 'work_durations.service_order_id'))
            ->whereNotNull('service_orders.technician_id')
            ->selectRaw('service_orders.technician_id, AVG(work_durations.duration_seconds) as average_seconds')
            ->groupBy('service_orders.technician_id')->pluck('average_seconds', 'service_orders.technician_id');
        $technicians = User::query()->where('role', 'teknisi')->orderBy('name')->get()
            ->map(fn (User $technician): array => [
                'name' => $technician->name,
                'units' => (int) ($completedByTechnician[$technician->id] ?? 0),
                'average_minutes' => (int) round(($averageDurations[$technician->id] ?? 0) / 60),
            ]);

        return view('admin.reports', compact('period', 'revenue', 'payments', 'technicians'));
    }

    private function applyPeriod(Builder $query, string $period, string $column): void
    {
        if ($period === 'today') {
            $query->whereDate($column, today());
        } elseif ($period === 'month') {
            $query->whereYear($column, now()->year)->whereMonth($column, now()->month);
        }
    }
}
