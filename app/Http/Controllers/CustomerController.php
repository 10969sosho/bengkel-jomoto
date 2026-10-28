<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $vehicles = $user->vehicles()->orderBy('plate_number')->get();

        $orders = $user->customerOrders()
            ->with(['vehicle', 'technician', 'sa', 'invoice', 'items'])
            ->orderByDesc('id')
            ->get();

        $active = $orders->whereIn('status', ['draft', 'waiting_approval', 'approved', 'working', 'completed']);
        $history = $orders->whereIn('status', ['invoiced', 'cancelled']);

        return view('customer.dashboard', compact('user', 'vehicles', 'active', 'history'));
    }
}
