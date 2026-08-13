<?php

namespace App\Services;

use App\Models\Order;

class OrderNumberGenerator
{
    /**
     * "MW-20260814-0001" — date-prefixed, per-day incrementing sequence.
     *
     * Must be called inside the same DB transaction as the Order insert:
     * lockForUpdate() only holds its lock for the life of that transaction,
     * and only actually locks rows when at least one exists for today's
     * prefix — the very first order of a given day has nothing to lock, so
     * callers should still treat a unique-constraint violation on
     * order_number as retryable rather than fatal.
     */
    public function generate(): string
    {
        $prefix = 'MW-'.now()->format('Ymd').'-';

        $lastNumber = Order::where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = $lastNumber
            ? ((int) substr($lastNumber, strlen($prefix))) + 1
            : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
