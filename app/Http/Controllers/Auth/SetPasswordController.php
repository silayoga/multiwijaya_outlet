<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\PasswordValidationRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SetPasswordController extends Controller
{
    use PasswordValidationRules;

    public function show(Request $request)
    {
        return view('auth.set-password', ['user' => $request->user()]);
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'password' => $this->passwordRules(),
        ])->validate();

        $user = $request->user();
        $user->password = $request->input('password');
        $user->password_set_at = now();
        $user->save();

        return back()->with('status', 'Password set. You can now also log in with your email and this password.');
    }
}
