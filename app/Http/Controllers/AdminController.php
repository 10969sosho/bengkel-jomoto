<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ServiceOrder;
use App\Models\ServicePricelist;
use App\Models\SubMaterialCatalog;
use App\Models\User;
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
}
