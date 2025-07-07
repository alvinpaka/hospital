<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectToWelcomeOnAuthFailure
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the request is for a protected route and user is not authenticated
        if (!$request->user() && $request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$request->user() && !$request->is('login') && !$request->is('register') && !$request->is('/')) {
            // Redirect to welcome page instead of login page
            return redirect()->route('home');
        }

        return $next($request);
    }
} 