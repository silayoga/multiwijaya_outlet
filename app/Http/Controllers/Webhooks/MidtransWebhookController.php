<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payments\MidtransGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransGateway $gateway): Response
    {
        // Gateway-specific — this URL is Midtrans's alone, so we resolve the
        // concrete class directly rather than the generic contract; a future
        // Xendit webhook would get its own URL bound to XenditGateway instead.
        $gateway->handleWebhook($request);

        return response('OK', 200);
    }
}
