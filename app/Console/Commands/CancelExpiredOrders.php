<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('orders:cancel-expired')]
#[Description('Cancel pending-payment orders older than 24 hours')]
class CancelExpiredOrders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = now()->subHours(24);

        $expiredOrders = Order::where('status', 'pending_payment')
            ->where('created_at', '<', $cutoff)
            ->get(['id']);

        $count = $expiredOrders->count();

        if ($count > 0) {
            Order::whereIn('id', $expiredOrders->pluck('id'))->update(['status' => 'cancelled']);
        }

        $message = "orders:cancel-expired: cancelled {$count} order(s) older than 24 hours.";
        Log::info($message);
        $this->info($message);

        return self::SUCCESS;
    }
}
