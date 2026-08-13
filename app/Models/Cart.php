<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'session_token', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function subtotal(): float
    {
        return $this->items->sum(function (CartItem $item) {
            return (float) ($item->unit_price_snapshot ?? 0) * $item->quantity;
        });
    }

    // Custom-quote items (e.g. E-HIOS Starter) don't add to subtotal — they
    // need a separate "request quote" confirmation step in the checkout flow.
    public function hasCustomQuoteItems(): bool
    {
        return $this->items->contains(fn (CartItem $i) => $i->billing_cycle_snapshot === 'custom_quote');
    }
}
