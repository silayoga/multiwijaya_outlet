<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'listing_id', 'pricing_plan_id',
        'listing_name_snapshot', 'plan_name_snapshot',
        'unit_price_snapshot', 'billing_cycle_snapshot', 'quantity',
    ];

    protected $casts = [
        'unit_price_snapshot' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class);
    }

    public function lineTotal(): float
    {
        return (float) ($this->unit_price_snapshot ?? 0) * $this->quantity;
    }
}
