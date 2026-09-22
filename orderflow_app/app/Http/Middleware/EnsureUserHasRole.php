<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi administrator.');
        }

        // Admin always has bypass access to all routes
        if ($request->user()->isAdmin()) {
            return $next($request);
        }

        if (! in_array($request->user()->role, $roles)) {
            abort(403, 'Akses Ditolak. Halaman ini hanya untuk pengguna dengan hak akses: ' . implode(', ', $roles) . '. Peran Anda saat ini: ' . $request->user()->role_label);
        }

        return $next($request);
    }
}
