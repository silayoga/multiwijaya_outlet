<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WhatsAppOtpService
{
    /**
     * Send an OTP code to a phone number over WhatsApp via Fonnte.
     *
     * @throws RuntimeException if the message could not be sent — callers must
     *                          surface a clear error to the user, never treat
     *                          a failed send as success.
     */
    public function sendOtp(string $phone, string $code): void
    {
        $target = $this->normalizePhoneNumber($phone);

        $message = "Kode verifikasi Multi Wijaya Anda: {$code}. Berlaku 5 menit. Jangan bagikan kode ini ke siapapun.";

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => config('services.fonnte.api_key')])
                ->asForm()
                ->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);
        } catch (\Throwable $e) {
            Log::error('Fonnte OTP send failed: transport error', [
                'phone' => $target,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Could not send verification code.', previous: $e);
        }

        if (! $response->successful() || $response->json('status') === false) {
            Log::error('Fonnte OTP send failed: API error', [
                'phone' => $target,
                'http_status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Could not send verification code.');
        }
    }

    /**
     * Convert a local Indonesian number (leading 0) into the international
     * MSISDN format Fonnte expects: digits only, leading 62, no "+".
     */
    public function normalizePhoneNumber(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (! str_starts_with($digits, '62')) {
            $digits = '62'.$digits;
        }

        return $digits;
    }
}
