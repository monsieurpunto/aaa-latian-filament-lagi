<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticatedFromAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            if (
                $request->routeIs('filament.auth.home') ||
                $request->routeIs('filament.auth.auth.login') ||
                $request->routeIs('filament.auth.auth.register')
            ) {
                return $user->hasRole('super_admin')
                    ? redirect('/admin')
                    : redirect('/app');
            }
        }

        return $next($request);
    }
}