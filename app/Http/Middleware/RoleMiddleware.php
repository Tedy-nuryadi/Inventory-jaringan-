<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {

        // Pastikan user sudah login
        if (!auth()->check()) {

            return redirect()->route('login');

        }


        // Ambil role user yang sedang login
        $userRole = auth()->user()->role;


        // Cek apakah role user diizinkan
        if (!in_array($userRole, $roles, true)) {

            abort(403);

        }


        return $next($request);
    }
}