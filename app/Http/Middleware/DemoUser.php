<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DemoUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::find(session('demo_user_id'))
            ?? User::where('role', 'admin')->orderBy('id')->first();

        if ($user) {
            Auth::login($user, remember: true);
        }

        return $next($request);
    }
}
