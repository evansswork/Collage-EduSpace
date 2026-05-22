<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Pastikan user punya role yang dibutuhkan.
     * Usage: ->middleware('role:student')  atau  ->middleware('role:lecturer')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->role !== $role) {
            // Kalau salah role, lempar ke dashboard sesuai role-nya
            return $user->isLecturer()
                ? redirect()->route('lecturer.dashboard')
                : redirect()->route('dashboard');
        }

        return $next($request);
    }
}
