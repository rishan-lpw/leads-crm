<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureGoogleAccount
{
    /**
     * If the user is authenticated but not a Google-linked account
     * log them out and redirect back to the login page with an error.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // If no authenticated user, just continue (login attempt will proceed).
        if (! $user) {
            return $next($request);
        }

        // Adjust the field name if your users table uses a different column.
        // Expecting a boolean/nullable column: google_account (true/1 means allowed).
        if (! (bool) ($user->google_account ?? false)) {
            Auth::logout();

            // If request expects JSON, return 403 json response.
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Only Google-linked accounts are permitted to sign in.'], 403);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Only Google-linked accounts are permitted to sign in.']);
        }

        return $next($request);
    }
}