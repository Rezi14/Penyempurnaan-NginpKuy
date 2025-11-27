<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // // 1. Cek apakah user login
        // if (!Auth::check()) {
        //     return redirect('login');
        // }

        // // 2. Cek apakah role user sesuai dengan parameter route
        // // Asumsi: Di tabel users ada kolom 'role' (admin/user)
        // if (Auth::user()->role !== $role) {
        //     abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        // }

        return $next($request);
    }
}
