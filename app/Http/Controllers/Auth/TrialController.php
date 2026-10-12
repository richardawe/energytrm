<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecurityGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Self-service 1-hour trial: creates a throwaway trader account in the
 * "Trial Access" security group and signs the visitor straight in.
 */
class TrialController extends Controller
{
    public const COOKIE = 'etrm_trial_used';
    public const MAX_PER_IP_PER_DAY = 3;

    public function store(Request $request): RedirectResponse
    {
        if ($request->cookie(self::COOKIE)) {
            return redirect()->route('register')
                ->with('status', 'You have already used your free trial — register to keep going.');
        }

        $key = 'trial-start:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, self::MAX_PER_IP_PER_DAY)) {
            return redirect()->route('register')
                ->with('status', 'Too many trials started from this network today — please register instead.');
        }
        RateLimiter::hit($key, 60 * 60 * 24);

        $user = DB::transaction(function () {
            $token = Str::lower(Str::random(10));

            $user = User::create([
                'name'             => 'Trial User',
                'email'            => "trial-{$token}@trial.local",
                'password'         => Str::password(32),
                'role'             => 'trader',
                'status'           => 'Authorized',
                'trial_expires_at' => now()->addMinutes(User::TRIAL_MINUTES),
            ]);

            $group = SecurityGroup::firstOrCreate(
                ['name' => User::TRIAL_GROUP],
                ['description' => 'Self-service 1-hour trial accounts.', 'is_active' => true],
            );
            $user->securityGroups()->attach($group->id);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Welcome! Your trial lasts ' . User::TRIAL_MINUTES . ' minutes. Downloads and exports are disabled during the trial.')
            ->withCookie(cookie()->forever(self::COOKIE, '1'));
    }

    /**
     * Ends the trial session early so the visitor can reach the (guest-only) registration form.
     */
    public function register(Request $request): RedirectResponse
    {
        if ($request->user()?->isTrial()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('register');
    }
}
