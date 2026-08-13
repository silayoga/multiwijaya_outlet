<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'sku', 'name', 'slug', 'listing_type',
        'short_description', 'long_description', 'images',
        'status', 'is_featured', 'stock_qty', 'track_stock', 'created_by',
    ];

    protected $casts = [
        'images' => 'array',
        'is_featured' => 'boolean',
        'track_stock' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function pricingPlans(): HasMany
    {
        return $this->hasMany(PricingPlan::class)->orderBy('sort_order');
    }

    public function defaultPlan(): HasMany
    {
        return $this->pricingPlans()->where('is_default', true);
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ListingSpec::class)->orderBy('sort_order');
    }

    public function isPhysical(): bool
    {
        return $this->listing_type === 'physical_product';
    }

    public function isService(): bool
    {
        return $this->listing_type === 'software_service';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function primaryImage(): ?string
    {
        return $this->images[0] ?? null;
    }

    // No sales-count field exists yet — is_featured is the real "best seller"
    // signal we curate today (already used to pick the homepage carousel).
    public function isPopular(): bool
    {
        return $this->is_featured;
    }

    public function isNew(): bool
    {
        return $this->created_at !== null && $this->created_at->gt(now()->subDays(30));
    }
}
