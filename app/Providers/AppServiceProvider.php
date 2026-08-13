<?php

namespace App\Providers;

use App\Listeners\MergeGuestCartIntoUserCart;
use App\Services\Payments\Contracts\PaymentGatewayContract;
use App\Services\Payments\MidtransGateway;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use App\Services\WhatsAppOtpService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Midtrans is the only gateway wired up today; Xendit (a later task)
        // will bind to the same contract without touching any caller of it.
        $this->app->bind(PaymentGatewayContract::class, MidtransGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Registered::class, SendEmailVerificationNotification::class);
        Event::listen(Login::class, MergeGuestCartIntoUserCart::class);

        // Two independent caps: one number can't be spammed, and one attacker
        // can't cycle through many numbers from a single IP.
        RateLimiter::for('send-otp', function (Request $request) {
            $phone = app(WhatsAppOtpService::class)->normalizePhoneNumber((string) $request->input('phone_number'));

            return [
                Limit::perHour(3)->by('phone:'.$phone),
                Limit::perHour(5)->by('ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('verify-otp', function (Request $request) {
            $phone = (string) $request->user()?->phone_number;

            return Limit::perMinutes(15, 5)->by('phone:'.$phone);
        });
    }
}
