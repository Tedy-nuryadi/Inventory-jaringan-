<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Pastikan user yang sedang login masih berstatus aktif.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response
    {
        /*
        |--------------------------------------------------------------------------
        | CEK USER LOGIN
        |--------------------------------------------------------------------------
        |
        | Jika user sedang login dan status akun berubah menjadi nonaktif,
        | user langsung dikeluarkan dari sistem.
        |
        */

        if (
            Auth::check()
            && ! (bool) Auth::user()->is_active
        ) {

            /*
            |--------------------------------------------------------------------------
            | LOGOUT
            |--------------------------------------------------------------------------
            */

            Auth::guard('web')->logout();


            /*
            |--------------------------------------------------------------------------
            | HAPUS SESSION LAMA
            |--------------------------------------------------------------------------
            */

            $request->session()->invalidate();


            /*
            |--------------------------------------------------------------------------
            | BUAT CSRF TOKEN BARU
            |--------------------------------------------------------------------------
            */

            $request->session()->regenerateToken();


            /*
            |--------------------------------------------------------------------------
            | KEMBALI KE LOGIN
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                        'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                ]);
        }


        return $next($request);
    }
}