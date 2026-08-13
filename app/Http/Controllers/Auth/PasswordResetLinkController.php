<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController as FortifyPasswordResetLinkController;
use Laravel\Fortify\Http\Requests\SendPasswordResetLinkRequest;

class PasswordResetLinkController extends FortifyPasswordResetLinkController
{
    /**
     * Intercept before Fortify's default broker logic. Google-only accounts
     * have no password the user actually knows (a random unusable hash), so
     * a reset link would let anyone with temporary email access set a real
     * password and take over the account — bypassing whatever Google-side
     * protections (2FA, etc.) exist on the real login method.
     */
    public function store(SendPasswordResetLinkRequest $request): Responsable
    {
        $email = $request->input(Fortify::email());
        $user = User::where('email', $email)->first();

        if ($user && $user->isGoogleOnly()) {
            Log::warning('Password reset requested for Google-only account', [
                'email' => $email,
                'ip' => $request->ip(),
            ]);

            // Deliberately a distinct message from the generic "link sent" response —
            // still says nothing about accounts that aren't Google-only or don't exist.
            return app(SuccessfulPasswordResetLinkRequestResponse::class, [
                'status' => 'This account uses Google Sign-In. Please continue with Google to log in.',
            ]);
        }

        return parent::store($request);
    }
}
