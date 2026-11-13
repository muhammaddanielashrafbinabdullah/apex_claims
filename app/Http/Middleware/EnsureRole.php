<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Wrong role for this page: still blocked, but sent to the user's own start page
        // instead of a bare "403 Forbidden" screen.
        if ($user->role !== $role) {
            return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'claims.create');
        }

        return $next($request);
    }
}
