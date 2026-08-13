<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->phone_verified_at) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('phone.verify')
                ->with('error', 'Please verify your phone number before completing your order.');
        }

        return $next($request);
    }
}
