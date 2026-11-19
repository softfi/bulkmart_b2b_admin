<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        if (!$user || $user->role !== $role) {
            abort(403, 'Unauthorized access.');
        }

        if (!$user->is_active) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route($role . '.login')
                ->withErrors([
                    'email' => 'Your account is inactive. Please contact the administrator.',
                ]);
        }

        return $next($request);
    }
}