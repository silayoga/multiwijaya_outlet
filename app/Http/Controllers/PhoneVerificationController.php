<?php

namespace App\Http\Controllers;

use App\Models\PhoneOtp;
use App\Services\WhatsAppOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PhoneVerificationController extends Controller
{
    public function __construct(private WhatsAppOtpService $whatsApp)
    {
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $pendingOtp = null;

        if ($user->phone_number && ! $user->phone_verified_at) {
            $pendingOtp = PhoneOtp::where('phone_number', $user->phone_number)
                ->latest('created_at')
                ->first();

            if ($pendingOtp && ($pendingOtp->isExpired() || $pendingOtp->hasExceededMaxAttempts())) {
                $pendingOtp = null;
            }
        }

        return view('phone.verify', [
            'user' => $user,
            'pendingOtp' => $pendingOtp,
        ]);
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^(\+?62|0)8[0-9]{8,11}$/'],
        ]);

        $phone = $this->whatsApp->normalizePhoneNumber($validated['phone_number']);

        // A fresh request invalidates any previously issued code for this number.
        PhoneOtp::where('phone_number', $phone)->delete();

        $code = (string) random_int(100000, 999999);

        $otp = PhoneOtp::create([
            'phone_number' => $phone,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
            'attempts' => 0,
        ]);

        $user = $request->user();
        $user->phone_number = $phone;
        $user->phone_verified_at = null;
        $user->save();

        try {
            $this->whatsApp->sendOtp($phone, $code);
        } catch (\RuntimeException $e) {
            // Keep the OTP row — the message may still have gone out despite
            // our client-side error, and "resend" will replace it anyway.
            return back()
                ->withInput()
                ->with('error', "Couldn't send verification code, please try again.");
        }

        return redirect()->route('phone.verify')
            ->with('status', 'A verification code was sent to your WhatsApp.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if (! $user->phone_number) {
            return redirect()->route('phone.verify')
                ->with('error', 'Please enter your phone number first.');
        }

        $otp = PhoneOtp::where('phone_number', $user->phone_number)
            ->latest('created_at')
            ->first();

        if (! $otp) {
            return back()->with('error', 'No verification code found. Please request a new one.');
        }

        if ($otp->isExpired()) {
            return back()->with('error', 'This code has expired. Please request a new one.');
        }

        if ($otp->hasExceededMaxAttempts()) {
            return back()->with('error', 'Too many incorrect attempts. Please request a new code.');
        }

        if (! Hash::check($request->input('code'), $otp->code_hash)) {
            $otp->increment('attempts');
            $remaining = max(0, 5 - $otp->attempts);

            return back()->with('error', "Incorrect code. {$remaining} attempt(s) remaining.");
        }

        $user->phone_verified_at = now();
        $user->save();

        PhoneOtp::where('phone_number', $user->phone_number)->delete();

        return redirect()->intended(route('catalog.index'))
            ->with('status', 'Phone number verified.');
    }
}
