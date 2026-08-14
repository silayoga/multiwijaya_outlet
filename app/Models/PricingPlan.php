<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id', 'plan_name', 'slug', 'billing_cycle', 'price', 'currency',
        'features', 'is_default', 'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_default' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function isCustomQuote(): bool
    {
        return $this->billing_cycle === 'custom_quote' || is_null($this->price);
    }

    public function formattedPrice(): string
    {
        if ($this->isCustomQuote()) {
            return 'Custom quote';
        }

        $amount = 'Rp ' . number_format((float) $this->price, 0, ',', '.');

        return match ($this->billing_cycle) {
            'monthly' => $amount . '/mo',
            'yearly' => $amount . '/yr',
            default => $amount,
        };
    }
}
