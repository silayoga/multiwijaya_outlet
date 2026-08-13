<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::firstOrNew(['email' => $googleUser->getEmail()]);

        if (! $user->exists) {
            $user->name = $googleUser->getName() ?: $googleUser->getNickname() ?: $googleUser->getEmail();
            $user->google_id = $googleUser->getId();
            $user->password = Str::random(40);
            $user->email_verified_at = now();
            // password_set_at stays null — this is a Google-only account until
            // the user explicitly sets their own password via /settings/set-password.
            $user->save();
        }

        Auth::login($user, remember: true);

        return redirect()->intended(config('fortify.home'));
    }
}
