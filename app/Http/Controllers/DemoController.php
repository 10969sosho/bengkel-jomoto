<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:sa,teknisi,customer,admin'],
            'technician' => ['required_if:role,teknisi', 'nullable', 'in:Budi Santoso,Agus Pratama'],
        ]);
        $user = User::where('role', $data['role'])
            ->when($data['role'] === 'teknisi', fn ($query) => $query->where('name', $data['technician']))
            ->orderBy('id')
            ->firstOrFail();

        session(['demo_user_id' => $user->id]);

        return redirect()->route('dashboard');
    }
}
