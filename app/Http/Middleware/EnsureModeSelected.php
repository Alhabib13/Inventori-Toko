<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModeSelected
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $pengguna = $request->user();

        if (! $pengguna || $pengguna->role !== 'owner') {
            return $next($request);
        }

        if (blank($pengguna->mode_app)) {
            return redirect()->route('mode-selection.show');
        }

        return $next($request);
    }
}
