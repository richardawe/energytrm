<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends a trial session once its hour is up and sends the visitor to registration.
 */
class EnsureTrialActive
{
    public const EXPIRED_MESSAGE = 'Your 1-hour trial has ended — register to keep going.';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->trialExpired()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => self::EXPIRED_MESSAGE], 401);
            }

            return redirect()->route('register')->with('status', self::EXPIRED_MESSAGE);
        }

        return $next($request);
    }
}
