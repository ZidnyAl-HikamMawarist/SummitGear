<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KasirTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'kasir') {
            $lastActivity = session('last_activity');
            $timeout = 20 * 60; // 20 minutes in seconds

            if ($lastActivity && (time() - $lastActivity > $timeout)) {
                auth()->logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect('/login')->withErrors(['email' => 'Sesi Anda telah berakhir karena tidak ada aktivitas selama 20 menit. Silakan login kembali.']);
            }

            session(['last_activity' => time()]);
        }

        return $next($request);
    }
}
