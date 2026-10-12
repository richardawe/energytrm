<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks trial accounts from downloads and exports.
 */
class DenyTrial
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isTrial()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Not available during the trial.'], 403);
            }
            abort(403, 'Downloads are not available during the trial. Register for full access.');
        }

        return $next($request);
    }
}
