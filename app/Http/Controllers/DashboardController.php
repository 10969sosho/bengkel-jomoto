<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $role = auth()->user()?->role;

        if (! $role) {
            return redirect()->route('login');
        }

        return redirect()->route(match ($role) {
            'sa' => 'sa.index',
            'teknisi' => 'teknisi.index',
            'customer' => 'customer.index',
            default => 'admin.index',
        });
    }
}
