<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $role = $request->input('role');
        $user = User::where('role', $role)->orderBy('id')->firstOrFail();

        session(['demo_user_id' => $user->id]);

        return redirect()->route('dashboard');
    }
}
